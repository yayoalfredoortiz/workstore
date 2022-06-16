<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Lead;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Country;
use App\Models\LeadAgent;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Imports\LeadImport;
use App\Jobs\ImportLeadJob;
use App\Models\GdprSetting;
use App\Models\LeadCategory;
use App\Models\LeadFollowUp;
use Illuminate\Http\Request;
use App\Models\PurposeConsent;
use App\DataTables\LeadsDataTable;
use App\Models\PurposeConsentLead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use App\Http\Requests\CommonRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\DataTables\LeadGDPRDataTable;
use App\DataTables\LeadNotesDataTable;
use App\DataTables\ProposalDataTable;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\HeadingRowImport;
use App\Http\Requests\Lead\StoreRequest;
use App\Http\Requests\Lead\UpdateRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\FollowUp\StoreRequest as FollowUpStoreRequest;

class LeadController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.lead';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('leads', $this->user->modules));
            return $next($request);
        });
    }

    public function index(LeadsDataTable $dataTable)
    {
        $this->viewLeadPermission = $viewPermission = user()->permission('view_lead');
        abort_403(!in_array($viewPermission, ['all', 'added', 'both', 'owned']));

        if (!request()->ajax()) {
            $this->totalLeads = Lead::get();
            $this->categories = LeadCategory::get();
            $this->sources = LeadSource::get();
            $this->status = LeadStatus::get();

            $this->totalClientConverted = $this->totalLeads->filter(function ($value, $key) {
                return $value->client_id != null;
            });

            $this->totalLeads = $this->totalLeads->count();
            $this->totalClientConverted = $this->totalClientConverted->count();

            $this->pendingLeadFollowUps = LeadFollowUp::where(DB::raw('DATE(next_follow_up_date)'), '<=', Carbon::now()->format('Y-m-d'))
                ->join('leads', 'leads.id', 'lead_follow_up.lead_id')
                ->where('leads.next_follow_up', 'yes')
                ->groupBy('lead_follow_up.lead_id')
                ->get();
            $this->pendingLeadFollowUps = $this->pendingLeadFollowUps->count();
            $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
                $q->where('status', 'active');
            })->get();
        }

        return $dataTable->render('leads.index', $this->data);

    }

    public function show($id)
    {
        $this->lead = Lead::with(['leadAgent', 'leadAgent.user', 'leadStatus'])->findOrFail($id)->withCustomFields();
        $this->viewPermission = user()->permission('view_lead');

        abort_403(!($this->viewPermission == 'all' || ($this->viewPermission == 'added' && $this->lead->added_by == user()->id)));

        $this->pageTitle = ucfirst($this->lead->client_name);

        $this->categories = LeadCategory::all();

        $this->leadId = $id;

        if (!empty($this->lead->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->lead->getCustomFieldGroupsWithFields()->fields;
        }

        $this->view = 'leads.ajax.profile';

        $tab = request('tab');

        switch ($tab) {
        case 'files':
            $this->view = 'leads.ajax.files';
                break;
        case 'follow-up':
            $this->view = 'leads.ajax.follow-up';
                break;
        case 'proposals':
                return $this->proposals();
        case 'notes':
            return $this->notes();
        case 'gdpr':

            $this->consents = PurposeConsent::with(['lead' => function ($query) use ($id) {
                $query->where('lead_id', $id)
                    ->orderBy('created_at', 'desc');
            }])->get();

            $this->gdpr = GdprSetting::first();

                return $this->gdpr();
        default:
            $this->view = 'leads.ajax.profile';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = ($tab == '') ? 'profile' : $tab;

        return view('leads.show', $this->data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_lead');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $defaultStatus = LeadStatus::where('default', '1')->first();
        $this->columnId = ((request('column_id') != '') ? request('column_id') : $defaultStatus->id);
        $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->get();

        $lead = new Lead();

        if (!empty($lead->getCustomFieldGroupsWithFields())) {
            $this->fields = $lead->getCustomFieldGroupsWithFields()->fields;
        }

        $this->sources = LeadSource::all();
        $this->status = LeadStatus::all();
        $this->categories = LeadCategory::all();
        $this->countries = Country::all();
        $this->pageTitle = __('modules.lead.createTitle');
        $this->salutations = ['mr', 'mrs', 'miss', 'dr', 'sir', 'madam'];

        if (request()->ajax()) {
            $html = view('leads.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.ajax.create';
        return view('leads.create', $this->data);

    }

    /**
     * @param StoreRequest $request
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreRequest $request)
    {
        $this->addPermission = user()->permission('add_lead');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $lead = new Lead();
        $lead->company_name = $request->company_name;
        $lead->website = $request->website;
        $lead->address = $request->address;
        $lead->cell = $request->cell;
        $lead->office = $request->office;
        $lead->city = $request->city;
        $lead->state = $request->state;
        $lead->country = $request->country;
        $lead->postal_code = $request->postal_code;
        $lead->salutation = $request->salutation;
        $lead->client_name = $request->client_name;
        $lead->client_email = $request->client_email;
        $lead->mobile = $request->mobile;
        $lead->note = str_replace('<p><br></p>', '', trim($request->note));
        $lead->next_follow_up = $request->next_follow_up;
        $lead->agent_id = $request->agent_id;
        $lead->source_id = $request->source_id;
        $lead->category_id = $request->category_id;
        $lead->status_id = $request->status;
        $lead->value = ($request->value) ? $request->value : 0;
        $lead->currency_id = $this->global->currency->id;
        $lead->save();

        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $lead->updateCustomFieldData($request->get('custom_fields_data'));
        }

        // Log search
        $this->logSearchEntry($lead->id, $lead->client_name, 'leads.show', 'lead');
        $this->logSearchEntry($lead->id, $lead->client_email, 'leads.show', 'lead');

        if (!is_null($lead->company_name)) {
            $this->logSearchEntry($lead->id, $lead->company_name, 'leads.show', 'lead');
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('leads.index');
        }

        return Reply::successWithData(__('messages.LeadAddedUpdated'), ['redirectUrl' => $redirectUrl]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->lead = Lead::with('currency', 'leadAgent', 'leadAgent.user')->findOrFail($id)->withCustomFields();
        $this->editPermission = user()->permission('edit_lead');

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->lead->added_by == user()->id)
            || ($this->editPermission == 'owned' && !is_null( $this->lead->agent_id) && user()->id == $this->lead->leadAgent->user->id)
            || ($this->editPermission == 'both' && ((!is_null( $this->lead->agent_id) && user()->id == $this->lead->leadAgent->user->id)
            || user()->id == $this->lead->added_by)
        )));

        $this->leadAgents = LeadAgent::with('user')->whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->get();

        if (!empty($this->lead->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->lead->getCustomFieldGroupsWithFields()->fields;
        }

        $this->sources = LeadSource::all();
        $this->status = LeadStatus::all();
        $this->categories = LeadCategory::all();
        $this->countries = Country::all();
        $this->pageTitle = __('modules.lead.updateTitle');
        $this->salutations = ['mr', 'mrs', 'miss', 'dr', 'sir', 'madam'];

        if (request()->ajax()) {
            $html = view('leads.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.ajax.edit';
        return view('leads.create', $this->data);

    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateRequest $request, $id)
    {
        $lead = Lead::with('leadAgent', 'leadAgent.user')->findOrFail($id);
        $this->editPermission = user()->permission('edit_lead');

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $lead->added_by == user()->id)
            || ($this->editPermission == 'owned' && !is_null( $lead->agent_id) && user()->id == $lead->leadAgent->user->id)
            || ($this->editPermission == 'both' && ((!is_null($lead->agent_id) && user()->id == $lead->leadAgent->user->id)
            || user()->id == $lead->added_by)
        )));

        $lead->company_name = $request->company_name;
        $lead->website = $request->website;
        $lead->address = $request->address;
        $lead->salutation = $request->salutation;
        $lead->client_name = $request->client_name;
        $lead->client_email = $request->client_email;
        $lead->mobile = $request->mobile;
        $lead->agent_id = $request->agent_id;
        $lead->source_id = $request->source_id;
        $lead->next_follow_up = $request->next_follow_up;
        $lead->status_id = $request->status;
        $lead->category_id = $request->category_id;
        $lead->value = $request->value;
        $lead->note = str_replace('<p><br></p>', '', trim($request->note));
        $lead->currency_id = $this->global->currency->id;
        $lead->cell = $request->cell;
        $lead->office = $request->office;
        $lead->city = $request->city;
        $lead->state = $request->state;
        $lead->country = $request->country;
        $lead->postal_code = $request->postal_code;
        $lead->save();

        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $lead->updateCustomFieldData($request->get('custom_fields_data'));
        }

        return Reply::successWithData(__('messages.LeadUpdated'), ['redirectUrl' => route('leads.index')]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $lead = Lead::with('leadAgent', 'leadAgent.user')->findOrFail($id);
        $this->deletePermission = user()->permission('delete_lead');

        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $lead->added_by == user()->id)
            || ($this->deletePermission == 'owned' && !is_null( $lead->agent_id) && user()->id == $lead->leadAgent->user->id)
            || ($this->deletePermission == 'both' && ((!is_null($lead->agent_id) && user()->id == $lead->leadAgent->user->id)
            || user()->id == $lead->added_by)
        )));

        Lead::destroy($id);
        return Reply::success(__('messages.LeadDeleted'));

    }

    /**
     * @param CommonRequest $request
     * @return array
     */
    public function changeStatus(CommonRequest $request)
    {
        $lead = Lead::findOrFail($request->leadID);
        $this->editPermission = user()->permission('edit_lead');

        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $lead->added_by == user()->id)));

        $lead->status_id = $request->statusID;
        $lead->save();

        return Reply::success(__('messages.leadStatusChangeSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeBulkStatus($request);
                return Reply::success(__('messages.statusUpdatedSuccessfully'));
        default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_lead') != 'all');

        Lead::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeBulkStatus($request)
    {
        abort_403(user()->permission('edit_lead') != 'all');

        Lead::whereIn('id', explode(',', $request->row_ids))->update(['status_id' => $request->status]);
    }

    /**
     *
     * @param int $leadID
     * @return void
     */
    public function followUpCreate($leadID)
    {
        $this->addPermission = user()->permission('add_lead_follow_up');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->leadID = $leadID;
        return view('leads.followup.create', $this->data);

    }

    /**
     * @param FollowUpStoreRequest $request
     * @return array|void
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function followUpStore(FollowUpStoreRequest $request)
    {
        $this->addPermission = user()->permission('add_lead_follow_up');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $followUp = new LeadFollowUp();
        $followUp->lead_id = $request->lead_id;

        $followUp->next_follow_up_date = Carbon::createFromFormat($this->global->date_format . ' ' . $this->global->time_format, $request->next_follow_up_date . ' ' . $request->start_time)->format('Y-m-d H:i:s');

        $followUp->remark = $request->remark;
        $followUp->save();
        $this->lead = Lead::findOrFail($request->lead_id);

        return Reply::success(__('messages.leadFollowUpAddedSuccess'));

    }

    public function editFollow($id)
    {
        $this->follow = LeadFollowUp::findOrFail($id);
        $this->editPermission = user()->permission('edit_lead_follow_up');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->follow->added_by == user()->id)));

        return view('leads.followup.edit', $this->data);
    }

    public function updateFollow(FollowUpStoreRequest $request)
    {
        $followUp = LeadFollowUp::findOrFail($request->id);
        $this->editPermission = user()->permission('edit_lead_follow_up');
        abort_403(!($this->editPermission == 'all' || ($this->editPermission == 'added' && $this->follow->added_by == user()->id)));


        $followUp->lead_id = $request->lead_id;

        $followUp->next_follow_up_date = Carbon::createFromFormat($this->global->date_format . ' ' . $this->global->time_format, $request->next_follow_up_date . ' ' . $request->start_time)->format('Y-m-d H:i:s');

        $followUp->remark = $request->remark;
        $followUp->save();

        $this->lead = Lead::findOrFail($request->lead_id);

        return Reply::success(__('messages.leadFollowUpUpdatedSuccess'));

    }

    public function deleteFollow($id)
    {
        $followUp = LeadFollowUp::findOrFail($id);
        $this->deletePermission = user()->permission('delete_lead_follow_up');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $followUp->added_by == user()->id)));

        LeadFollowUp::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function proposals()
    {
        $viewPermission = user()->permission('view_lead_proposals');

        abort_403(!in_array($viewPermission, ['all', 'added']));

        $tab = request('tab');
        $this->activeTab = ($tab == '') ? 'overview' : $tab;
        $this->view = 'leads.ajax.proposal';
        $dataTable = new ProposalDataTable();

        return $dataTable->render('leads.show', $this->data);
    }

    public function gdpr()
    {
        $dataTable = new LeadGDPRDataTable();
        $tab = request('tab');
        $this->activeTab = ($tab == '') ? 'gdpr' : $tab;
        $this->view = 'leads.ajax.gdpr';
        return $dataTable->render('leads.show', $this->data);
    }

    public function consent(Request $request)
    {
        $leadId = $request->leadId;
        $this->consentId = $request->consentId;
        $this->leadId = $leadId;

        $this->consent = PurposeConsent::with(['lead' => function ($query) use ($request) {
            $query->where('lead_id', $request->leadId)
                ->orderBy('created_at', 'desc');
        }])
            ->where('id', $request->consentId)
            ->first();

        return view('leads.gdpr.consent-form', $this->data);
    }

    public function saveLeadConsent(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $consent = PurposeConsent::findOrFail($request->consent_id);

        if ($request->consent_description && $request->consent_description != '') {
            $consent->description = str_replace('<p><br></p>', '', trim($request->consent_description));
            $consent->save();
        }

        // Saving Consent Data
        $newConsentLead = new PurposeConsentLead();
        $newConsentLead->lead_id = $lead->id;
        $newConsentLead->purpose_consent_id = $consent->id;
        $newConsentLead->status = trim($request->status);
        $newConsentLead->ip = $request->ip();
        $newConsentLead->updated_by_id = $this->user->id;
        $newConsentLead->additional_description = $request->additional_description;
        $newConsentLead->save();

        return $request->status == 'agree' ? Reply::success(__('messages.consentOptIn')) : Reply::success(__('messages.consentOptOut'));
    }

    public function importLead()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.lead');

        $this->addPermission = user()->permission('add_lead');
        abort_403(!in_array($this->addPermission, ['all', 'added']));


        if (request()->ajax()) {
            $html = view('leads.ajax.import', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'leads.ajax.import';

        return view('leads.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $this->file = Files::upload($request->import_file, 'import-files', false, false, false);
        $excelData = Excel::toArray(new LeadImport, public_path('user-uploads/import-files/' . $this->file))[0];
        $this->hasHeading = $request->has('heading');
        $this->heading = array();

        $this->columns = LeadImport::$field;
        $this->importMatchedColumns = array();
        $this->matchedColumns = array();

        if ($this->hasHeading) {
            $this->heading = (new HeadingRowImport)->toArray(public_path('user-uploads/import-files/' . $this->file))[0][0];
            array_shift($excelData);
            $this->matchedColumns = collect($this->columns)->whereIn('id', $this->heading)->pluck('id');
            $importMatchedColumns = array();

            foreach ($this->matchedColumns as $matchedColumn) {
                $importMatchedColumns[$matchedColumn] = 1;
            }

            $this->importMatchedColumns = $importMatchedColumns;
        }

        $this->importSample = array_slice($excelData, 0, 5);

        $view = view('leads.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        // clear previous import
        Artisan::call('queue:clear database --queue=import_lead');
        Artisan::call('queue:flush');
        // Get index of an array not null value with key
        $columns = array_filter($request->columns, function ($value) {
            return $value !== null;
        });

        $excelData = Excel::toArray(new LeadImport, public_path('user-uploads/import-files/' . $request->file))[0];

        if ($request->has_heading) {
            array_shift($excelData);
        }

        $jobs = [];

        foreach ($excelData as $row) {

            $jobs[] = (new ImportLeadJob($row, $columns));
        }

        $batch = Bus::batch($jobs)->onConnection('database')->onQueue('import_lead')->name('import_lead')->dispatch();

        Files::deleteFile($request->file, 'import-files');

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function notes()
    {
        $dataTable = new LeadNotesDataTable();
        $viewPermission = user()->permission('view_lead');

        abort_403 (!($viewPermission == 'all' || $viewPermission == 'added' || $viewPermission == 'both'));
        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'profile' : $this->activeTab = $tab;
        $this->view = 'leads.ajax.notes';
        return $dataTable->render('leads.show', $this->data);
    }

}
