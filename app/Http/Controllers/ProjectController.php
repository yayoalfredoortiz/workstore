<?php

namespace App\Http\Controllers;

use App\DataTables\ArchiveProjectsDataTable;
use App\DataTables\DiscussionDataTable;
use App\DataTables\ExpensesDataTable;
use App\DataTables\InvoicesDataTable;
use App\DataTables\PaymentsDataTable;
use App\DataTables\ProjectNotesDataTable;
use App\DataTables\TasksDataTable;
use App\DataTables\ProjectsDataTable;
use App\DataTables\TimeLogsDataTable;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Project\StoreProject;
use App\Http\Requests\Project\UpdateProject;
use App\Models\Currency;
use App\Models\DiscussionCategory;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\MessageSetting;
use App\Models\Payment;
use App\Models\Pinned;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectCategory;
use App\Models\ProjectMember;
use App\Models\ProjectTemplate;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\TaskboardColumn;
use App\Models\TaskUser;
use App\Models\Team;
use App\Models\User;
use App\Traits\ProjectProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends AccountBaseController
{
    use ProjectProgress;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.projects';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('projects', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
                $this->allEmployees = User::allEmployees();
            }

            $this->categories = ProjectCategory::all();
            $this->departments = Team::all();
        }

        return $dataTable->render('projects.index', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
        case 'archive':
            $this->archiveRecords($request);
                return Reply::success(__('messages.projectArchiveSuccessfully'));
        case 'change-status':
            $this->changeStatus($request);
                return Reply::success(__('messages.statusUpdatedSuccessfully'));
        default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_projects') != 'all');

        Project::withTrashed()->whereIn('id', explode(',', $request->row_ids))->forceDelete();

        $items = explode(',', $request->row_ids);

        foreach ($items as $item) {
            // Delete project files
            Files::deleteDirectory('project-files/' . $item);
        }
    }

    protected function archiveRecords($request)
    {
        abort_403(user()->permission('edit_projects') != 'all');

        Project::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    public function archiveDestroy($id)
    {
        Project::destroy($id);
        return Reply::success(__('messages.projectArchiveSuccessfully'));
    }

    protected function changeStatus($request)
    {
        abort_403(user()->permission('edit_projects') != 'all');

        Project::whereIn('id', explode(',', $request->row_ids))->update(['status' => $request->status]);
    }

    public function updateStatus(Request $request, $id)
    {
        Project::find($id)
            ->update([
                'status' => $request->status
            ]);

        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->deletePermission = user()->permission('delete_projects');
        abort_403(!($this->deletePermission == 'all' || ($this->deletePermission == 'added' && $project->added_by == user()->id)));

        // Delete project files
        Files::deleteDirectory('project-files/' . $id);
        $project->forceDelete();

        return Reply::success(__('messages.projectDeleted'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_projects');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->pageTitle = __('app.add') . ' ' . __('app.project');
        $this->clients = User::allClients();
        $this->categories = ProjectCategory::all();
        $this->templates = ProjectTemplate::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();
        $this->employees = User::allEmployees();
        $this->redirectUrl = request()->redirectUrl;

        $this->projectTemplate = request('template') ? ProjectTemplate::with('membersMany')->findOrFail(request('template')) : null;

        if ($this->projectTemplate) {
            $this->projectTemplateMembers = $this->projectTemplate->membersMany ? $this->projectTemplate->membersMany->pluck('id')->toArray() : null;
        }

        $project = new Project();

        if (!empty($project->getCustomFieldGroupsWithFields())) {
            $this->fields = $project->getCustomFieldGroupsWithFields()->fields;
        }

        $this->client = isset(request()->default_client) ? User::find(request()->default_client) : null;

        if (request()->ajax()) {
            $html = view('projects.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'projects.ajax.create';
        return view('projects.create', $this->data);

    }

    /**
     * @param StoreProject $request
     * @return array|mixed
     * @throws \Throwable
     */
    public function store(StoreProject $request)
    {
        $this->addPermission = user()->permission('add_projects');

        abort_403(!in_array($this->addPermission, ['all', 'added']));

        DB::beginTransaction();

        try {
            $this->addPermission = user()->permission('add_projects');

            $project = new Project();
            $project->project_name = $request->project_name;

            $project->project_summary = ($request->project_summary !== '<p><br></p>') ? $request->project_summary : null;

            $project->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');

            if (!$request->has('without_deadline')) {
                $project->deadline = Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d');
            }

            if ($request->notes != '') {
                $project->notes = str_replace('<p><br></p>', '', trim($request->notes));
            }

            if ($request->category_id != '') {
                $project->category_id = $request->category_id;
            }

            $project->client_id = $request->client_id;
            $request->client_view_task = $request->client_view_task ? 'enable' : 'disable';
            $project->allow_client_notification = ($request->client_view_task) && ($request->client_task_notification) ? 'enable' : 'disable';
            $request->manual_timelog = $request->manual_timelog ? 'enable' : 'disable';

            if ($request->team_id > 0) {
                $project->team_id = $request->team_id;
            }

            $project->project_budget = $request->project_budget;
            $project->currency_id = $request->currency_id != '' ? $request->currency_id : global_setting()->currency_id;
            $project->hours_allocated = $request->hours_allocated;

            $project->status = 'not started';
            $project->public = $request->public ? 1 : 0;

            $project->save();


            $this->logSearchEntry($project->id, $project->project_name, 'projects.show', 'project');
            $this->logProjectActivity($project->id, 'messages.addedAsNewProject');

            if ($request->template_id) {
                $template = ProjectTemplate::with('membersMany')->findOrFail($request->template_id);

                foreach ($template->tasks as $task) {
                    $projectTask = new Task();

                    $projectTask->project_id = $project->id;
                    $projectTask->heading = $task->heading;
                    $projectTask->task_category_id = $task->project_template_task_category_id;
                    $projectTask->description = str_replace('<p><br></p>', '', trim($task->description));
                    $projectTask->start_date = now()->format('Y-m-d');
                    $projectTask->due_date = Carbon::now()->addDay()->format('Y-m-d');
                    $projectTask->is_private = 0;
                    $projectTask->save();

                    foreach ($task->usersMany as $value) {
                        TaskUser::create(
                            [
                                'user_id' => $value->id,
                                'task_id' => $projectTask->id
                            ]
                        );
                    }

                    foreach ($task->subtasks as $value) {
                        SubTask::create(
                            [
                                'title' => $value->title,
                                'task_id' => $projectTask->id
                            ]
                        );
                    }
                }
            }

            // To add custom fields data
            if ($request->get('custom_fields_data')) {
                $project->updateCustomFieldData($request->get('custom_fields_data'));
            }

            // Commit Transaction
            DB::commit();


            $redirectUrl = urldecode($request->redirect_url);

            if ($redirectUrl == '') {
                $redirectUrl = route('projects.index');
            }

            return Reply::dataOnly(['projectID' => $project->id, 'redirectUrl' => $redirectUrl]);

        } catch (\Swift_TransportException $e) {
            // Rollback Transaction
            DB::rollback();
            return Reply::error('Please configure SMTP details to add project. Visit Settings -> notification setting to set smtp', 'smtp_error');
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return Reply::error('Some error occured when inserting the data. Please try again or contact support');
        }
    }

    public function edit($id)
    {
        $this->project = Project::with('client', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.designation', 'milestones', 'milestones.currency')
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();

        $memberIds = $this->project->members->pluck('user_id')->toArray();

        $this->editPermission = user()->permission('edit_projects');
        abort_403(!(
            $this->editPermission == 'all'
            || ($this->editPermission == 'added' && user()->id == $this->project->added_by)
            || ($this->editPermission == 'owned' && user()->id == $this->project->client_id && in_array('client', user_roles()))
            || ($this->editPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($this->editPermission == 'both' && (user()->id == $this->project->client_id || user()->id == $this->project->added_by))
            || ($this->editPermission == 'both' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
        ));

        $this->pageTitle = __('app.update') . ' ' . __('app.project');

        if (!empty($this->project->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;
        }

        $this->clients = User::allClients();
        $this->categories = ProjectCategory::all();
        $this->currencies = Currency::all();
        $this->teams = Team::all();

        if ($this->editPermission == 'all') {
            $this->employees = User::allEmployees();
        }

        if (request()->ajax()) {
            $html = view('projects.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }


        abort_403(user()->permission('edit_projects') == 'added' && $this->project->added_by != user()->id);
        $this->view = 'projects.ajax.edit';

        return view('projects.create', $this->data);

    }

    /**
     * @param UpdateProject $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateProject $request, $id)
    {
        $project = Project::findOrFail($id);
        $project->project_name = $request->project_name;

        $project->project_summary = ($request->project_summary !== '<p><br></p>') ? $request->project_summary : null;

        $project->start_date = Carbon::createFromFormat($this->global->date_format, $request->start_date)->format('Y-m-d');

        if (!$request->has('without_deadline')) {
            $project->deadline = Carbon::createFromFormat($this->global->date_format, $request->deadline)->format('Y-m-d');
        }
        else {
            $project->deadline = null;
        }

        if ($request->notes != '') {
            $project->notes = str_replace('<p><br></p>', '', trim($request->notes));
        }

        if ($request->category_id != '') {
            $project->category_id = $request->category_id;
        }

        if ($request->client_view_task) {
            $project->client_view_task = 'enable';
        }
        else {
            $project->client_view_task = 'disable';
        }

        if (($request->client_view_task) && ($request->client_task_notification)) {
            $project->allow_client_notification = 'enable';
        }
        else {
            $project->allow_client_notification = 'disable';
        }

        if ($request->manual_timelog) {
            $project->manual_timelog = 'enable';
        }
        else {
            $project->manual_timelog = 'disable';
        }

        $project->team_id = null;

        if ($request->team_id > 0) {
            $project->team_id = $request->team_id;
        }

        $project->client_id = ($request->client_id == 'null' || $request->client_id == '') ? null : $request->client_id;

        if ($request->calculate_task_progress) {
            $project->calculate_task_progress = 'true';
            $project->completion_percent = $this->calculateProjectProgress($id);
        }
        else {
            $project->calculate_task_progress = 'false';
            $project->completion_percent = $request->completion_percent;
        }

        $project->project_budget = $request->project_budget;
        $project->currency_id = $request->currency_id != '' ? $request->currency_id : global_setting()->currency_id;
        $project->hours_allocated = $request->hours_allocated;
        $project->status = $request->status;

        $project->save();

        // To add custom fields data
        if ($request->get('custom_fields_data')) {
            $project->updateCustomFieldData($request->get('custom_fields_data'));
        }

        $this->logProjectActivity($project->id, 'modules.projects.projectUpdated');

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('projects.index');
        }

        return Reply::successWithData(__('messages.projectUpdated'), ['projectID' => $project->id, 'redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('view_projects');
        $viewFilePermission = user()->permission('view_project_files');
        $viewMilestonePermission = user()->permission('view_project_milestones');
        $this->viewPaymentPermission = user()->permission('view_project_payments');
        $this->viewProjectTimelogPermission = user()->permission('view_project_timelogs');
        $this->viewExpensePermission = user()->permission('view_project_expenses');
        $this->viewRatingPermission = user()->permission('view_project_rating');
        $this->viewBurndownChartPermission = user()->permission('view_project_burndown_chart');

        $this->project = Project::with(['client', 'members', 'members.user', 'members.user.session', 'members.user.employeeDetail.designation', 'milestones' => function ($q) use ($viewMilestonePermission) {
            if ($viewMilestonePermission == 'added') {
                $q->where('added_by', user()->id);
            }
        },
         'milestones.currency', 'files' => function ($q) use ($viewFilePermission) {
            if ($viewFilePermission == 'added') {
                $q->where('added_by', user()->id);
            }
         }])
            ->withTrashed()
            ->findOrFail($id)
            ->withCustomFields();


        $memberIds = $this->project->members->pluck('user_id')->toArray();
        abort_403(!(
            $this->viewPermission == 'all'
            || $this->project->public
            || ($this->viewPermission == 'added' && user()->id == $this->project->added_by)
            || ($this->viewPermission == 'owned' && user()->id == $this->project->client_id && in_array('client', user_roles()))
            || ($this->viewPermission == 'owned' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
            || ($this->viewPermission == 'both' && (user()->id == $this->project->client_id || user()->id == $this->project->added_by))
            || ($this->viewPermission == 'both' && in_array(user()->id, $memberIds) && in_array('employee', user_roles()))
        ));

        $this->pageTitle = ucfirst($this->project->project_name);

        if (!empty($this->project->getCustomFieldGroupsWithFields())) {
            $this->fields = $this->project->getCustomFieldGroupsWithFields()->fields;
        }

        $this->messageSetting = MessageSetting::first();

        $tab = request('tab');

        switch ($tab) {
        case 'members':
            $this->view = 'projects.ajax.members';
                break;
        case 'milestones':
            $this->view = 'projects.ajax.milestones';
                break;
        case 'taskboard':
            session()->forget('pusher_settings');
            $this->view = 'projects.ajax.taskboard';
                break;
        case 'tasks':
            $this->taskBoardStatus = TaskboardColumn::all();
                return $this->tasks();
        case 'gantt':
            $this->view = 'projects.ajax.gantt';
                break;
        case 'invoices':
                return $this->invoices();
        case 'files':
            $this->view = 'projects.ajax.files';
                break;
        case 'timelogs':
                return $this->timelogs();
        case 'expenses':
                return $this->expenses();
        case 'payments':
                return $this->payments();
        case 'discussion':
            $this->discussionCategories = DiscussionCategory::orderBy('order', 'asc')->get();
                return $this->discussions();
        case 'notes':
                return $this->notes();
        case 'rating':
                return $this->rating();
        case 'burndown-chart':
            $this->fromDate = now($this->global->timezone)->startOfMonth();
            $this->toDate = now($this->global->timezone);
                return $this->burndownChart();
        default:
            $this->activities = ProjectActivity::getProjectActivities($id, 10);
            $this->taskChart = $this->taskChartData($id);
            $this->hoursBudgetChart = $this->hoursBudgetChartData($this->project);
            $this->amountBudgetChart = $this->amountBudgetChartData($this->project);
            $this->taskBoardStatus = TaskboardColumn::all();
            $this->earnings = Payment::where('status', 'complete')
                ->where('project_id', $id)
                ->sum('amount');
            $hoursLogged = $this->project->times()->sum('total_minutes');
            $this->hoursLogged = intdiv($hoursLogged, 60);
            $this->expenses = Expense::where(['project_id' => $id, 'status' => 'approved'])->sum('price');
            $this->view = 'projects.ajax.overview';
                break;
        }


        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        ($tab == '') ? $this->activeTab = 'overview' : $this->activeTab = $tab;

        abort_403(user()->permission('view_projects') == 'added' && $this->project->added_by != user()->id);

        return view('projects.show', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function taskChartData($id)
    {
        $taskStatus = TaskboardColumn::all();
        $data['labels'] = $taskStatus->pluck('column_name');
        $data['colors'] = $taskStatus->pluck('label_color');
        $data['values'] = [];

        foreach ($taskStatus as $label) {
            $data['values'][] = Task::where('project_id', $id)->where('tasks.board_column_id', $label->id)->count();
        }

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function hoursBudgetChartData($project)
    {
        $hoursBudget = $project->hours_allocated ? $project->hours_allocated : 0;
        $hoursLogged = $project->times()->sum('total_minutes');
        $hoursLogged = intdiv($hoursLogged, 60);
        $overRun = $hoursLogged - $hoursBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $hoursBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$hoursBudget, $hoursBudget],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function amountBudgetChartData($project)
    {
        $amountBudget = $project->project_budget ? $project->project_budget : 0;
        $earnings = Payment::where('status', 'complete')
            ->where('project_id', $project->id)
            ->sum('amount');
        $plannedOverun = $earnings < $amountBudget ? $earnings : $amountBudget;
        $overRun = $earnings - $amountBudget;
        $overRun = $overRun < 0 ? 0 : $overRun;

        $data['labels'] = [__('app.planned'), __('app.actual')];
        $data['colors'] = ['#2cb100', '#d30000'];
        $data['threshold'] = $amountBudget;
        $dataset = [
            [
                'name' => __('app.planned'),
                'values' => [$amountBudget, $plannedOverun],
            ],
            [
                'name' => __('app.overrun'),
                'values' => [0, $overRun],
            ],
        ];
        $data['datasets'] = $dataset;

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function storePin(Request $request)
    {
        $pinned = new Pinned();
        $pinned->task_id = $request->task_id;
        $pinned->project_id = $request->project_id;
        $pinned->save();

        return Reply::success(__('messages.pinnedSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroyPin(Request $request, $id)
    {
        Pinned::where('project_id', $id)->where('user_id', user()->id)->delete();
        return Reply::success(__('messages.pinnedRemovedSuccess'));
    }

    public function assignProjectAdmin(Request $request)
    {
        $userId = $request->userId;
        $projectId = $request->projectId;
        $project = Project::findOrFail($projectId);
        $project->project_admin = $userId;
        $project->save();

        return Reply::success(__('messages.roleAssigned'));
    }

    public function tasks()
    {
        $dataTable = new TasksDataTable();
        $viewPermission = user()->permission('view_project_tasks');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'overview' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.tasks';
        return $dataTable->render('projects.show', $this->data);

    }

    public function ganttData()
    {
        $id = request('projectID');
        $assignedTo = request('assignedTo');
        $projectTask = request('projectTask');
        $withoutDueDate = false;

        if ($assignedTo != 'all') {
            $tasks = Task::projectTasks($id, $assignedTo, null, $withoutDueDate);
        }
        else {
            $tasks = Task::projectTasks($id, null, null, $withoutDueDate);
        }

        if ($projectTask) {
            $tasks = $tasks->whereIn('id', explode(',', $projectTask));
        }

        $data = array();

        foreach ($tasks as $key => $task) {

            $data[] = [
                'id' => 'task-' . $task->id,
                'name' => ucfirst($task->heading),
                'start' => ((!is_null($task->start_date)) ? $task->start_date->format('Y-m-d') : ((!is_null($task->due_date)) ? $task->due_date->format('Y-m-d') : null)),
                'end' => (!is_null($task->due_date)) ? $task->due_date->format('Y-m-d') : $task->start_date->format('Y-m-d'),
                'progress' => 0,
                'bg_color' => $task->boardColumn->label_color,
                'taskid' => $task->id,
                'draggable' => true
            ];

            if (!is_null($task->dependent_task_id)) {
                $data[$key]['dependencies'] = 'task-' . $task->dependent_task_id;
            }
        }

        return response()->json($data);
    }

    public function invoices()
    {
        $dataTable = new InvoicesDataTable;
        $viewPermission = user()->permission('view_project_invoices');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'overview' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.invoices';
        return $dataTable->render('projects.show', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function invoiceList($id)
    {
        $options = '<option value="">--</option>';

        $viewPermission = user()->permission('view_invoices');

        if (($viewPermission == 'all' || $viewPermission == 'added')) {

            if ($id != 0) {
                $invoices = Invoice::with('payment', 'currency')->where('project_id', $id)->pending()->get();
            }
            else{
                $invoices = Invoice::with('payment')->where(function ($q) {
                    $q->where('status', 'unpaid')
                        ->orWhere('status', 'partial');
                })->get();
            }

            foreach ($invoices as $item) {
                $paidAmount = $item->amountPaid();

                $options .= '<option data-currency-id="'.$item->currency->id.'" data-content="' . $item->invoice_number . ' - ' . __('app.total') . ': <span class=\'text-dark f-w-500 mr-2\'>' . currency_formatter($item->total, $item->currency->currency_symbol) . ' </span>' . __('modules.invoices.due') . ': <span class=\'text-red\'>' . currency_formatter(max(($item->total - $paidAmount), 0), $item->currency->currency_symbol) . '</span>" value="' . $item->id . '"> ' . $item->invoice_number . ' </option>';
            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function members($id)
    {
        $options = '';

        if ($id == 0) {
            $members = User::allEmployees();

            foreach ($members as $item) {
                $options .= '<option  data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div>  ' . $item->name . '" value="' . $item->id . '"> ' . $item->name . ' </option>';
            }
        }
        else {
            $members = ProjectMember::with('user')->where('project_id', $id)->get();

            foreach ($members as $item) {
                $options .= '<option  data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->user->image_url . ' ></div>  ' . $item->user->name . '" value="' . $item->user->id . '"> ' . $item->user->name . ' </option>';
            }
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function timelogs()
    {
        $dataTable = new TimeLogsDataTable();
        $viewPermission = user()->permission('view_project_timelogs');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'overview' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.timelogs';
        return $dataTable->render('projects.show', $this->data);
    }

    public function expenses()
    {
        $dataTable = new ExpensesDataTable();
        $viewPermission = user()->permission('view_project_expenses');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'overview' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.expenses';
        return $dataTable->render('projects.show', $this->data);

    }

    public function payments()
    {
        $dataTable = new PaymentsDataTable();
        $viewPermission = user()->permission('view_project_payments');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'overview' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.payments';
        return $dataTable->render('projects.show', $this->data);

    }

    public function discussions()
    {
        $dataTable = new DiscussionDataTable();
        $viewPermission = user()->permission('view_project_discussions');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'overview' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.discussion';
        return $dataTable->render('projects.show', $this->data);

    }

    public function burndown(Request $request, $id)
    {
        $this->project = Project::with(['tasks' => function ($query) use ($request) {
            if ($request->startDate !== null && $request->startDate != 'null' && $request->startDate != '') {
                $query->where(DB::raw('DATE(`start_date`)'), '>=', Carbon::createFromFormat($this->global->date_format, $request->startDate));
            }

            if ($request->endDate !== null && $request->endDate != 'null' && $request->endDate != '') {
                $query->where(DB::raw('DATE(`due_date`)'), '<=', Carbon::createFromFormat($this->global->date_format, $request->endDate));
            }

            $query->whereNotNull('due_date');
        }])->withTrashed()->findOrFail($id);

        $this->totalTask = $this->project->tasks->count();
        $datesArray = [];
        $startDate = $request->startDate ? Carbon::createFromFormat($this->global->date_format, $request->startDate) : Carbon::parse($this->project->start_date);

        if ($this->project->deadline) {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->global->date_format, $request->endDate) : Carbon::parse($this->project->deadline);
        }
        else {
            $endDate = $request->endDate ? Carbon::createFromFormat($this->global->date_format, $request->endDate) : Carbon::now();
        }

        for ($startDate; $startDate <= $endDate; $startDate->addDay()) {
            $datesArray[] = $startDate->format($this->global->date_format);
        }

        $uncompletedTasks = [];
        $createdTasks = [];
        $deadlineTasks = [];
        $deadlineTasksCount = [];
        $this->datesArray = json_encode($datesArray);

        foreach ($datesArray as $key => $value) {

            if (Carbon::createFromFormat($this->global->date_format, $value)->lessThanOrEqualTo(Carbon::now())) {
                $uncompletedTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {

                    if (is_null($task->completed_on)) {
                        return true;
                    }

                    return $task->completed_on ? $task->completed_on->greaterThanOrEqualTo(Carbon::createFromFormat($this->global->date_format, $value)) : false;
                })->count();

                $createdTasks[$key] = $this->project->tasks->filter(function ($task) use ($value) {
                    return Carbon::createFromFormat($this->global->date_format, $value)->startOfDay()->equalTo($task->created_at->startOfDay());
                })->count();

                if ($key > 0) {
                    $uncompletedTasks[$key] += $createdTasks[$key];
                }

            }

            $deadlineTasksCount[] = $this->project->tasks->filter(function ($task) use ($value) {
                return Carbon::createFromFormat($this->global->date_format, $value)->startOfDay()->equalTo($task->due_date->startOfDay());
            })->count();

            if ($key == 0) {
                $deadlineTasks[$key] = $this->totalTask - $deadlineTasksCount[$key];
            }
            else {
                $newKey = $key - 1;
                $deadlineTasks[$key] = $deadlineTasks[$newKey] - $deadlineTasksCount[$key];
            }
        }

        $this->uncompletedTasks = json_encode($uncompletedTasks);
        $this->deadlineTasks = json_encode($deadlineTasks);

        if ($request->ajax()) {
            return $this->data;
        }

        $this->startDate = $request->startDate ? Carbon::parse($request->startDate)->format($this->global->date_format) : Carbon::parse($this->project->start_date)->format($this->global->date_format);
        $this->endDate = $endDate->format($this->global->date_format);

        return view('projects.ajax.burndown', $this->data);
    }

    public function notes()
    {
        $dataTable = new ProjectNotesDataTable();
        $viewPermission = user()->permission('view_project_note');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'profile' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.notes';
        return $dataTable->render('projects.show', $this->data);

    }

    public function burndownChart()
    {
        $viewPermission = user()->permission('view_project_burndown_chart');
        abort_403(!in_array($viewPermission, ['all']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'burndown-chart' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.burndown';
        return view('projects.show', $this->data);

    }

    public function rating()
    {
        $viewPermission = user()->permission('view_project_rating');
        abort_403(!in_array($viewPermission, ['all', 'added']));

        $tab = request('tab');
        ($tab == '') ? $this->activeTab = 'rating' : $this->activeTab = $tab;
        $this->view = 'projects.ajax.rating';
        return view('projects.show', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function archive(ArchiveProjectsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_projects');
        abort_403($viewPermission == 'none');

        if (!request()->ajax()) {

            if (in_array('client', user_roles())) {
                $this->clients = User::client();
            }
            else {
                $this->clients = User::allClients();
                $this->allEmployees = User::allEmployees();
            }

            $this->categories = ProjectCategory::all();
            $this->departments = Team::all();
        }

        return $dataTable->render('projects.archive', $this->data);

    }

    public function archiveRestore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->restore();

        return Reply::success(__('messages.projectRevertSuccessfully'));
    }

}
