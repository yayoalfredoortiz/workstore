<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectTimeLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 *
 */
trait ProjectDashboard
{

    /**
     *
     * @return void
     */
    public function projectDashboard()
    {
        abort_403(!($this->viewProjectDashboard == 'all'));

        $this->pageTitle = 'app.projectDashboard';

        $this->startDate  = (request('startDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('startDate')) : now($this->global->timezone)->startOfMonth();

        $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('endDate')) : now($this->global->timezone);

        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        $this->totalProject = Project::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])->count();

        $hoursLogged = ProjectTimeLog::whereDate('start_time', '>=', $startDate)
            ->whereDate('end_time', '<=', $endDate)
            ->whereNotNull('project_id')
            ->where('approved', 1)
            ->sum('total_minutes');

        $timeLog = intdiv($hoursLogged, 60) . ' ' . __('app.hrs') . ' ';

        if (($hoursLogged % 60) > 0) {
            $timeLog .= ($hoursLogged % 60) . ' ' . __('app.mins');
        }

        $this->totalHoursLogged = $timeLog;

        $this->totalOverdueProject = Project::whereNotNull('deadline')
            ->where(DB::raw('DATE(deadline)'), '>=', $startDate)
            ->where(DB::raw('DATE(deadline)'), '<=', $endDate)
            ->count();

        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-project-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        $this->pendingMilestone = ProjectMilestone::whereBetween(DB::raw('DATE(project_milestones.`created_at`)'), [$startDate, $endDate])
            ->with('project', 'currency')
            ->get();

        $this->statusWiseProject = $this->statusChartData($startDate, $endDate);

        $this->view = 'dashboard.ajax.project';
    }

    public function statusChartData($startDate, $endDate)
    {
        $labels = ['in progress', 'on hold', 'not started', 'canceled', 'finished'];
        $data['labels'] = [__('app.inProgress'), __('app.onHold'), __('app.notStarted'), __('app.canceled'), __('app.finished')];
        $data['colors'] = ['#1d82f5', '#FCBD01', '#616e80', '#D30000', '#2CB100'];
        $data['values'] = [];

        foreach ($labels as $label) {
            $data['values'][] = Project::whereBetween(DB::raw('DATE(`created_at`)'), [$startDate, $endDate])->where('status', $label)->count();
        }

        return $data;
    }

}
