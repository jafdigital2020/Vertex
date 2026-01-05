<?php

namespace App\Http\Controllers\Tenant;

use App\Models\CRUD;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\Module;
use App\Models\SubModule;
use App\Models\RoleAccess;
use Illuminate\Http\Request;
use App\Models\UserPermission;
use App\Models\DataAccessLevel;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\UserPermissionAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;
use App\Helpers\ErrorLogger;
use App\Traits\ResponseTimingTrait;

class UserManagementController extends Controller
{
   use ResponseTimingTrait;
    
   private function logUserManagementError(
        string $errorType,
        string $message,
        Request $request,
        ?float $startTime = null,
        ?array $responseData = null
    ): void {
        try {
            $processingTime = null;
            $timingData = null;

            if ($responseData && isset($responseData['timing'])) {
                $timingData = $responseData['timing'];
                $processingTime = $timingData['server_processing_time_ms'] ?? null;
            } elseif ($startTime) {
                $timingData = $this->getTimingData($startTime);
                $processingTime = $timingData ? $timingData['server_processing_time_ms'] : null;
            }

            $errorMessage = sprintf("[%s] %s", $errorType, $message);

            // Get authenticated user
            $authUser = $this->authUser();

            // ===== DEBUG LOG START =====
            Log::debug('logPayrollError - Auth User & Tenant Info', [
                'auth_user_id' => $authUser?->id,
                'auth_user_tenant_id' => $authUser?->tenant_id,
                'tenant_loaded' => isset($authUser->tenant),
                'tenant_name_from_relation' => $authUser->tenant?->tenant_name ?? null,
            ]);

            $clientName = $authUser->tenant?->tenant_name ?? 'Unknown Tenant';
            $clientId   = $authUser->tenant?->id ?? null;

            Log::debug('logPayrollError - Sending to ErrorLogger', [
                'client_name' => $clientName,
                'client_id' => $clientId,
                'error_message' => $errorMessage,
            ]);
            // ===== DEBUG LOG END =====

            // Log to remote system
            ErrorLogger::logToRemoteSystem(
                $errorMessage,
                $clientName,
                $clientId,
                $timingData
            );

            // Local Laravel log
            Log::error($errorType, [
                'clean_message' => $message,
                'full_error' => $responseData['full_error'] ?? null,
                'user_id' => $authUser->id ?? null,
                'client_name' => $clientName,
                'client_id' => $clientId,
                'processing_time_ms' => $processingTime,
                'url' => $request->fullUrl(),
                'request_data' => $request->except(['password', 'token', 'api_key'])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log error', [
                'original_error' => $message,
                'logging_error' => $e->getMessage()
            ]);
        }
    }


    public function authUser()
    {
        $user = null;
        
        if (Auth::guard('global')->check()) {
            $user = Auth::guard('global')->user();
        } else {
            $user = Auth::guard('web')->user();
        }
        
        // Load tenant relationship if user exists
        if ($user) {
            $user->load('tenant');
        }
        
        return $user;
    }


     public function userIndex()
    {

        $authUser = $this->authUser();   
        $sub_modules = SubModule::where('module_id', '<>', 2)->where('module_id','<>',14)->orderBy('module_id', 'asc')->orderBy('order_no', 'asc')->get(); 
        $crud  = CRUD::all();
        $permission = PermissionHelper::get(30);
        $data_access = DataAccessLevel::all(); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
        $users = $accessData['employees']->with( 'personalInformation','userPermission','employmentDetail')->get(); 
        $roles = $accessData['roles']->get();
        $branches = Branch::where('tenant_id', $authUser->tenant_id)->get(); 
        return view('tenant.usermanagement.user', ['users' => $users,'roles' => $roles,'sub_modules'=> $sub_modules, 'CRUD' => $crud, 'permission' => $permission,'data_access'=> $data_access,'branches'=>$branches]);

    }  

    public function userFilter(Request $request)
   {
      $authUser = $this->authUser();  
      $permission = PermissionHelper::get(30);
      $role = $request->input('role');
      $status = $request->input('status');
      $sortBy = $request->input('sort_by');

      $dataAccessController = new DataAccessController();
      $accessData = $dataAccessController->getAccessData($authUser);  

      $query = $accessData['employees']->with([
         'personalInformation', 
         'userPermission.role',
         'employmentDetail',
         'userPermission.data_access_level'
      ]);

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

      $html = view('tenant.usermanagement.user_filter', compact('users', 'permission'))->render();

      return response()->json([
         'status' => 'success',
         'html' => $html
      ]);
   }


     public function getUserPermissionDetails(Request $request)
   {
      $validator = Validator::make($request->all(), [
         'user_permission_id' => 'required|exists:user_permission,id',
      ]);

      if ($validator->fails()) {
         return response()->json([
               'status' => 'error',
               'message' => 'User Permission ID is required or does not exist.'
         ], 422);
      }

      $id = $request->user_permission_id;

      $user_permission = UserPermission::with(['data_access_level', 'user_permission_access'])
         ->find($id);

      if (!$user_permission) {
         return response()->json([
               'status' => 'error',
               'message' => 'User permission not found.'
         ], 404);
      }

      return response()->json([
         'status' => 'success',
         'message' => 'User permission fetched successfully.',
         'user_permission' => $user_permission
      ]);
   }


   public function editUserDataAccessLevel(Request $request)
      {
         $startTime = microtime(true);
         $data = $request->all();   
         $authUser = $this->authUser();  
         $permission = PermissionHelper::get(30);

         if (!in_array('Update', $permission)) {
            Log::info('User does not have permission to update user data access.'); 
            return response()->json([
                  'status' => 'error',
                  'message' => 'You do not have the permission to update.'
            ], 403);  
         }
         $validator = Validator::make($request->all(), [ 
         'edit_user_data_access' => 'required',
         ]);

         if ($validator->fails()) {
            return response()->json([
               'status' => 'error',
               'message' =>  $validator->errors() 
            ], 422);
         }
         DB::beginTransaction();

         try { 
         $user_access = UserPermission::find($data['edit_user_data_access_id']);
         $user_access->data_access_id = $data['edit_user_data_access'];
         $user_access->save();


         if ($data['edit_user_data_access'] == 1) {

            $selectedBranches = $request->editbranch_id;
            $branchIdsString = $selectedBranches ? implode(',', $selectedBranches) : null; 
            $user_permission_access = UserPermissionAccess::where('user_permission_id', $user_access->id)->first();

            if ($user_permission_access) { 
               $user_permission_access->access_ids = $branchIdsString;
               $user_permission_access->save();
            } else { 
               $user_permission_access = new UserPermissionAccess();
               $user_permission_access->user_permission_id = $user_access->id;
               $user_permission_access->access_ids = $branchIdsString;
               $user_permission_access->save();
            }
         } 
            DB::commit();
            return response()->json([
                  'status' => 'success',
                  'message' => 'User data access updated successfully'
            ]);
         } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role: ' . $e->getMessage());
             $cleanMessage = "An unexpected error occurred while updating user access. Please try again later.";

            $this->logUserManagementError(
                '[ERROR_UPDATING_USER_DATA_ACCESS]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
         }
      }
         

  
   public function editUserPermission(Request $request)
   {
      $startTime = microtime(true);
      $authUser = $this->authUser();
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

           $cleanMessage = "An unexpected error occurred while updating permissions. Please try again later.";

            $this->logUserManagementError(
                '[ERROR_UPDATING_USER_PERMISSION]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
      }
   }



    public function roleIndex()
    {
        $authUser = $this->authUser();   
        $roles = Role::where('tenant_id', $authUser->tenant_id)->get();
        $sub_modules = SubModule::where('module_id', '<>', 2)->where('module_id','<>',14)->orderBy('module_id', 'asc')->orderBy('order_no', 'asc')->get(); 
        $crud  = CRUD::all();
        $permission = PermissionHelper::get(31);
        $data_access = DataAccessLevel::all(); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
        $branches = Branch::where('tenant_id', $authUser->tenant_id)->get(); 
 
        return view('tenant.usermanagement.role', ['roles' => $roles, 'sub_modules'=> $sub_modules, 'CRUD' => $crud,'permission'=> $permission, 'data_access' => $data_access,'branches'=> $branches]);
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
       $role = Role::with('data_access_level','role_access')->where('id',$id)->first();
       
       return response()->json(['status' => 'success', 'message' => 'Role fetch successfully','role' => $role]);
     }

   public function addRole(Request $request)
   {
      $startTime = microtime(true);
      $data = $request->all();   
      $authUser = $this->authUser();  
      $permission = PermissionHelper::get(31);

      if (!in_array('Create', $permission)) {
         Log::info('User does not have permission to create roles.'); 
         return response()->json([
               'status' => 'error',
               'message' => 'You do not have the permission to create.'
         ], 403);  
      }
      $validator = Validator::make($request->all(), [
      'add_role_name' => 'required|string|max:255',
      'add_data_access' => 'required',
      ]);

      if ($validator->fails()) {
         return response()->json([
            'status' => 'error',
            'message' =>  $validator->errors() 
         ], 422);
      }
      DB::beginTransaction();

      try { 
          
         $role = new Role(); 
         $role->tenant_id = $authUser->tenant_id;
         $role->data_access_id = $data['add_data_access'];
         $role->role_name = $data['add_role_name']; 
         $role->status = 1;
         $role->save(); 

         if($data['add_data_access'] == 1 ){

            $selectedBranches = $request->branch_id;  
            $branchIdsString = $selectedBranches ? implode(',', $selectedBranches) : null;
               $role_access = new RoleAccess();
               $role_access->role_id = $role->id;
               $role_access->access_ids = $branchIdsString;
               $role_access->save(); 
         }  

         DB::commit(); 

         return response()->json([
               'status' => 'success',
               'message' => 'Role created successfully'
         ]);
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Failed to create role: ' . $e->getMessage());

         $cleanMessage = "An unexpected error occurred while creating role. Please try again later.";

            $this->logUserManagementError(
                '[ERROR_CREATING_ROLE]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
      }
   }
         
    public function editRole(Request $request)
   {
      $startTime = microtime(true);
      $authUser = $this->authUser();
      $data = $request->all();   
      $permission = PermissionHelper::get(31);

      if (!in_array('Update', $permission)) {
         Log::info('User does not have permission to update roles.'); 
         return response()->json([
               'status' => 'error',
               'message' => 'You do not have the permission to update.'
         ], 403);  
      }    

      $validator = Validator::make($request->all(), [
      'edit_role_name' => 'required|string|max:255',
      'edit_role_status' => 'required',
      'edit_data_access' => 'required',
      ]);

      if ($validator->fails()) {
         return response()->json([
            'status' => 'error',
            'message' =>  $validator->errors() 
         ], 422);
      }

      DB::beginTransaction();

      try {
         $role = Role::find($data['edit_role_id']);

         if (!$role) {
                return response()->json([
               'status' => 'success',
               'message' => 'User permission updated successfully'
         ]);
         }

         $role->role_name = $data['edit_role_name'];
         $role->data_access_id = $data['edit_data_access'];
         $role->status = $data['edit_role_status'];
         $role->save();

         if($data['edit_data_access'] == 1 ){ 
            $selectedBranches = $request->editbranch_id;  
            $branchIdsString = $selectedBranches ? implode(',', $selectedBranches) : null; 
            $role_access = RoleAccess::where('role_id', $role->id)->first(); 
            if (!$role_access) { 
               $role_access = new RoleAccess();
               $role_access->role_id = $role->id;
            }
            $role_access->access_ids = $branchIdsString;
            $role_access->save();
         }   
         DB::commit();
         return response()->json([
               'status' => 'success',
               'message' => 'Role updated successfully'
         ]);
      } catch (\Exception $e) {
         DB::rollBack();
         Log::error('Failed to update role: ' . $e->getMessage());

         $cleanMessage = "An unexpected error occurred while updating role. Please try again later.";

            $this->logUserManagementError(
                '[ERROR_UPDATING_ROLE]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
      }
   }
         
    public function getRolePermissionDetails(Request $request) {

       $data = $request->all(); 
       
       $validator = Validator::make($data,[
          'role_permission_id' => 'required'
       ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Role Permission ID is required']);
        }
        $id = $data['role_permission_id'];
        $role_permission = Role::find($id);

        return response()->json(['status' => 'success', 'message' => 'Role permission fetch successfully', 'role_permission' => $role_permission]);
    }
  
    public function editRolePermission(Request $request)
   {
      $startTime = microtime(true);
      $authUser = $this->authUser();
    $data = $request->all();
    $permission = PermissionHelper::get(31);

    if (!in_array('Update', $permission)) { 
         return response()->json([
               'status' => 'error',
               'message' => 'You do not have the permission to update.'
         ], 403);  
    }

    DB::beginTransaction();

    try {
        $role = Role::find($data['edit_role_permission_id']);

        if (!$role) {
             return response()->json([
               'status' => 'error',
               'message' => 'Role not found.'
         ], 403);  
        }

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

            $menuIds = Module::whereIn('id', $moduleIds)
                ->pluck('menu_id')
                ->unique()
                ->values()
                ->toArray();

            $role->menu_ids = implode(',', $menuIds);
            $role->module_ids = implode(',', $moduleIds);
            $role->role_permission_ids = $permissionIdsString;
        } else { 
            $role->menu_ids = null;
            $role->module_ids = null;
            $role->role_permission_ids = null;
        }

        $role->save();
        DB::commit();

       return response()->json([
               'status' => 'success',
               'message' => 'Role permission updated successfully'
         ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
       Log::error('Failed to update role permission.', [
               'error' => $e->getMessage(), 
               'data' => $data
         ]); 

         $cleanMessage = "An unexpected error occurred while updating role permissions. Please try again later.";

            $this->logUserManagementError(
                '[ERROR_UPDATING_ROLE_PERMISSION]',
                $cleanMessage,
                $request,
                $startTime
            );
            return response()->json([
                'status' => 'error',
                'message' => $cleanMessage,
                'tenant' => $authUser->tenant?->tenant_name ?? null,
            ], 500);
    }
}

  public function roleFilter(Request $request)
{
    $authUser = $this->authUser();  
    $permission = PermissionHelper::get(31); 
    $status = $request->input('status');
    $sortBy = $request->input('sort_by');

    $query = Role::with('data_access_level')->where('tenant_id', $authUser->tenant_id);

    if (!is_null($status)) { 
        $query->where('status', $status); 
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

    $roles = $query->get();
 
    $html = view('tenant.usermanagement.role_filter', compact('roles', 'permission'))->render();

    return response()->json([
        'status' => 'success',
        'html' => $html
    ]);
}

 

} 