<?php

namespace App\Traits;

use App\Models\DashboardWidget;
use App\Models\Designation;
use App\Models\EmployeeDetails;
use App\Models\Leave;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 *
 */
trait HRDashboard
{
    use CurrencyExchange;

    /**
     *
     * @return void
     */
    public function hrDashboard()
    {
        abort_403(!($this->viewHRDashboard == 'all'));

        $this->pageTitle = 'app.hrDashboard';
        $this->startDate  = (request('startDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('startDate')) : now($this->global->timezone)->startOfMonth();
        $this->endDate = (request('endDate') != '') ? Carbon::createFromFormat($this->global->date_format, request('endDate')) : now($this->global->timezone);
        $startDate = $this->startDate->toDateString();
        $endDate = $this->endDate->toDateString();

        $this->widgets = DashboardWidget::where('dashboard_type', 'admin-hr-dashboard')->get();
        $this->activeWidgets = $this->widgets->filter(function ($value, $key) {
            return $value->status == '1';
        })->pluck('widget_name')->toArray();

        $this->totalLeavesApproved = Leave::whereBetween(DB::raw('DATE(`leave_date`)'), [$startDate, $endDate])->where('status', 'approved')->count();
        $this->totalNewEmployee = EmployeeDetails::whereBetween(DB::raw('DATE(`joining_date`)'), [$startDate, $endDate])->count();
        $this->totalEmployeeExits = EmployeeDetails::whereBetween(DB::raw('DATE(`last_date`)'), [$startDate, $endDate])->count();

        $attandance = EmployeeDetails::join('users', 'users.id', 'employee_details.user_id')
            ->join('attendances', 'attendances.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(attendances.`clock_in_time`)'), [$startDate, $endDate])
            ->select(DB::raw('count(users.id) as employeeCount'), DB::raw('DATE(attendances.clock_in_time) as date'))
            ->groupBy('date')
            ->get();

        if ($attandance->count() > 0) {
            try {
                $this->averageAttendance = number_format(((array_sum(array_column($attandance->toArray(), 'employeeCount')) / $attandance->count()) * 100) / User::allEmployees()->count(), 2) . '%';
            } catch (Exception $e) {
                $this->averageAttendance = '0%';
            }

        } else {
            $this->averageAttendance = '0%';
        }

        $this->departmentWiseChart = $this->departmentWiseChart($startDate, $endDate);
        $this->designationWiseChart = $this->designationWiseChart($startDate, $endDate);
        $this->genderWiseChart = $this->genderWiseChart($startDate, $endDate);
        $this->roleWiseChart = $this->roleWiseChart($startDate, $endDate);

        $this->leavesTaken = User::with('employeeDetail', 'employeeDetail.designation')
            ->join('leaves', 'leaves.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(leaves.`leave_date`)'), [$startDate, $endDate])
            ->where('leaves.status', 'approved')
            ->select(DB::raw('count(leaves.id) as employeeLeaveCount'), 'users.*')
            ->groupBy('users.id')
            ->orderBy('employeeLeaveCount', 'DESC')
            ->get();

        $this->lateAttendanceMarks = User::with('employeeDetail', 'employeeDetail.designation')
            ->join('attendances', 'attendances.user_id', 'users.id')
            ->whereBetween(DB::raw('DATE(attendances.`clock_in_time`)'), [$startDate, $endDate])
            ->where('late', 'yes')
            ->select(DB::raw('count(DISTINCT DATE(attendances.clock_in_time) ) as employeeLateCount'), 'users.*')
            ->groupBy('users.id')
            ->orderBy('employeeLateCount', 'DESC')
            ->get();


        $this->view = 'dashboard.ajax.hr';
    }

    public function departmentWiseChart($startDate, $endDate)
    {
        $departments = Team::withCount(['teamMembers' => function ($query) use ($startDate, $endDate) {
            return $query->whereBetween(DB::raw('DATE(`joining_date`)'), [$startDate, $endDate]);
        }])->get();

        $data['labels'] = $departments->pluck('team_name')->toArray();

        foreach ($data['labels'] as $key => $value) {
            $data['colors'][] = '#' . substr(md5($value), 0, 6);
        }

        $data['values'] = $departments->pluck('team_members_count')->toArray();

        return $data;
    }

    public function designationWiseChart($startDate, $endDate)
    {
        $departments = Designation::withCount(['members' => function ($query) use ($startDate, $endDate) {
            return $query->whereBetween(DB::raw('DATE(`joining_date`)'), [$startDate, $endDate]);
        }])->get();

        $data['labels'] = $departments->pluck('name')->toArray();

        foreach ($data['labels'] as $key => $value) {
            $data['colors'][] = '#' . substr(md5($value), 0, 6);
        }

        $data['values'] = $departments->pluck('members_count')->toArray();

        return $data;
    }

    public function genderWiseChart($startDate, $endDate)
    {

        $genderWiseEmployee = EmployeeDetails::join('users', 'users.id', 'employee_details.user_id')
            ->select(DB::raw('count(employee_details.id) as totalEmployee'), 'users.gender')
            ->whereBetween(DB::raw('DATE(employee_details.`joining_date`)'), [$startDate, $endDate])
            ->groupBy('users.gender')
            ->orderBy('users.gender', 'ASC')
            ->get();

        $labels = $genderWiseEmployee->pluck('gender')->toArray();

        $data['labels'] = [];

        foreach ($labels as $key => $value) {
            $data['labels'][] = __('app.' . $value);
        }

        $data['values'] = $genderWiseEmployee->pluck('totalEmployee')->toArray();
        $data['colors'] = ['#1d82f5', '#FCBD01', '#D30000'];
        return $data;
    }

    public function roleWiseChart($startDate, $endDate)
    {
        $roleWiseChart = Role::withCount(['users' => function ($query) use ($startDate, $endDate) {
            return $query->join('employee_details', 'users.id', 'employee_details.user_id')
                ->whereBetween(DB::raw('DATE(employee_details.`joining_date`)'), [$startDate, $endDate]);
        }])
            ->where('name', '<>', 'client')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($roleWiseChart as $key => $value) {
            if ($value->name == 'admin' || $value->name == 'employee') {
                $data['labels'][] = __('app.' . $value->name);
                $data['colors'][] = '#' . substr(md5($value->name), 0, 6);
            }
            else {
                $data['labels'][] = $value->display_name;
                $data['colors'][] = '#' . substr(md5($value), 0, 6);
            }
        }

        $data['values'] = $roleWiseChart->pluck('users_count')->toArray();
        return $data;
    }

}
