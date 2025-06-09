<?php

namespace App\Http\Controllers\Tenant;

use App\Models\CRUD;
use App\Models\Role;
use App\Models\User;
use App\Models\Module;
use App\Models\SubModule;
use Illuminate\Http\Request; 
use App\Models\UserPermission;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
   

   public function authUser() {
      if (Auth::guard('global')->check()) {
         return Auth::guard('global')->user();
      } 
      return Auth::guard('web')->user();
   }

     public function userIndex()
    {

        $authUser = $this->authUser();  
        $users = User::where('tenant_id', $authUser->tenant_id)->with( 'personalInformation','userPermission','employmentDetail')->get(); 
        $roles = Role::where('tenant_id',$authUser->tenant_id)->get();
        $sub_modules = SubModule::all();
        $crud  = CRUD::all();
        $permission = PermissionHelper::get(30);
        return view('tenant.usermanagement.user', ['users' => $users,'roles' => $roles,'sub_modules'=> $sub_modules, 'CRUD' => $crud, 'permission' => $permission]);

    }  

       public function userFilter(Request $request)
       { 
         $authUser = $this->authUser();  
         $permission = PermissionHelper::get(30);
         $role = $request->input('role');
         $status = $request->input('status');
         $sortBy = $request->input('sort_by');

         $query = User::where('tenant_id', $authUser->tenant_id)->with(['personalInformation', 'userPermission.role','employmentDetail']);
        
         if ($role) {
            $query->whereHas('userPermission', function ($q) use ($role) {
                  $q->where('role_id', $role);
            });
         }

         if (!is_null($status)) {
            $query->whereHas('employmentDetail', function ($q) use ($status) {
                  $q->where('status', $status);
            });
         }
         if ($sortBy === 'ascending') {
            $query->orderBy('created_at', 'asc');
         } elseif ($sortBy === 'descending') {
            $query->orderBy('created_at', 'desc');
         } elseif ($sortBy === 'last_month') {
            $query->where('created_at', '>=', now()->subMonth());
         } elseif ($sortBy === 'last_7_days') {
            $query->where('created_at', '>=', now()->subDays(7));
         }
       
         $users = $query->get();
 

         return response()->json([
            'status' => 'success',
            'data' => $users,
            'permission' => $permission
         ]);
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
      $permission = PermissionHelper::get(30);

      if (!in_array('Update', $permission)) { 
         return response()->json([
               'status' => 'error',
               'message' => 'You do not have the permission to update.'
         ], 403);  
      }

      DB::beginTransaction();

      try {
         $user_permission = UserPermission::find($data['edit_user_permission_id']);
         $permissionIdsArray = $data['edit_user_permission_ids'] ?? [];

         if ($user_permission) {
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

                  $user_permission->menu_ids = $menuIdsString;
                  $user_permission->module_ids = $moduleIdsString;
                  $user_permission->user_permission_ids = $permissionIdsString;
               } else {
                  $user_permission->menu_ids = null;
                  $user_permission->module_ids = null;
                  $user_permission->user_permission_ids = null;
               }

               $user_permission->save();
         }

         DB::commit();
 
         return response()->json([
               'status' => 'success',
               'message' => 'User permission updated successfully'
         ]);

      } catch (\Exception $e) {
         DB::rollBack(); 
         Log::error('Failed to update user permission.', [
               'error' => $e->getMessage(), 
               'data' => $data
         ]); 
         return response()->json([
               'status' => 'error',
               'message' => 'An unexpected error occurred while updating permissions.'
         ], 500);
      }
   }



    public function roleIndex()
    {
        $authUser = $this->authUser();  
        $roles = Role::where('tenant_id', $authUser->tenant_id)->get();
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

         $permission = PermissionHelper::get(31);
 
         if(in_array('Update', $permission)){

         $role = Role::find($data['edit_role_id']); 
         $role->role_name = $data['edit_role_name']; 
         $role->status = $data['edit_role_status'];  
         $role->save();

         return redirect()->back()->with('success','Role updated successfully');

         }else{
 
         Log::info('doesnt have permission');
         return redirect()->back()->with('error', 'You do not have the permission to update.');
         
         }
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
      $permission = PermissionHelper::get(31);
 
      if(in_array('Update', $permission)){
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
    Log::info('has permission');
      return redirect()->back()->with('success', 'Role permission updated successfully');
     }else{
      Log::info('doesnt have permission');
      return redirect()->back()->with('error', 'You do not have the permission to update.');
     } 
   }

}
