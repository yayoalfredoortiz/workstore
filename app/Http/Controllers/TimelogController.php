<?php

namespace App\Http\Controllers;

use App\DataTables\TimeLogsDataTable;
use App\Exports\EmployeeTimelogs;
use App\Helper\Reply;
use App\Http\Requests\TimeLogs\StartTimer;
use App\Http\Requests\TimeLogs\StoreTimeLog;
use App\Http\Requests\TimeLogs\UpdateTimeLog;
use App\Models\Project;
use App\Models\ProjectTimeLog;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TimelogController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.timeLogs';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('timelogs', $this->user->modules));
            return $next($request);
        });
    }

    public function index(TimeLogsDataTable $dataTable)
    {
        $viewPermission = $this->viewTimelogPermission;
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        if (!request()->ajax()) {
            $this->employees = User::allEmployees();
            $this->projects = Project::allProjects();
        }

        return $dataTable->render('timelogs.index', $this->data);

    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
        case 'delete':
            $this->deleteRecords($request);
                return Reply::success(__('messages.deleteSuccess'));
        case 'change-status':
            $this->changeStatus($request);
                return Reply::success(__('messages.statusUpdatedSuccessfully'));
        default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_timelogs') !== 'all');
        ProjectTimeLog::whereIn('id', explode(',', $request->row_ids))->delete();
    }

    protected function changeStatus($request)
    {
        abort_403(user()->permission('edit_timelogs') !== 'all');
        ProjectTimeLog::whereIn('id', explode(',', $request->row_ids))->update(
            [
                'approved' => $request->status,
                'approved_by' => (($request->status == 1) ? user()->id : null)
            ]
        );
    }

    public function create()
    {
        $this->pageTitle = __('modules.timeLogs.logTime');
        $this->addTimelogPermission = user()->permission('add_timelogs');
        abort_403(!in_array($this->addTimelogPermission, ['all', 'added']));

        if (request()->has('default_assign') && request('default_assign') != '') {
            $assignId = request('default_assign');
            $this->projects = $projects = Project::whereHas('members', function ($query) use ($assignId) {
                $query->where('user_id', $assignId);
            })
            ->orWhere('projects.public', 1)
            ->orderBy('project_name', 'asc')->get();
        }
        elseif (request()->has('default_project') && request('default_project') != '') {
            $defaultProject = request('default_project');
            $this->projects = $projects = Project::where('id', $defaultProject)
                ->get();
        }
        else {
            $this->projects = Project::allProjects();
        }

        $this->tasks = Task::timelogTasks(request('default_project'));

        if (request()->ajax()) {
            $html = view('timelogs.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'timelogs.ajax.create';
        return view('timelogs.create', $this->data);

    }

    public function store(StoreTimeLog $request)
    {
        $startDateTime = Carbon::createFromFormat($this->global->date_format, $request->start_date, $this->global->timezone)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->global->time_format, $request->start_time)->format('H:i:s');
        $startDateTime = Carbon::parse($startDateTime, $this->global->timezone)->setTimezone('UTC');

        $endDateTime = Carbon::createFromFormat($this->global->date_format, $request->end_date, $this->global->timezone)->format('Y-m-d') . ' ' . Carbon::createFromFormat($this->global->time_format, $request->end_time)->format('H:i:s');
        $endDateTime = Carbon::parse($endDateTime, $this->global->timezone)->setTimezone('UTC');

        $timeLog = new ProjectTimeLog();

        if ($request->has('project_id')) {
            $timeLog->project_id = $request->project_id;
        }

        $timeLog->task_id = $request->task_id;
        $timeLog->user_id = $request->user_id;
        $userID = $request->user_id;

        $activeTimer = ProjectTimeLog::with('user')
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(
                        function ($q1) use ($startDateTime, $endDateTime) {
                            $q1->where('end_time', '>', $startDateTime->format('Y-m-d H:i:s'));
                            $q1->where('end_time', '<', $endDateTime->format('Y-m-d H:i:s'));
                        }
                    )
                    ->orWhere(
                    function ($q1) use ($startDateTime) {
                        $q1->whereDate('start_time', $startDateTime->format('Y-m-d'));
                        $q1->whereNull('end_time');
                    }
                );
            })
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->where('user_id', $userID)
            ->first();

        if (is_null($activeTimer)) {
            $timeLog->start_time = $startDateTime;
            $timeLog->end_time = $endDateTime;
            $timeLog->total_hours = (int)$timeLog->end_time->diff($timeLog->start_time)->format('%d') * 24 + (int)$timeLog->end_time->diff($timeLog->start_time)->format('%H');
            $timeLog->total_minutes = ((int)$timeLog->total_hours * 60) + (int)($timeLog->end_time->diff($timeLog->start_time)->format('%i'));
            $timeLog->hourly_rate = 0;
            $timeLog->memo = $request->memo;
            $timeLog->edited_by_user = user()->id;
            $timeLog->save();

            return Reply::successWithData(__('messages.timeLogAdded'), ['redirectUrl' => route('timelogs.index')]);
        }

        return Reply::error(__('messages.timelogAlreadyExist'));
    }

    public function destroy($id)
    {
        $timelog = ProjectTimeLog::findOrFail($id);
        $deleteTimelogPermission = user()->permission('delete_timelogs');
        abort_403(!($deleteTimelogPermission == 'all' || ($deleteTimelogPermission == 'added' && $timelog->added_by == user()->id)));

        ProjectTimeLog::destroy($id);
        return Reply::success(__('messages.timeLogDeleted'));
    }

    public function edit($id)
    {
        $this->pageTitle = __('modules.timeLogs.logTime');
        $editTimelogPermission = $this->editTimelogPermission = user()->permission('edit_timelogs');
        $timeLog = $this->timeLog = ProjectTimeLog::with('user', 'project', 'task')->findOrFail($id);

        abort_403(!(
            $editTimelogPermission == 'all'
        || ($editTimelogPermission == 'added' && $timeLog->added_by == user()->id)
        || ($editTimelogPermission == 'owned'
            && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id)
            )
        || ($editTimelogPermission == 'both' && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id || $timeLog->added_by == user()->id))
        ));

        if (!is_null($this->timeLog->task_id) && !is_null($this->timeLog->project_id)) {
            $this->tasks = Task::timelogTasks($this->timeLog->project_id);
            $this->employees = $this->timeLog->task->users;
        }
        else if (!is_null($this->timeLog->project_id)) {
            $this->tasks = Task::timelogTasks($this->timeLog->project_id);
            $this->employees = $this->timeLog->project->membersMany;
        }
        else {
            $this->tasks = Task::timelogTasks();
            $this->employees = $this->timeLog->task->users;
        }

        $this->projects = Project::allProjects();

        if (request()->ajax()) {
            $html = view('timelogs.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'timelogs.ajax.edit';
        return view('timelogs.create', $this->data);

    }

    public function update(UpdateTimeLog $request, $id)
    {
        $timeLog = ProjectTimeLog::findOrFail($id);

        $start_time = Carbon::parse($request->start_date)->format('Y-m-d') . ' ' . Carbon::parse($request->start_time)->format('H:i:s');
        $start_time = Carbon::createFromFormat('Y-m-d H:i:s', $start_time, $this->global->timezone)->setTimezone('UTC');
        $end_time = Carbon::parse($request->end_date)->format('Y-m-d') . ' ' . Carbon::parse($request->end_time)->format('H:i:s');
        $end_time = Carbon::createFromFormat('Y-m-d H:i:s', $end_time, $this->global->timezone)->setTimezone('UTC');

        if ($request->has('project_id')) {
            $timeLog->project_id = $request->project_id;
        }

        $timeLog->task_id = $request->task_id;

        if ($request->has('user_id')) {
            $userID = $request->user_id;
        }
        else {
            $userID = $timeLog->user_id;
        }

        $activeTimer = ProjectTimeLog::with('user')
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(
                        function ($q1) use ($start_time, $end_time) {
                            $q1->where('end_time', '>', $start_time->format('Y-m-d H:i:s'));
                            $q1->where('end_time', '<', $end_time->format('Y-m-d H:i:s'));
                        }
                    )
                    ->orWhere(
                        function ($q1) use ($start_time) {
                            $q1->whereDate('start_time', $start_time->format('Y-m-d'));
                            $q1->whereNull('end_time');
                        }
                    );
            })
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->where('user_id', $userID)
            ->where('project_time_logs.id', '!=', $id)
            ->select('project_time_logs.*')
            ->first();

        if (is_null($activeTimer)) {
            $timeLog->start_time = $start_time->format('Y-m-d H:i:s');
            $timeLog->end_time = $end_time->format('Y-m-d H:i:s');
            $timeLog->total_hours = (int)$end_time->diff($timeLog->start_time)->format('%d') * 24 + (int)$end_time->diff($timeLog->start_time)->format('%H');
            $timeLog->total_minutes = ((int)$timeLog->total_hours * 60) + (int)($end_time->diff($timeLog->start_time)->format('%i'));

            $timeLog->memo = $request->memo;
            $timeLog->user_id = $userID;
            $timeLog->edited_by_user = user()->id;
            $timeLog->save();
            return Reply::successWithData(__('messages.timeLogUpdated'), ['redirectUrl' => route('timelogs.index')]);
        }

        return Reply::error(__('messages.timelogAlreadyExist'));
    }

    public function show($id)
    {
        $this->pageTitle = __('modules.timeLogs.logTime');
        $this->editTimelogPermission = user()->permission('edit_timelogs');
        $this->timeLog = ProjectTimeLog::with('user', 'user.employeeDetail', 'project', 'task')->findOrFail($id);
        $viewProjectTimelogPermission = user()->permission('view_project_timelogs');

        abort_403(!(
            $this->viewTimelogPermission == 'all'
        || ($this->viewTimelogPermission == 'added' && $this->timeLog->added_by == user()->id)
        || ($this->viewTimelogPermission == 'owned'
            && (($this->timeLog->project && $this->timeLog->project->client_id == user()->id) || $this->timeLog->user_id == user()->id)
            )
        || ($this->viewTimelogPermission == 'both' && (($this->timeLog->project && $this->timeLog->project->client_id == user()->id) || $this->timeLog->user_id == user()->id || $this->timeLog->added_by == user()->id))
        ));


        if (request()->ajax()) {
            $html = view('timelogs.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'timelogs.ajax.show';
        return view('timelogs.create', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function showTimer()
    {
        $this->addTimelogPermission = user()->permission('add_timelogs');
        abort_403(!in_array($this->addTimelogPermission, ['all', 'added', 'owned', 'both']));

        $this->tasks = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
            ->with('project')
            ->pending()
            ->where('task_users.user_id', '=', $this->user->id)
            ->select('tasks.*')
            ->get();

        return view('timelogs.ajax.timer', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function startTimer(StartTimer $request)
    {
        $timeLog = new ProjectTimeLog();

        $activeTimer = ProjectTimeLog::with('user')
            ->whereNull('end_time')
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->where('user_id', $this->user->id)->first();

        if (is_null($activeTimer)) {
            $taskId = $request->task_id;

            if ($request->has('create_task')) {
                $task = new Task();
                $task->heading = $request->memo;
                $task->board_column_id = $this->global->default_task_status;
                $task->is_private = $request->has('is_private') && $request->is_private == 'true' ? 1 : 0;
                $task->start_date = Carbon::now($this->global->timezone)->format('Y-m-d');
                $task->due_date = Carbon::now($this->global->timezone)->format('Y-m-d');

                if ($request->project_id != '') {
                    $task->project_id = $request->project_id;
                }

                $task->save();
                $taskId = $task->id;
            }

            if ($request->project_id != '') {
                $timeLog->project_id = $request->project_id;
            }

            $timeLog->task_id = $taskId;

            $timeLog->user_id = $this->user->id;
            $timeLog->start_time = Carbon::now();
            $timeLog->hourly_rate = 0;
            $timeLog->memo = $request->memo;
            $timeLog->save();

            if ($request->project_id != '') {
                $this->logProjectActivity($request->project_id, 'modules.tasks.timerStartedBy');
                $this->logUserActivity($this->user->id, 'modules.tasks.timerStartedProject');
            }
            else {
                $this->logUserActivity($this->user->id, 'modules.tasks.timerStartedTask');
            }

            $this->logTaskActivity($timeLog->task_id, user()->id, 'timerStartedBy');

            return Reply::success(__('messages.timerStartedSuccessfully'));
        }

        return Reply::error(__('messages.timerAlreadyRunning'));
    }

    public function stopTimer(Request $request)
    {
        $timeId = $request->timeId;
        $timeLog = ProjectTimeLog::findOrFail($timeId);
        $editTimelogPermission = user()->permission('edit_timelogs');
        $activeTimelogPermission = user()->permission('manage_active_timelogs');

        abort_403(!(
            $editTimelogPermission == 'all'
        || ($editTimelogPermission == 'added' && $timeLog->added_by == user()->id)
        || ($editTimelogPermission == 'owned'
            && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id)
            )
        || ($editTimelogPermission == 'both' && (($timeLog->project && $timeLog->project->client_id == user()->id) || $timeLog->user_id == user()->id || $timeLog->added_by == user()->id))
        ));

        $timeLog->end_time = Carbon::now();
        $timeLog->save();

        $timeLog->total_hours = (int)$timeLog->end_time->diff($timeLog->start_time)->format('%d') * 24 + (int)$timeLog->end_time->diff($timeLog->start_time)->format('%H');
        $timeLog->total_minutes = ((int)$timeLog->total_hours * 60) + (int)($timeLog->end_time->diff($timeLog->start_time)->format('%i'));
        $timeLog->edited_by_user = $this->user->id;
        $timeLog->save();

        if (!is_null($timeLog->project_id)) {
            $this->logProjectActivity($timeLog->project_id, 'modules.tasks.timerStoppedBy');
        }

        if (!is_null($timeLog->task_id)) {
            $this->logTaskActivity($timeLog->task_id, user()->id, 'timerStoppedBy');
        }

        $this->logUserActivity($this->user->id, 'modules.tasks.timerStoppedBy');

        return Reply::success(__('messages.timerStoppedSuccessfully'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function showActiveTimer()
    {
        $this->activeTimers = ProjectTimeLog::with('task', 'user')->whereNull('end_time');
        $this->viewTimelogPermission = user()->permission('view_timelogs');

        abort_403($this->viewTimelogPermission == 'none' || in_array('client', user_roles()));

        if ($this->viewTimelogPermission == 'owned') {
            $this->activeTimers->where('user_id', user()->id);
        }

        if ($this->viewTimelogPermission == 'added') {
            $this->activeTimers->where('added_by', user()->id);
        }

        if ($this->viewTimelogPermission == 'both') {
            $this->activeTimers->where(function ($q) {
                $q->where('user_id', '=', user()->id);

                $q->orWhere('added_by', '=', user()->id);
            });
        }

        $this->activeTimers = $this->activeTimers->get();

        return view('timelogs.ajax.active_timer', $this->data);
    }

    public function byEmployee()
    {
        $viewPermission = $this->viewTimelogPermission;
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->employees = User::allEmployees();
        $this->projects = Project::allProjects();
        $this->timeLogProjects = $this->projects;
        $this->tasks = Task::all();
        $this->timeLogTasks = $this->tasks;

        $this->activeTimers = $this->activeTimerCount;

        $this->startDate = Carbon::now()->startOfMonth();
        $this->endDate = Carbon::now();
        return view('timelogs.by_employee', $this->data);
    }

    public function employeeData(Request $request)
    {
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
        $employee = $request->employee;
        $projectId = $request->projectID;
        $this->viewTimelogPermission = user()->permission('view_timelogs');

        $this->employees = User::join('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->leftJoin('project_time_logs', 'project_time_logs.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id');

        $where = '';

        if ($projectId != 'all') {
            $where .= ' and project_time_logs.project_id="' . $projectId . '" ';
        }

        $this->employees = $this->employees->select(
            'users.name',
            'users.image',
            'users.id',
            'designations.name as designation_name',
            DB::raw(
                "(SELECT SUM(project_time_logs.total_minutes) FROM project_time_logs WHERE project_time_logs.user_id = users.id and DATE(project_time_logs.start_time) >= '" . $startDate . "' and DATE(project_time_logs.start_time) <= '" . $endDate . "' '" . $where . "' GROUP BY project_time_logs.user_id) as total_minutes"
            ),
            DB::raw(
                "(SELECT SUM(project_time_logs.earnings) FROM project_time_logs WHERE project_time_logs.user_id = users.id and DATE(project_time_logs.start_time) >= '" . $startDate . "' and DATE(project_time_logs.start_time) <= '" . $endDate . "' '" . $where . "' GROUP BY project_time_logs.user_id) as earnings"
            )
        );

        if (!is_null($employee) && $employee !== 'all') {
            $this->employees = $this->employees->where('project_time_logs.user_id', $employee);
        }

        if (!is_null($projectId) && $projectId !== 'all') {
            $this->employees = $this->employees->where('project_time_logs.project_id', '=', $projectId);
        }

        if ($this->viewTimelogPermission == 'owned') {
            $this->employees = $this->employees->where('project_time_logs.user_id', user()->id);
        }

        if ($this->viewTimelogPermission == 'added') {
            $this->employees = $this->employees->where('project_time_logs.added_by', user()->id);
        }

        if ($this->viewTimelogPermission == 'both') {
            $this->employees = $this->employees->where(function ($q) {
                $q->where('project_time_logs.added_by', user()->id)
                    ->orWhere('project_time_logs.user_id', '=', user()->id);
            });
        }

        $this->employees = $this->employees->groupBy('project_time_logs.user_id')
            ->orderBy('users.name')
            ->get();

        $html = view('timelogs.ajax.member-list', $this->data)->render();
        return Reply::dataOnly(['html' => $html]);
    }

    public function userTimelogs(Request $request)
    {
        $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
        $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();
        $employee = $request->employee;
        $projectId = $request->projectID;

        $this->timelogs = ProjectTimeLog::with('project', 'task')->select('*')
            ->whereDate('start_time', '>=', $startDate)
            ->whereDate('start_time', '<=', $endDate)
            ->whereNotNull('end_time')
            ->where('user_id', $employee);

        if ($projectId != 'all') {
            $this->timelogs = $this->timelogs->where('project_id', $projectId);
        }

        $this->timelogs = $this->timelogs->orderBy('end_time', 'desc')
            ->get();

        $html = view('timelogs.ajax.user-timelogs', $this->data)->render();
        return Reply::dataOnly(['html' => $html]);
    }

    public function approveTimelog(Request $request)
    {
        ProjectTimeLog::where('id', $request->id)->update(
            [
                'approved' => 1,
                'approved_by' => user()->id
            ]
        );
        return Reply::dataOnly(['status' => 'success']);
    }

    public function export()
    {
        return Excel::download(new EmployeeTimelogs, 'timelogs.xlsx');
    }

}
