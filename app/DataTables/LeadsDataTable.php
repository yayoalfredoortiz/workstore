<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\Lead;
use App\Models\LeadStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LeadsDataTable extends BaseDataTable
{

    private $addLeadPermission;
    private $editLeadPermission;
    private $deleteLeadPermission;
    private $addFollowUpPermission;
    /**
     * @var LeadStatus[]|\Illuminate\Database\Eloquent\Collection
     */
    private $status;

    public function __construct()
    {
        parent::__construct();
        $this->addLeadPermission = user()->permission('add_lead');
        $this->editLeadPermission = user()->permission('edit_lead');
        $this->deleteLeadPermission = user()->permission('delete_lead');
        $this->addFollowUpPermission = user()->permission('add_lead_follow_up');
        $this->status = LeadStatus::get();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $currentDate = Carbon::now(global_setting()->timezone)->format('Y-m-d');
        $status = $this->status;

        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                    $action .= '<a href="' . route('leads.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if (
                    $this->editLeadPermission == 'all'
                    || ($this->editLeadPermission == 'added' && user()->id == $row->added_by)
                    || ($this->editLeadPermission == 'owned' && !is_null($row->agent_id) && user()->id == $row->leadAgent->user->id)
                    || ($this->editLeadPermission == 'both' && ((!is_null($row->agent_id) && user()->id == $row->leadAgent->user->id)
                    || user()->id == $row->added_by))
                    ) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('leads.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (
                    $this->deleteLeadPermission == 'all'
                    || ($this->deleteLeadPermission == 'added' && user()->id == $row->added_by)
                    || ($this->deleteLeadPermission == 'owned' && !is_null($row->agent_id) && user()->id == $row->leadAgent->user->id)
                    || ($this->deleteLeadPermission == 'both' && ((!is_null($row->agent_id) && user()->id == $row->leadAgent->user->id)
                    || user()->id == $row->added_by))
                    ) {
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-id="' . $row->id . '">
                        <i class="fa fa-trash mr-2"></i>
                        ' . trans('app.delete') . '
                    </a>';
                }


                if ($row->client_id == null || $row->client_id == '') {
                    $action .= '<a class="dropdown-item" href="' . route('clients.create') . '?lead=' . $row->id . '">
                                <i class="fa fa-user mr-2"></i>
                                ' . trans('modules.lead.changeToClient') . '
                            </a>';
                }

                if (($this->addFollowUpPermission == 'all' || ($this->addFollowUpPermission == 'added' && user()->id == $row->added_by)) && $row->client_id == null && $row->next_follow_up == 'yes') {
                    $action .= '<a onclick="followUp(' . $row->id . ')" class="dropdown-item" href="javascript:;">
                                <i class="fa fa-thumbs-up mr-2"></i>
                                ' . trans('modules.lead.addFollowUp') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addColumn('employee_name', function ($row) {
                if (!is_null($row->agent_id)) {
                    return $row->leadAgent->user->name;
                }
            })
            ->addColumn('lead', function ($row) {
                return $row->client_name;
            })

            ->addColumn('status', function ($row) use ($status) {

                $statusLi = '--';

                foreach ($status as $st) {
                    if ($row->status_id == $st->id) {
                        $selected = 'selected';
                    }
                    else {
                        $selected = '';
                    }

                    $statusLi .= '<option ' . $selected . ' value="' . $st->id . '">' . ucfirst($st->type) . '</option>';
                }

                $action = '<select class="form-control statusChange" name="statusChange" onchange="changeStatus( ' . $row->id . ', this.value)">
                    ' . $statusLi . '
                </select>';

                return $action;
            })
            ->addColumn('leadStatus', function ($row) use ($status) {
                $leadStatus = '';

                foreach ($status as $st) {
                    if ($row->status_id == $st->id) {
                        $leadStatus = $st->type;
                    }
                }

                return $leadStatus;
            })
            ->editColumn('client_name', function ($row) {
                if ($row->client_id != null && $row->client_id != '') {
                    $label = '<label class="badge badge-secondary">' . __('app.client') . '</label>';
                }
                else {
                    $label = '';
                }

                $client_name = ucfirst($row->salutation) . ' ' . ucfirst($row->client_name);

                return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('leads.show', [$row->id]) . '">' . $client_name . '</a></h5>
                    <p class="mb-0">' . $label . '</p>
                    </div>
                  </div>';
            })
            ->editColumn('next_follow_up_date', function ($row) use ($currentDate) {
                if ($row->next_follow_up_date != null && $row->next_follow_up_date != '') {
                    $date = Carbon::parse($row->next_follow_up_date)->format($this->global->date_format .' '. $this->global->time_format);
                }
                else {
                    $date = '--';
                }

                if ($row->next_follow_up_date < $currentDate && $date != '--') {
                    return $date . '<br><label class="label label-danger">' . __('app.pending') . '</label>';
                }

                return $date;
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format($this->global->date_format);
            })
            ->editColumn('agent_name', function ($row) {

                if (!is_null($row->agent_id)) {
                    return view('components.employee', [
                        'user' => $row->leadAgent->user
                    ]);
                }

                return '--';
            })
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->removeColumn('status_id')
            ->removeColumn('client_id')
            ->removeColumn('source')
            ->removeColumn('next_follow_up')
            ->removeColumn('statusName')
            ->rawColumns(['status', 'action', 'client_name', 'next_follow_up_date', 'agent_name', 'check']);
    }

    /**
     * @param Lead $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Lead $model)
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        $lead = $model->with(['leadAgent', 'leadAgent.user'])
            ->select(
                'leads.id',
                'leads.agent_id',
                'leads.added_by',
                'leads.client_id',
                'leads.next_follow_up',
                'leads.salutation',
                'client_name',
                'company_name',
                'lead_status.type as statusName',
                'status_id',
                'leads.created_at',
                'lead_sources.type as source',
                'users.name as agent_name',
                'users.image',
                DB::raw("(select next_follow_up_date from lead_follow_up where lead_id = leads.id and leads.next_follow_up  = 'yes' and DATE(next_follow_up_date) >= '".$currentDate."' ORDER BY next_follow_up_date asc limit 1) as next_follow_up_date")
            )
            ->leftJoin('lead_status', 'lead_status.id', 'leads.status_id')
            ->leftJoin('lead_agents', 'lead_agents.id', 'leads.agent_id')
            ->leftJoin('users', 'users.id', 'lead_agents.user_id')
            ->leftJoin('lead_sources', 'lead_sources.id', 'leads.source_id');

        if ($this->request()->followUp != 'all' && $this->request()->followUp != '') {
            $lead = $lead->leftJoin('lead_follow_up', 'lead_follow_up.lead_id', 'leads.id');

            if ($this->request()->followUp == 'yes') {
                $lead = $lead->where('leads.next_follow_up', 'yes');
            }
            else {
                $lead = $lead->where('leads.next_follow_up', 'no');
            }

        }

        if ($this->request()->type != 'all' && $this->request()->type != '') {

            if ($this->request()->type == 'lead') {
                $lead = $lead->whereNull('client_id');
            }
            else {
                $lead = $lead->whereNotNull('client_id');
            }
        }

        if ($this->request()->startDate !== null && $this->request()->startDate != 'null' && $this->request()->startDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $this->request()->startDate)->toDateString();

            $lead = $lead->having(DB::raw('DATE(leads.`created_at`)'), '>=', $startDate);
        }

        if ($this->request()->endDate !== null && $this->request()->endDate != 'null' && $this->request()->endDate != '') {
            $endDate = Carbon::createFromFormat($this->global->date_format, $this->request()->endDate)->toDateString();
            $lead = $lead->having(DB::raw('DATE(leads.`created_at`)'), '<=', $endDate);
        }

        if (($this->request()->agent != 'all' && $this->request()->agent != '') || $this->addLeadPermission == 'added') {
            $lead = $lead->where(function ($query) {
                if ($this->request()->agent != 'all' && $this->request()->agent != '') {
                    $query->where('agent_id', $this->request()->agent);
                }

                if ($this->addLeadPermission == 'added') {
                    $query->orWhere('leads.added_by', user()->id);
                }
            });
        }

        if ($this->request()->category_id != 'all' && $this->request()->category_id != '') {
            $lead = $lead->where('category_id', $this->request()->category_id);
        }

        if ($this->request()->source_id != 'all' && $this->request()->source_id != '') {
            $lead = $lead->where('source_id', $this->request()->source_id);
        }

        if ($this->request()->searchText != '') {
            $lead = $lead->where(function ($query) {
                $query->where('leads.client_name', 'like', '%' . request('searchText') . '%');
            });
        }

        return $lead->groupBy('leads.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('leads-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2)
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            ->processing(true)
            ->language(__('app.datatable'))
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["leads-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".statusChange").selectpicker();
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id')],
            __('app.name') => ['data' => 'client_name', 'name' => 'client_name', 'exportable' => false, 'title' => __('app.name')],
            __('app.lead') => ['data' => 'lead', 'name' => 'client_name', 'visible' => false, 'title' => __('app.lead')],
            __('modules.lead.companyName') => ['data' => 'company_name', 'name' => 'company_name', 'title' => __('modules.lead.companyName')],
            __('app.createdOn') => ['data' => 'created_at', 'name' => 'created_at', 'title' => __('app.createdOn')],
            __('modules.lead.nextFollowUp') => ['data' => 'next_follow_up_date', 'name' => 'next_follow_up_date', 'orderable' => false, 'searchable' => false, 'title' => __('modules.lead.nextFollowUp')],
            __('modules.lead.leadAgent') => ['data' => 'agent_name', 'name' => 'users.name', 'exportable' => false, 'title' => __('modules.lead.leadAgent')],
            __('app.employee') => ['data' => 'employee_name', 'name' => 'users.name', 'visible' => false, 'title' => __('app.employee')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'exportable' => false, 'title' => __('app.status')],
            __('app.leadStatus') => ['data' => 'leadStatus', 'name' => 'leadStatus', 'visible' => false, 'orderable' => false, 'searchable' => false, 'title' => __('app.leadStatus')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'leads_' . date('YmdHis');
    }

    public function pdf()
    {
        set_time_limit(0);

        if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }

}
