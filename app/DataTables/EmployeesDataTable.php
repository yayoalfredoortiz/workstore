<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class EmployeesDataTable extends BaseDataTable
{

    private $editEmployeePermission;
    private $deleteEmployeePermission;
    private $viewEmployeePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editEmployeePermission = user()->permission('edit_employees');
        $this->deleteEmployeePermission = user()->permission('delete_employees');
        $this->viewEmployeePermission = user()->permission('view_employees');
        $this->changeEmployeeRolePermission = user()->permission('change_employee_role');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {

        $roles = Role::where('name', '<>', 'client')->get();
        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                if ($row->id != 1 && $row->id != user()->id) {
                    return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
                }

                return '--';
            })
            ->editColumn('current_role_name', function ($row) {
                $userRole = $row->roles->pluck('name')->toArray();

                if (in_array('admin', $userRole)) {
                    return __('app.admin');

                } else {
                    return $row->current_role_name;
                }
            })
            ->addColumn('role', function ($row) use ($roles) {
                $userRole = $row->roles->pluck('name')->toArray();

                if (in_array('admin', $userRole) && !in_array('admin', user_roles())) {
                    return __('messages.roleCannotChange');
                }

                if ($row->id == user()->id || $row->id == 1) {
                    return __('messages.roleCannotChange');
                }

                $role = '<select class="form-control select-picker assign_role" data-user-id="' . $row->id . '">';

                foreach ($roles as $item) {
                    if (
                        $item->name != 'admin'
                    || ($item->name == 'admin' && in_array('admin', user_roles()))
                    ) {

                        $role .= '<option ';

                        if (
                            (in_array($item->name, $userRole) && $item->name == 'admin')
                            || (in_array($item->name, $userRole) && !in_array('admin', $userRole))
                            ) {
                            $role .= 'selected';
                        }

                        $role .= ' value="' . $item->id . '">' . (($item->id <= 3) ? __('app.' . $item->name) : $item->name) . '</option>';
                        
                    }
                }

                $role .= '</select>';
                return $role;
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('employees.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editEmployeePermission == 'all' || ($this->editEmployeePermission == 'added' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('employees.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteEmployeePermission == 'all' || ($this->deleteEmployeePermission == 'added' && user()->id == $row->added_by)) {
                    if (user()->id !== $row->id) {
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                    }
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addColumn('employee_name', function ($row) {
                return $row->name;
            })
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->format($this->global->date_format);
                }
            )
            ->editColumn(
                'status',
                function ($row) {
                    if ($row->status == 'active') {
                        return ' <i class="fa fa-circle mr-1 text-light-green f-10"></i>' . __('app.active');
                    }
                    else {
                        return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.inactive');
                    }
                }
            )
            ->editColumn('name', function ($row) {
                return view('components.employee', [
                    'user' => $row
                ]);
            })
            ->addIndexColumn()
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['name', 'action', 'role', 'status', 'check'])
            ->removeColumn('roleId')
            ->removeColumn('roleName')
            ->removeColumn('current_role');
    }

    /**
     * @param User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();

        $userRoles = '';

        if ($request->role != 'all' && $request->role != '') {
            $userRoles = Role::findOrFail($request->role);
        }

        $users = $model->with('role', 'roles', 'employeeDetail', 'session')
            ->withoutGlobalScope('active')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'employee_details.added_by', 'users.name', 'users.email', 'users.created_at', 'roles.name as roleName', 'roles.id as roleId', 'users.image', 'users.status', DB::raw('(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1) as `current_role`'), DB::raw('(select roles.name from roles as roles where roles.id = current_role limit 1) as `current_role_name`'), 'designations.name as designation_name')
            ->where('roles.name', '<>', 'client');

        if ($request->status != 'all' && $request->status != '') {

            if($request->status === 'ex_employee') {
                $users = $users->whereNotNull('employee_details.last_date');
                $users->whereRaw('Date(employee_details.last_date) <= ?', [Carbon::now()]);
            }
            else {
                $users = $users->where('users.status', $request->status);
            }
        }

        if ($request->employee != 'all' && $request->employee != '') {
            $users = $users->where('users.id', $request->employee);
        }

        if ($request->designation != 'all' && $request->designation != '') {
            $users = $users->where('employee_details.designation_id', $request->designation);
        }

        if ($request->department != 'all' && $request->department != '') {
            $users = $users->where('employee_details.department_id', $request->department);
        }

        if ($request->role != 'all' && $request->role != '' && $userRoles) {
            if ($userRoles->name == 'admin') {
                $users = $users->where('roles.id', $request->role);
            }
            elseif ($userRoles->name == 'employee') {
                $users = $users->where(DB::raw('(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1)'), $request->role)
                    ->having('roleName', '<>', 'admin');
            }
            else {
                $users = $users->where(DB::raw('(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1)'), $request->role);
            }
        }

        if ((is_array($request->skill) && $request->skill[0] != 'all') && $request->skill != '' && $request->skill != null && $request->skill != 'null') {
            $users = $users->join('employee_skills', 'employee_skills.user_id', '=', 'users.id')
                ->whereIn('employee_skills.skill_id', $request->skill);
        }

        if ($this->viewEmployeePermission == 'added') {
            $users = $users->where('employee_details.added_by', user()->id);
        }

        if ($this->viewEmployeePermission == 'owned') {
            $users = $users->where('employee_details.user_id', user()->id);
        }

        if ($this->viewEmployeePermission == 'both') {
            $users = $users->where(function ($q) {
                $q->where('employee_details.user_id', user()->id);
                $q->orWhere('employee_details.added_by', user()->id);
            });
        }

        if ($request->startDate != '' && $request->endDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->startDate)->toDateString();
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->endDate)->toDateString();

            $users = $users->whereRaw('Date(employee_details.joining_date) >= ?', [$startDate])->whereRaw('Date(employee_details.joining_date) <= ?', [$endDate]);
        }

        if ($request->status == 'ex_employee' && isset($request->lastStartDate) && isset($request->lastEndDate) && $request->lastStartDate != '' && $request->lastEndDate != '') {
            $startDate = Carbon::createFromFormat($this->global->date_format, $request->lastStartDate)->toDateString();
            $endDate = Carbon::createFromFormat($this->global->date_format, $request->lastEndDate)->toDateString();
            $users = $users->whereNotNull('last_date')->whereRaw('Date(employee_details.last_date) >= ?', [$startDate])->whereRaw('Date(employee_details.last_date) <= ?', [$endDate]);
        }

        if ($request->searchText != '') {
            $users = $users->where(function ($query) {
                $query->where('users.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('users.email', 'like', '%' . request('searchText') . '%');
            });
        }

        return $users->groupBy('users.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('employees-table')
            ->columns($this->getColumns())
            ->minifiedAjax()

            ->destroy(true)
            ->orderBy(2)
            ->responsive(true)
            ->serverSide(true)
            ->stateSave(false)
            ->processing(true)
            ->language(__('app.datatable'))
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["employees-table"].buttons().container()
                     .appendTo( "#table-actions")
                 }',
                'fnDrawCallback' => 'function( oSettings ) {
                   //
                   $(".select-picker").selectpicker();
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
            __('app.name') => ['data' => 'name', 'name' => 'name', 'exportable' => false, 'title' => __('app.name')],
            __('app.employee') => ['data' => 'employee_name', 'name' => 'name', 'visible' => false, 'title' => __('app.employee')],
            __('app.email') => ['data' => 'email', 'name' => 'email', 'title' => __('app.email')],
            __('app.role') => ['data' => 'role', 'name' => 'role', 'width' => '20%', 'orderable' => false, 'exportable' => false, 'title' => __('app.role'), 'visible' => ($this->changeEmployeeRolePermission == 'all')],
            __('modules.employees.role') => ['data' => 'current_role_name', 'name' => 'current_role_name', 'visible' => false, 'title' => __('modules.employees.role')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
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
        return 'employees_' . date('YmdHis');
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
