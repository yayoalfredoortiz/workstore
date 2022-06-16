<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use App\Helper\Files;
use App\Helper\Reply;
use App\Models\Leave;
use App\Models\Holiday;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Exports\AttendanceExport;
use App\Imports\AttendanceImport;
use App\Jobs\ImportAttendanceJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\HeadingRowImport;
use App\Exports\AttendanceByMemberExport;
use App\Http\Requests\Attendance\StoreAttendance;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Attendance\StoreBulkAttendance;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;

class AttendanceController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.attendance';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('attendance', $this->user->modules));
            $this->viewAttendancePermission = user()->permission('view_attendance');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $managePermission = user()->permission('manage_attendance');

        abort_403(!(in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both']) || $managePermission == 'all'));

        if (request()->ajax()) {
            return $this->summaryData($request);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $this->employees = User::where('id', user()->id)->get();

        } elseif ($this->viewAttendancePermission == 'all' || $managePermission == 'all') {
            $this->employees = User::allEmployees();
        }

        $now = Carbon::now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');
        $this->departments = Team::all();

        return view('attendances.index', $this->data);
    }

    public function summaryData($request)
    {
        $employees = User::with(
            ['attendance' => function ($query) use ($request) {
                $query->whereRaw('MONTH(attendances.clock_in_time) = ?', [$request->month])
                    ->whereRaw('YEAR(attendances.clock_in_time) = ?', [$request->year]);

                if ($request->late != 'all') {
                    $query = $query->where('attendances.late', $request->late);
                }

                if ($this->viewAttendancePermission == 'added') {
                    $query = $query->where('attendances.added_by', user()->id);

                } elseif ($this->viewAttendancePermission == 'owned') {
                    $query = $query->where('attendances.user_id', user()->id);
                }
            },
            'leaves' => function ($query) use ($request) {
                $query->whereRaw('MONTH(leaves.leave_date) = ?', [$request->month])
                    ->whereRaw('YEAR(leaves.leave_date) = ?', [$request->year])
                    ->where('status', 'approved');
            }]
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image')
            ->where('roles.name', '<>', 'client')->groupBy('users.id');

        if ($request->department != 'all') {
            $employees = $employees->where('employee_details.department_id', $request->department);
        }

        if ($request->userId != 'all') {
            $employees = $employees->where('users.id', $request->userId);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $employees = $employees->where('users.id', user()->id);
        }

        $employees = $employees->get();

        $this->holidays = Holiday::whereRaw('MONTH(holidays.date) = ?', [$request->month])->whereRaw('YEAR(holidays.date) = ?', [$request->year])->get();

        $final = [];
        $holidayOccasions = [];

        $this->daysInMonth = Carbon::parse('01-' . $request->month . '-' . $request->year)->daysInMonth;
        $now = Carbon::now()->timezone($this->global->timezone);
        $requestedDate = Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year))->endOfMonth();

        foreach ($employees as $employee) {

            $dataBeforeJoin = null;

            $dataTillToday = array_fill(1, $now->copy()->format('d'), 'Absent');

            if (($now->copy()->format('d') != $this->daysInMonth) && !$requestedDate->isPast()) {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), ((int)$this->daysInMonth - (int)$now->copy()->format('d')), '-');
            }
            else {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), ((int)$this->daysInMonth - (int)$now->copy()->format('d')), 'Absent');
            }

            $final[$employee->id . '#' . $employee->name] = array_replace($dataTillToday, $dataFromTomorrow);

            foreach ($employee->attendance as $attendance) {
                $final[$employee->id . '#' . $employee->name][Carbon::parse($attendance->clock_in_time)->timezone($this->global->timezone)->day] = '<a href="javascript:;" class="view-attendance" data-attendance-id="' . $attendance->id . '"><i class="fa fa-check text-primary"></i></a>';
            }

            $emplolyeeName = view('components.employee', [
                'user' => $employee
            ]);

            $final[$employee->id . '#' . $employee->name][] = $emplolyeeName;

            if ($employee->employeeDetail->joining_date->greaterThan(Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year)))) {
                if($request->month == $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')){
                    if($employee->employeeDetail->joining_date->format('d') == '01'){
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '-');
                    }
                    else{
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->subDay()->format('d'), '-');
                    }
                }

                if(($request->month < $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')) || $request->year < $employee->employeeDetail->joining_date->format('Y'))
                {
                    $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
                }
            }

            if(Carbon::parse('01-' . $request->month . '-' . $request->year)->isFuture()){
                $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
            }

            if(!is_null($dataBeforeJoin)){
                $final[$employee->id . '#' . $employee->name] = array_replace($final[$employee->id . '#' . $employee->name], $dataBeforeJoin);
            }

            foreach ($employee->leaves as $leave) {
                $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Leave';
            }

            foreach ($this->holidays as $holiday) {
                if ($final[$employee->id . '#' . $employee->name][$holiday->date->day] == 'Absent' || $final[$employee->id . '#' . $employee->name][$holiday->date->day] == '-') {
                    $final[$employee->id . '#' . $employee->name][$holiday->date->day] = 'Holiday';
                    $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                }
            }
        }

        $this->employeeAttendence = $final;
        $this->holidayOccasions = $holidayOccasions;

        $view = view('attendances.ajax.summary_data', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $managePermission = user()->permission('manage_attendance');
        $viewPermission = user()->permission('view_attendance');
        $attendance = Attendance::with('user', 'user.employeeDetail')->findOrFail($id);

        abort_403(!(
            $viewPermission == 'all'
            || ($viewPermission == 'added' && $attendance->added_by == user()->id)
            || ($viewPermission == 'owned' && $attendance->user->id == user()->id)
            || ($viewPermission == 'both' && ($attendance->added_by == user()->id || $attendance->user->id == user()->id))
            || $managePermission == 'all')
        );

        $this->attendanceActivity = Attendance::userAttendanceByDate($attendance->clock_in_time->format('Y-m-d'), $attendance->clock_in_time->format('Y-m-d'), $attendance->user_id);

        $attendanceActivity = clone $this->attendanceActivity;
        $attendanceActivity = $attendanceActivity->reverse()->values();

        $defaultEndTime = $settingEndTime = Carbon::createFromFormat('H:i:s', attendance_setting()->office_end_time, $this->global->timezone);

        if ($settingEndTime->greaterThan(now()->timezone($this->global->timezone))) {
            $defaultEndTime = now()->timezone($this->global->timezone);
        }

        $this->totalTime = 0;

        foreach ($attendanceActivity as $key => $activity) {
            if ($key == 0) {
                $this->firstClockIn = $activity;
                $this->startTime = Carbon::parse($this->firstClockIn->clock_in_time)->timezone($this->global->timezone);
            }

            $this->lastClockOut = $activity;

            if (!is_null($this->lastClockOut->clock_out_time)) {
                $this->endTime = Carbon::parse($this->lastClockOut->clock_out_time)->timezone($this->global->timezone);

            } elseif (($this->lastClockOut->clock_in_time->timezone($this->global->timezone)->format('Y-m-d') != Carbon::now()->timezone($this->global->timezone)->format('Y-m-d')) && is_null($this->lastClockOut->clock_out_time)) {
                $this->endTime = Carbon::parse($this->startTime->format('Y-m-d') . ' ' . attendance_setting()->office_end_time, $this->global->timezone);
                $this->notClockedOut = true;

            } else {
                $this->endTime = $defaultEndTime;
                $this->notClockedOut = true;
            }

            $this->totalTime = $this->totalTime + $this->endTime->timezone($this->global->timezone)->diffInMinutes($activity->clock_in_time->timezone($this->global->timezone), true);
        }

        $totalTime = intdiv($this->totalTime, 60) . ' ' . __('app.hrs') . ' ';

        if (($this->totalTime % 60) > 0) {
            $totalTime .= ($this->totalTime % 60) . ' ' . __('app.mins');
        }

        $this->totalTime = $totalTime;

        $this->attendance = $attendance;

        return view('attendances.ajax.show', $this->data);

    }

    public function edit($id)
    {
        $attendance = Attendance::find($id);

        $this->date = $attendance->clock_in_time->format('Y-m-d');
        $this->row = $attendance;
        $this->clock_in = 1;
        $this->userid = $attendance->user_id;
        $this->total_clock_in  = Attendance::where('user_id', $attendance->user_id)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $this->date)
            ->whereNull('attendances.clock_out_time')->count();
        $this->type = 'edit';

        $this->maxAttendanceInDay = attendance_setting()->clockin_in_day;
        return view('attendances.ajax.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $date = Carbon::parse($request->attendance_date)->format('Y-m-d');
        $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date . ' ' . $request->clock_in_time, $this->global->timezone);
        $clockIn->setTimezone('UTC');

        if ($request->clock_out_time != '') {
            $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date . ' ' . $request->clock_out_time, $this->global->timezone);
            $clockOut->setTimezone('UTC');

            if ($clockIn->gt($clockOut) && !is_null($clockOut)) {
                return Reply::error(__('messages.clockOutTimeError'));
            }

            $clockIn = $clockIn->toDateTimeString();
            $clockOut = $clockOut->toDateTimeString();
        }
        else {
            $clockOut = null;
        }

        $attendance->user_id = $request->user_id;
        $attendance->clock_in_time = $clockIn;
        $attendance->clock_in_ip = $request->clock_in_ip;
        $attendance->clock_out_time = $clockOut;
        $attendance->clock_out_ip = $request->clock_out_ip;
        $attendance->working_from = $request->working_from;
        $attendance->late = ($request->has('late')) ? 'yes' : 'no';
        $attendance->half_day = ($request->has('halfday')) ? 'yes' : 'no';
        $attendance->save();

        return Reply::success(__('messages.attendanceSaveSuccess'));
    }

    public function mark(Request $request, $userid, $day, $month, $year)
    {
        $userDetail = User::find($userid);

        $this->date = Carbon::createFromFormat('d-m-Y', $day . '-' . $month . '-' . $year)->format('Y-m-d');
        $this->row = Attendance::attendanceByUserDate($userid, $this->date);
        $this->clock_in = 0;
        $this->total_clock_in = Attendance::where('user_id', $userid)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $this->date)
            ->whereNull('attendances.clock_out_time')->count();

        $this->userid = $userid;
        $this->type = 'add';
        $this->maxAttendanceInDay = attendance_setting()->clockin_in_day;
        return view('attendances.ajax.edit', $this->data);
    }

    public function store(StoreAttendance $request)
    {
        $date = Carbon::parse($request->attendance_date)->format('Y-m-d');
        $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date . ' ' . $request->clock_in_time, $this->global->timezone);
        $clockIn->setTimezone('UTC');

        if ($request->clock_out_time != '') {
            $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date . ' ' . $request->clock_out_time, $this->global->timezone);
            $clockOut->setTimezone('UTC');

            if ($clockIn->gt($clockOut) && !is_null($clockOut)) {
                return Reply::error(__('messages.clockOutTimeError'));
            }

            $clockIn = $clockIn->toDateTimeString();
            $clockOut = $clockOut->toDateTimeString();
        }
        else {
            $clockOut = null;
        }

        $attendance = Attendance::where('user_id', $request->user_id)
            ->where(DB::raw('DATE(`clock_in_time`)'), $date)
            ->whereNull('clock_out_time')
            ->first();

        $clockInCount = Attendance::getTotalUserClockIn($date, $request->user_id);

        if (!is_null($attendance)) {
            $attendance->update([
                'user_id' => $request->user_id,
                'clock_in_time' => $clockIn,
                'clock_in_ip' => $request->clock_in_ip,
                'clock_out_time' => $clockOut,
                'clock_out_ip' => $request->clock_out_ip,
                'working_from' => $request->working_from,
                'late' => ($request->has('late')) ? 'yes' : 'no',
                'half_day' => ($request->has('halfday')) ? 'yes' : 'no'
            ]);
        }
        else {

            // Check maximum attendance in a day
            if ($clockInCount < attendance_setting()->clockin_in_day) {
                Attendance::create([
                    'user_id' => $request->user_id,
                    'clock_in_time' => $clockIn,
                    'clock_in_ip' => $request->clock_in_ip,
                    'clock_out_time' => $clockOut,
                    'clock_out_ip' => $request->clock_out_ip,
                    'working_from' => $request->working_from,
                    'late' => ($request->has('late')) ? 'yes' : 'no',
                    'half_day' => ($request->has('halfday')) ? 'yes' : 'no'
                ]);
            }
            else {
                return Reply::error(__('messages.maxColckIn'));
            }
        }

        return Reply::success(__('messages.attendanceSaveSuccess'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function byMember()
    {
        $managePermission = user()->permission('manage_attendance');
        $this->pageTitle = 'modules.attendance.attendanceByMember';

        abort_403(!(in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both']) || $managePermission == 'all'));

        if ($this->viewAttendancePermission == 'owned') {
            $this->employees = User::where('id', user()->id)->get();

        } else {
            $this->employees = User::allEmployees();
        }

        $now = Carbon::now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');

        return view('attendances.by_member', $this->data);
    }

    public function employeeData(Request $request, $startDate = null, $endDate = null, $userId = null)
    {
        $ant = []; // Array For attendance Data indexed by similar date
        $dateWiseData = []; // Array For Combine Data

        $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        $userId = $request->userId;

        $attendances = Attendance::userAttendanceByDate($startDate, $endDate, $userId); // Getting Attendance Data
        $holidays = Holiday::getHolidayByDates($startDate, $endDate); // Getting Holiday Data

        $openDays = json_decode(attendance_setting()->office_open_days);
        $userId = $request->userId;

        $totalWorkingDays = $startDate->diffInDaysFiltered(function (Carbon $date) use ($openDays) {
            foreach ($openDays as $day) {
                if ($date->dayOfWeek == $day) {
                    return $date;
                }
            }
        }, $endDate);
        $daysPresent = Attendance::countDaysPresentByUser($startDate, $endDate, $userId);
        $daysLate = Attendance::countDaysLateByUser($startDate, $endDate, $userId);
        $halfDays = Attendance::countHalfDaysByUser($startDate, $endDate, $userId);
        $daysAbsent = (($totalWorkingDays - $daysPresent) < 0) ? '0' : ($totalWorkingDays - $daysPresent);
        $holidayCount = Count($holidays);

        // Getting Leaves Data
        $leavesDates = Leave::where('user_id', $userId)
            ->where('leave_date', '>=', $startDate)
            ->where('leave_date', '<=', $endDate)
            ->where('status', 'approved')
            ->select('leave_date', 'reason', 'duration')
            ->get()->keyBy('date')->toArray();

        $holidayData = $holidays->keyBy('holiday_date');
        $holidayArray = $holidayData->toArray();

        // Set Date as index for same date clock-ins
        foreach ($attendances as $attand) {
            $ant[$attand->clock_in_date][] = $attand; // Set attendance Data indexed by similar date
        }

        // Set All Data in a single Array
        // @codingStandardsIgnoreStart

        for($date = $endDate; $date->diffInDays($startDate) > 0; $date->subDay()) {
        // @codingStandardsIgnoreEnd

            if ($date->isPast() || $date->isToday()) {

                // Set default array for record
                $dateWiseData[$date->toDateString()] = [
                    'holiday' => false,
                    'attendance' => false,
                    'leave' => false
                ];

                // Set Holiday Data
                if (array_key_exists($date->toDateString(), $holidayArray)) {
                    $dateWiseData[$date->toDateString()]['holiday'] = $holidayData[$date->toDateString()];
                }

                // Set Attendance Data
                if (array_key_exists($date->toDateString(), $ant)) {
                    $dateWiseData[$date->toDateString()]['attendance'] = $ant[$date->toDateString()];
                }

                // Set Leave Data
                if (array_key_exists($date->toDateString(), $leavesDates)) {
                    $dateWiseData[$date->toDateString()]['leave'] = $leavesDates[$date->toDateString()];
                }
            }
        }

        if ($startDate->isPast() || $startDate->isToday()) {
            // Set default array for record
            $dateWiseData[$startDate->toDateString()] = [
                'holiday' => false,
                'attendance' => false,
                'leave' => false
            ];

            // Set Holiday Data
            if (array_key_exists($startDate->toDateString(), $holidayArray)) {
                $dateWiseData[$startDate->toDateString()]['holiday'] = $holidayData[$startDate->toDateString()];
            }

            // Set Attendance Data
            if (array_key_exists($startDate->toDateString(), $ant)) {
                $dateWiseData[$startDate->toDateString()]['attendance'] = $ant[$startDate->toDateString()];
            }

            // Set Leave Data
            if (array_key_exists($startDate->toDateString(), $leavesDates)) {
                $dateWiseData[$startDate->toDateString()]['leave'] = $leavesDates[$startDate->toDateString()];
            }
        }

        // Getting View data
        $view = view('attendances.ajax.user_attendance', ['dateWiseData' => $dateWiseData, 'global' => $this->global])->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view, 'daysPresent' => $daysPresent, 'daysLate' => $daysLate, 'halfDays' => $halfDays, 'totalWorkingDays' => $totalWorkingDays, 'absentDays' => $daysAbsent, 'holidays' => $holidayCount]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $managePermission = user()->permission('manage_attendance');
        $addPermission = user()->permission('add_attendance');

        abort_403 (!($addPermission == 'all' || $addPermission == 'added' || $managePermission == 'all'));
        $this->employees = User::allEmployees();
        $this->departments = Team::allDepartments();
        $this->pageTitle = __('modules.attendance.markAttendance');
        $this->year = now()->format('Y');
        $this->month = now()->format('m');

        if (request()->ajax()) {
            $html = view('attendances.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'attendances.ajax.create';

        return view('attendances.create', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function bulkMark(StoreBulkAttendance $request)
    {
        $employees = $request->user_id;
        $employeeData = User::with('employeeDetail')->whereIn('id', $employees)->get();

        $date = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->format('Y-m-d');
        $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date . ' ' . $request->clock_in_time, $this->global->timezone);
        $clockIn->setTimezone('UTC');

        if ($request->clock_out_time != '') {
            $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date . ' ' . $request->clock_out_time, $this->global->timezone);
            $clockOut->setTimezone('UTC');

            if ($clockIn->gt($clockOut) && !is_null($clockOut)) {
                return Reply::error(__('messages.clockOutTimeError'));
            }

            $clockIn = $clockIn->toDateTimeString();
            $clockOut = $clockOut->toDateTimeString();
        }

        $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $request->month . '-' . $request->year)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $period = CarbonPeriod::create($startDate, $endDate);

        $holidays = Holiday::getHolidayByDates($startDate->format('Y-m-d'), $endDate->format('Y-m-d'))->pluck('holiday_date')->toArray();

        $insertData = [];
        $currentDate = Carbon::now();

        foreach ($employees as $key => $userId) {
            $userData = $employeeData->filter(function ($value) use($userId) {
                return $value->id == $userId;
            })->first();

            foreach ($period as $date) {
                $attendance = Attendance::where('user_id', $userId)
                    ->where(DB::raw('DATE(`clock_in_time`)'), $date->format('Y-m-d'))
                    ->first();

                if (is_null($attendance) && $date->greaterThanOrEqualTo($userData->employeeDetail->joining_date) && $date->lessThanOrEqualTo($currentDate)) { // Attendance should not exist for the user for the same date
                    if (!in_array($date->format('Y-m-d'), $holidays)) { // date should not be a holiday

                        $clockIn = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date->format('Y-m-d') . ' ' . $request->clock_in_time, $this->global->timezone);
                        $clockIn->setTimezone('UTC');

                        $clockOut = Carbon::createFromFormat('Y-m-d ' . $this->global->time_format, $date->format('Y-m-d') . ' ' . $request->clock_out_time, $this->global->timezone);
                        $clockOut->setTimezone('UTC');

                        $insertData[] = [
                            'user_id' => $userId,
                            'clock_in_time' => $clockIn,
                            'clock_in_ip' => request()->ip(),
                            'clock_out_time' => $clockOut,
                            'clock_out_ip' => request()->ip(),
                            'working_from' => $request->working_from,
                            'late' => $request->late,
                            'half_day' => $request->halfday,
                            'added_by' => user()->id,
                            'last_updated_by' => user()->id
                        ];
                    }
                }
            }
        }

        Attendance::insertOrIgnore($insertData);

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('attendances.index');
        }

        return Reply::redirect($redirectUrl, __('messages.attendanceSaveSuccess'));
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $deleteAttendancePermission = user()->permission('delete_attendance');
        $manageAttendancePermission = user()->permission('manage_attendance');

        abort_403 (!($deleteAttendancePermission == 'all' || $manageAttendancePermission == 'all' || ($deleteAttendancePermission == 'added' && $attendance->added_by == user()->id)));
        Attendance::destroy($id);
        return Reply::success(__('messages.attendanceDelete'));
    }

    public function importAttendance()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.menu.attendance');

        $managePermission = user()->permission('manage_attendance');
        $addPermission = user()->permission('add_attendance');

        abort_403 (!($addPermission == 'all' || $addPermission == 'added' || $managePermission == 'all'));


        if (request()->ajax()) {
            $html = view('attendances.ajax.import', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'attendances.ajax.import';

        return view('attendances.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $this->file = Files::upload($request->import_file, 'import-files', false, false, false);
        $excelData = Excel::toArray(new AttendanceImport, public_path('user-uploads/import-files/' . $this->file))[0];
        $this->hasHeading = $request->has('heading');
        $this->heading = array();

        $this->columns = AttendanceImport::$field;
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

        $view = view('attendances.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        // clear previous import
        Artisan::call('queue:clear database --queue=import_attendance');
        Artisan::call('queue:flush');
        // Get index of an array not null value with key
        $columns = array_filter($request->columns, function ($value) {
            return $value !== null;
        });

        $excelData = Excel::toArray(new AttendanceImport, public_path('user-uploads/import-files/' . $request->file))[0];

        if ($request->has_heading) {
            array_shift($excelData);
        }

        $jobs = [];

        foreach ($excelData as $row) {

            $jobs[] = (new ImportAttendanceJob($row, $columns));
        }

        $batch = Bus::batch($jobs)->onConnection('database')->onQueue('import_attendance')->name('import_attendance')->dispatch();

        Files::deleteFile($request->file, 'import-files');

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function exportAttendanceByMemeber($year, $month, $id)
    {
        $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $month . '-' . $year)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        $obj = User::findOrFail($id);
        $date = $endDate->lessThan(Carbon::now()) ? $endDate : Carbon::now();

        return Excel::download(new AttendanceByMemberExport($year, $month, $id, $obj->name, $startDate, $endDate), $obj->name.'_'.$startDate->format('d-m-Y').'_To_'.$date->format('d-m-Y').'.xlsx');
    }

    public function exportAllAttendance($year, $month, $id, $late, $department)
    {
        $startDate = Carbon::createFromFormat('d-m-Y', '01-' . $month . '-' . $year)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $date = $endDate->lessThan(Carbon::now()) ? $endDate : Carbon::now();
        return Excel::download(new AttendanceExport($year, $month, $id, $late, $department, $startDate, $endDate), 'Attendance_From_'.$startDate->format('d-m-Y').'_To_'.$date->format('d-m-Y').'.xlsx');
    }

    public function byHour(Request $request)
    {
        $managePermission = user()->permission('manage_attendance');
        $this->pageTitle = 'modules.attendance.attendanceByHour';

        abort_403(!(in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both']) || $managePermission == 'all'));

        if (request()->ajax()) {
            return $this->hourSummaryData($request);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $this->employees = User::where('id', user()->id)->get();

        } elseif ($this->viewAttendancePermission == 'all' || $managePermission == 'all') {
            $this->employees = User::allEmployees();
        }

        $now = Carbon::now(global_setting()->timezone);
        $this->year = $now->format('Y');
        $this->month = $now->format('m');
        $this->departments = Team::all();

        return view('attendances.by_hour', $this->data);
    }

    public function hourSummaryData($request)
    {
        $employees = User::with(
            ['attendance' => function ($query) use ($request) {
                $query->whereRaw('MONTH(attendances.clock_in_time) = ?', [$request->month])
                    ->whereRaw('YEAR(attendances.clock_in_time) = ?', [$request->year]);

                if ($request->late != 'all') {
                    $query = $query->where('attendances.late', $request->late);
                }

                if ($this->viewAttendancePermission == 'added') {
                    $query = $query->where('attendances.added_by', user()->id);

                } elseif ($this->viewAttendancePermission == 'owned') {
                    $query = $query->where('attendances.user_id', user()->id);
                }
            },
            'leaves' => function ($query) use ($request) {
                $query->whereRaw('MONTH(leaves.leave_date) = ?', [$request->month])
                    ->whereRaw('YEAR(leaves.leave_date) = ?', [$request->year])
                    ->where('status', 'approved');
            }]
        )->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at', 'employee_details.department_id', 'users.image')
            ->where('roles.name', '<>', 'client')->groupBy('users.id');

        if ($request->department != 'all') {
            $employees = $employees->where('employee_details.department_id', $request->department);
        }

        if ($request->userId != 'all') {
            $employees = $employees->where('users.id', $request->userId);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $employees = $employees->where('users.id', user()->id);
        }

        $employees = $employees->get();

        $this->holidays = Holiday::whereRaw('MONTH(holidays.date) = ?', [$request->month])->whereRaw('YEAR(holidays.date) = ?', [$request->year])->get();

        $final = [];
        $holidayOccasions = [];

        $this->daysInMonth = Carbon::parse('01-' . $request->month . '-' . $request->year)->daysInMonth;
        $now = Carbon::now()->timezone($this->global->timezone);
        $requestedDate = Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year))->endOfMonth();

        foreach ($employees as $employee) {

            $dataBeforeJoin = null;

            $dataTillToday = array_fill(1, $now->copy()->format('d'), 'Absent');

            if (($now->copy()->format('d') != $this->daysInMonth) && !$requestedDate->isPast()) {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), ((int)$this->daysInMonth - (int)$now->copy()->format('d')), '-');
            }
            else {
                $dataFromTomorrow = array_fill($now->copy()->addDay()->format('d'), ((int)$this->daysInMonth - (int)$now->copy()->format('d')), 'Absent');
            }

            $final[$employee->id . '#' . $employee->name] = array_replace($dataTillToday, $dataFromTomorrow);

            foreach ($employee->attendance as $attendance) {
                $to = $attendance->clock_out_time ? \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $attendance->clock_out_time) : null;
                $from = $attendance->clock_in_time ? \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $attendance->clock_in_time) : null;

                $diff_in_hours = ($to && $from) ? $to->diffInHours($from) : 0;

                $final[$employee->id . '#' . $employee->name][Carbon::parse($attendance->clock_in_time)->timezone($this->global->timezone)->day] = '<a href="javascript:;" class="view-attendance" data-attendance-id="' . $attendance->id . '">'. $diff_in_hours .'</a>';
            }

            $emplolyeeName = view('components.employee', [
                'user' => $employee
            ]);

            $final[$employee->id . '#' . $employee->name][] = $emplolyeeName;

            if ($employee->employeeDetail->joining_date->greaterThan(Carbon::parse(Carbon::parse('01-' . $request->month . '-' . $request->year)))) {
                if($request->month == $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')){
                    if($employee->employeeDetail->joining_date->format('d') == '01'){
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->format('d'), '-');
                    }
                    else{
                        $dataBeforeJoin = array_fill(1, $employee->employeeDetail->joining_date->subDay()->format('d'), '-');
                    }
                }

                if(($request->month < $employee->employeeDetail->joining_date->format('m') && $request->year == $employee->employeeDetail->joining_date->format('Y')) || $request->year < $employee->employeeDetail->joining_date->format('Y'))
                {
                    $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
                }
            }

            if(Carbon::parse('01-' . $request->month . '-' . $request->year)->isFuture()){
                $dataBeforeJoin = array_fill(1, $this->daysInMonth, '-');
            }

            if(!is_null($dataBeforeJoin)){
                $final[$employee->id . '#' . $employee->name] = array_replace($final[$employee->id . '#' . $employee->name], $dataBeforeJoin);
            }

            foreach ($employee->leaves as $leave) {
                $final[$employee->id . '#' . $employee->name][$leave->leave_date->day] = 'Leave';
            }

            foreach ($this->holidays as $holiday) {
                if ($final[$employee->id . '#' . $employee->name][$holiday->date->day] == 'Absent' || $final[$employee->id . '#' . $employee->name][$holiday->date->day] == '-') {
                    $final[$employee->id . '#' . $employee->name][$holiday->date->day] = 'Holiday';
                    $holidayOccasions[$holiday->date->day] = $holiday->occassion;
                }
            }
        }

        $this->employeeAttendence = $final;
        $this->holidayOccasions = $holidayOccasions;

        $view = view('attendances.ajax.hour_summary_data', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

    public function byMapLocation(Request $request)
    {
        $managePermission = user()->permission('manage_attendance');

        abort_403(!(in_array($this->viewAttendancePermission, ['all', 'added', 'owned', 'both']) || $managePermission == 'all'));

        if (request()->ajax()) {
            return $this->byMapLocationData($request);
        }

        $this->employees = User::allEmployees();
        $this->departments = Team::all();

        return view('attendances.by_map_location', $this->data);
    }

    protected function byMapLocationData($request)
    {
        $this->attendances = Attendance::with('user')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->select('attendances.*')
            ->whereDate('clock_in_time', Carbon::createFromFormat($this->global->date_format, $request->attendance_date)->toDateString())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->department != 'all') {
            $this->attendances = $this->attendances->where('employee_details.department_id', $request->department);
        }

        if ($request->userId != 'all') {
            $this->attendances = $this->attendances->where('users.id', $request->userId);
        }

        if ($this->viewAttendancePermission == 'owned') {
            $this->attendances = $this->attendances->where('users.id', user()->id);
        }

        if ($request->late != 'all') {
            $this->attendances = $this->attendances->where('attendances.late', $request->late);
        }

        $this->attendances = $this->attendances->get();

        $view = view('attendances.ajax.map_location_data', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'data' => $view]);
    }

}
