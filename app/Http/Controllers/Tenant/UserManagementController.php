<?php

namespace App\Http\Controllers\Tenant;

use App\Models\CRUD;
use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\SubModule;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\UserPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    
     public function userIndex()
    {
        $users = User::with('personalInformation','userPermission')->get(); 
      
        $sub_modules = SubModule::all();
        $crud  = CRUD::all();
          
        return view('tenant.usermanagement.user', ['users' => $users, 'sub_modules'=> $sub_modules, 'CRUD' => $crud]);
    }  

      public function getUserPermissionDetails(Request $request) {
       $data = $request->all(); 
       
       $validator = Validator::make($data,[
          'user_permission_id' => 'required'
       ]);

       if($validator->fails()){
          return response()->json(['status' => 'error', 'message' => 'User Permission ID is required']);
       } 
       $id = $data['user_permission_id'];
       $user_permission = UserPermission::find($id);  
       
       
       
       return response()->json(['status' => 'success', 'message' => 'User permission fetch successfully','user_permission' => $user_permission]);
    
      } 

      public function editUserPermission(Request $request)
      {
      $data = $request->all();

      $user_permission = UserPermission::find($data['edit_user_permission_id']);
      $permissionIdsArray = $data['edit_user_permission_ids'] ?? [];

      if (count($permissionIdsArray) > 0) {

      $permissionIdsString = implode(',', $permissionIdsArray);

      $subModuleIds = array_map(function ($item) {
         return explode('-', $item)[0];
      }, $permissionIdsArray);

      $moduleIds = SubModule::whereIn('id', $subModuleIds)
         ->pluck('module_id')
         ->unique()
         ->values()
         ->toArray();

      $modules = Module::whereIn('id', $moduleIds)->get();
      $menuIds = $modules->pluck('menu_id')->unique()->values()->toArray();

      $menuIdsString = implode(',', $menuIds);
      $moduleIdsString = implode(',', $moduleIds);

      if ($user_permission) {
         $user_permission->menu_ids = $menuIdsString;
         $user_permission->module_ids = $moduleIdsString;
         $user_permission->user_permission_ids = $permissionIdsString;
         $user_permission->save();
      }

      } else {
         if ($user_permission) {
            $user_permission->menu_ids = null;
            $user_permission->module_ids = null;
            $user_permission->user_permission_ids = null;
            $user_permission->save();
         }
      }

      return redirect()->back()->with('success', 'User permission updated successfully');

   }


    public function roleIndex()
    {
        $roles = Role::all();  
        $sub_modules = SubModule::all();
        $crud  = CRUD::all();
        
        return view('tenant.usermanagement.role', ['roles' => $roles, 'sub_modules'=> $sub_modules, 'CRUD' => $crud]);
    } 

    public function getRoleDetails(Request $request) {
       $data = $request->all(); 
       
       $validator = Validator::make($data,[
          'role_id' => 'required'
       ]);

       if($validator->fails()){
          return response()->json(['status' => 'error', 'message' => 'Role ID is required']);
       } 
       $id = $data['role_id'];
       $role = Role::find($id);  
       
       return response()->json(['status' => 'success', 'message' => 'Role fetch successfully','role' => $role]);
     }

     public function editRole(Request $request){

         $data = $request->all();   
         $role = Role::find($data['edit_role_id']); 
         $role->role_name = $data['edit_role_name']; 
         $role->status = $data['edit_role_status'];  
         $role->save();

         return redirect()->back()->with('success','Role updated successfully');
    }
      
    public function getRolePermissionDetails(Request $request) {
       $data = $request->all(); 
       
       $validator = Validator::make($data,[
          'role_permission_id' => 'required'
       ]);

       if($validator->fails()){
          return response()->json(['status' => 'error', 'message' => 'Role Permission ID is required']);
       } 
       $id = $data['role_permission_id'];
       $role_permission = Role::find($id);  
       
       
       return response()->json(['status' => 'success', 'message' => 'Role permission fetch successfully','role_permission' => $role_permission]);
    
      } 

      public function editRolePermission(Request $request)
      {
      $data = $request->all();

      $role_permission = Role::find($data['edit_role_permission_id']);
      $permissionIdsArray = $data['edit_permission_ids'] ?? [];

      if (count($permissionIdsArray) > 0) {

      $permissionIdsString = implode(',', $permissionIdsArray);

      $subModuleIds = array_map(function ($item) {
         return explode('-', $item)[0];
      }, $permissionIdsArray);

      $moduleIds = SubModule::whereIn('id', $subModuleIds)
         ->pluck('module_id')
         ->unique()
         ->values()
         ->toArray();

      $modules = Module::whereIn('id', $moduleIds)->get();
      $menuIds = $modules->pluck('menu_id')->unique()->values()->toArray();

      $menuIdsString = implode(',', $menuIds);
      $moduleIdsString = implode(',', $moduleIds);

      if ($role_permission) {
         $role_permission->menu_ids = $menuIdsString;
         $role_permission->module_ids = $moduleIdsString;
         $role_permission->role_permission_ids = $permissionIdsString;
         $role_permission->save();
      }

      } else {
         if ($role_permission) {
            $role_permission->menu_ids = null;
            $role_permission->module_ids = null;
            $role_permission->role_permission_ids = null;
            $role_permission->save();
         }
      }

      return redirect()->back()->with('success', 'Role permission updated successfully');

   }

}
