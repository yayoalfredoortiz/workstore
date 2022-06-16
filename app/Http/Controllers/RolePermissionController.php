<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Role\StoreRole;
use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\Request;

class RolePermissionController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.rolesPermission';
        $this->activeSettingMenu = 'role_permissions';
    }

    public function index()
    {
        $this->roles = Role::withCount('roleuser')
            ->where('name', '<>', 'admin')
            ->orderBy('id', 'asc')
            ->get();

        $this->totalPermissions = Permission::count();
        return view('role-permissions.index', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        $this->roles = Role::withCount('unsyncedUsers')->get();
        
        return view('role-permissions.ajax.create', $this->data);
    }

    public function store(Request $request)
    {
        $roleId = $request->roleId;
        $permissionId = $request->permissionId;
        
        $permissionType = $request->permissionType;

        if ($permissionType == '') {
            abort(404);
        }

        $role = Role::with('roleuser', 'roleuser.user.roles')->find($roleId);

        // Update role's permission
        $permissionRole = PermissionRole::where('permission_id', $permissionId)
            ->where('role_id', $roleId)
            ->first();

        if ($permissionRole) {
            $permissionRole = PermissionRole::where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->update(['permission_type_id' => $permissionType]);
    
        } else {
            $permissionRole = new PermissionRole();
            $permissionRole->permission_id = $permissionId;
            $permissionRole->role_id = $roleId;
            $permissionRole->permission_type_id = $permissionType;
            $permissionRole->save();
    
        }

        // Update user permission with the role
        foreach ($role->roleuser as $roleuser) {
            if (($role->name == 'employee' && count($roleuser->user->roles) == 1) || $role->name != 'employee') {
                $userPermission = UserPermission::where('permission_id', $permissionId)
                    ->where('user_id', $roleuser->user_id)
                    ->firstOrNew();

                $userPermission->permission_id = $permissionId;
                $userPermission->user_id = $roleuser->user_id;
                $userPermission->permission_type_id = $permissionType;
                $userPermission->save();
            }
        }

        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function permissions()
    {
        $roleId = request('roleId');
        $this->role = Role::with('permissions')->find($roleId);

        if ($this->role->name == 'client') {
            $clientModules = ModuleSetting::where('type', 'client')->get()->pluck('module_name');
            $this->modulesData = Module::with('permissions')->withCount('customPermissions')
                ->whereIn('module_name', $clientModules)->get();

        } else {
            $this->modulesData = Module::with('permissions')->withCount('customPermissions')->get();
        }

        $html = view('role-permissions.ajax.permissions', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function updateUserPermissions($roleId, $userId)
    {
        $rolePermissions = PermissionRole::where('role_id', $roleId)->get();

        foreach ($rolePermissions as $key => $value) {
            UserPermission::where('permission_id', $value->permission_id)
                ->where('user_id', $userId)
                ->update(['permission_type_id' => $value->permission_type_id]);
        }

        return Reply::dataOnly(['status' => 'success']);
    }

    public function storeRole(StoreRole $request)
    {
        $role = new Role();
        $role->name = $request->name;
        $role->display_name = ucwords($request->name);
        $role->save();

        if ($request->import_from_role != '') {
            $importRolePermissions = PermissionRole::where('role_id', $request->import_from_role)->get();
            
            if (count($importRolePermissions) == 0) {
                return Reply::error(__('messages.noRoleFound'));
            }
            
            foreach($importRolePermissions as $perm)
            {
                $perm->replicate()->fill([
                    'role_id' => $role->id
                ])->save();
            }

        } else {
            $allPermissions = Permission::all();
            $role->perms()->sync([]);
            $role->attachPermissions($allPermissions);
        }

        return Reply::success(__('messages.roleCreated'));
    }

    public function deleteRole(Request $request)
    {
        Role::whereId($request->roleId)->delete();
        return Reply::dataOnly(['status' => 'success']);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function customPermissions(Request $request)
    {
        $moduleId = $request->moduleId;
        $roleId = request('roleId');
        $this->role = Role::with('permissions')->find($roleId);
        $this->modulesData = Module::with('customPermissions')->findOrFail($moduleId);;
        $html = view('role-permissions.ajax.custom_permissions', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'html' => $html]);
    }

    public function resetPermissions()
    {
        $permissionTypes = [
            'added' => 1,
            'owned' => 2,
            'both' => 3,
            'all' => 4,
            'none' => 5
        ];
        
        $role = Role::with('roleuser', 'roleuser.user.roles')->findOrFail(request('roleId'));
        $allPermissions = Permission::all();
        
        PermissionRole::where('role_id', $role->id)->delete();

        switch ($role->name) {
        case 'employee':
            $rolePermissionsArray = PermissionRole::employeeRolePermissions();
                break;

        case 'client':
            $rolePermissionsArray = PermissionRole::clientRolePermissions();
                break;
            
        default:
                return Reply::error(__('messages.permissionDenied'));
        }

        foreach ($allPermissions as $key => $value) {
            $permissionRole = new PermissionRole();
            $permissionRole->permission_id = $value->id;
            $permissionRole->role_id = $role->id;
            $permissionRole->permission_type_id = $permissionTypes['none'];
            $permissionRole->save();
        }

        $rolePermissionsArrayKeys = array_keys($rolePermissionsArray);

        $rolePermissions = Permission::whereIn('name', $rolePermissionsArrayKeys)->get();

           
        foreach ($rolePermissions as $key => $ep) {

            $permissionRole = PermissionRole::with('permission', 'permission')
                ->where('permission_id', $ep->id)
                ->where('role_id', $role->id)
                ->first();
            PermissionRole::where('permission_id', $ep->id)
                ->where('role_id', $role->id)
                ->update(['permission_type_id' => $permissionTypes[$rolePermissionsArray[$permissionRole->permission->name]]]);
        }

        $userIds = $role->roleuser->pluck('user_id');

        User::whereIn('id', $userIds)->update(['permission_sync' => 0]);

        return Reply::success(__('messages.recordSaved'));

    }

}
