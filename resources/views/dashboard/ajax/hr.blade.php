<script src="{{ asset('vendor/jquery/frappe-charts.min.iife.js') }}"></script>

<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>

<div class="row">
    @if (in_array('leaves', $modules) && in_array('total_leaves_approved', $activeWidgets))
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">

            <a href="javascript:;" id="total-leaves-approved">
                <x-cards.widget :title="__('modules.dashboard.totalLeavesApproved')" :value="$totalLeavesApproved"
                    icon="plane-departure" :info="__('messages.leaveInfo')" />
            </a>
        </div>
    @endif

    @if (in_array('employees', $modules) && in_array('total_new_employee', $activeWidgets))
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="total-new-employees">
                <x-cards.widget :title="__('modules.dashboard.totalNewEmployee')" :value="$totalNewEmployee"
                    :info="__('messages.newEmployeeInfo')" icon="users" />
            </a>
        </div>
    @endif

    @if (in_array('employees', $modules) && in_array('total_employee_exits', $activeWidgets))
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="javascript:;" id="total-ex-employees">
                <x-cards.widget :title="__('modules.dashboard.totalEmployeeExits')" :value="$totalEmployeeExits"
                    icon="sign-out-alt" :info="__('messages.employeeExitInfo')" />
            </a>
        </div>
    @endif

    @if (in_array('attendance', $modules) && in_array('average_attendance', $activeWidgets))
        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
            <a href="{{ route('attendances.index') }}">
                <x-cards.widget :title="__('modules.dashboard.averageAttendance')" :value="$averageAttendance"
                    icon="fingerprint" />
            </a>
        </div>
    @endif

</div>

<div class="row">
    @if (in_array('employees', $modules) && in_array('department_wise_employee', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.departmentWiseEmployee')">
                <x-pie-chart id="task-chart1" :labels="$departmentWiseChart['labels']"
                    :values="$departmentWiseChart['values']" :colors="$departmentWiseChart['colors'] ?? null" height="300" width="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('employees', $modules) && in_array('designation_wise_employee', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.designationWiseEmployee')">
                <x-pie-chart id="task-chart2" :labels="$designationWiseChart['labels']"
                    :values="$designationWiseChart['values']" :colors="$designationWiseChart['colors'] ?? null" height="300" width="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('employees', $modules) && in_array('gender_wise_employee', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.genderWiseEmployee')">
                <x-pie-chart id="task-chart3" :labels="$genderWiseChart['labels']" :values="$genderWiseChart['values']"
                    :colors="$genderWiseChart['colors'] ?? null" height="300" width="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('employees', $modules) && in_array('role_wise_employee', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.roleWiseEmployee')">
                <x-pie-chart id="task-chart4" :labels="$roleWiseChart['labels']" :values="$roleWiseChart['values']"
                    :colors="$roleWiseChart['colors'] ?? null" height="300" width="300" />
            </x-cards.data>
        </div>
    @endif

    @if (in_array('leaves', $modules) && in_array('leaves_taken', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.leavesTaken')" padding="false" otherClasses="h-200">
                <x-table>
                    @forelse ($leavesTaken as $item)
                        <tr>
                            <td class="pl-20">
                                <x-employee :user="$item" />
                            </td>
                            <td class="pr-20"><span
                                    class="badge badge-light p-2">{{ $item->employeeLeaveCount }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="shadow-none">
                                <x-cards.no-record icon="plane-departure" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </x-cards.data>
        </div>
    @endif

    @if (in_array('attendance', $modules) && in_array('late_attendance_mark', $activeWidgets))
        <div class="col-sm-12 col-lg-6 mt-3">
            <x-cards.data :title="__('modules.dashboard.lateAttendanceMark')" padding="false" otherClasses="h-200">
                <x-table>
                    @forelse ($lateAttendanceMarks as $item)
                        <tr>
                            <td class="pl-20">
                                <x-employee :user="$item" />
                            </td>
                            <td><span
                                    class="badge badge-light p-2">{{ $item->employeeLateCount }}</span></td>
                            <td>
                                <x-forms.button-secondary icon="eye" data-user-id="{{ $item->id }}" class="view-late-attendance">
                                    @lang('app.view')
                                </x-forms.button-secondary>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="shadow-none">
                                <x-cards.no-record icon="user-clock" :message="__('messages.noRecordFound')" />
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </x-cards.data>
        </div>
    @endif

</div>

<script>

    $('#save-dashboard-widget').click(function() {
        $.easyAjax({
            url: "{{ route('dashboard.widget', 'admin-hr-dashboard') }}",
            container: '#dashboardWidgetForm',
            blockUI: true,
            type: "POST",
            redirect: true,
            data: $('#dashboardWidgetForm').serialize(),
            success: function() {
                window.location.reload();
            }
        })
    });

    $('body').on('click', '#total-leaves-approved', function() {
        var dateRange = getDateRange();

        var url = `{{ route('leaves.index') }}`;
        string = `?status=approved&start=${dateRange.startDate}&end=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('body').on('click', '#total-new-employees', function() {
        var dateRange = getDateRange();
        var url = `{{ route('employees.index') }}`;

        string = `?startDate=${dateRange.startDate}&endDate=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('body').on('click', '#total-ex-employees', function() {
        var dateRange = getDateRange();
        var url = `{{ route('employees.index') }}`;

        string = `?status=ex_employee&lastStartDate=${dateRange.startDate}&lastEndDate=${dateRange.endDate}`;
        url += string;

        window.location.href = url;
    });

    $('body').on('click', '.view-late-attendance', function() {
        var empId = $(this).data('user-id');
        var dateRange = getDateRange();
        var url = `{{ route('attendances.index') }}`;

        string = `?employee_id=${empId}&late=yes`;
        url += string;

        window.location.href = url;
    });

    function getDateRange() {
        var dateRange = $('#datatableRange2').data('daterangepicker');
        var startDate = dateRange.startDate.format('{{ $global->moment_date_format }}');
        var endDate = dateRange.endDate.format('{{ $global->moment_date_format }}');

        startDate = encodeURIComponent(startDate);
        endDate = encodeURIComponent(endDate);

        var data = [];
        data['startDate'] = startDate;
        data['endDate'] = endDate;

        return data;
    }

</script>
