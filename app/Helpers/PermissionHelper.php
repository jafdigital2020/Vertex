<?php

namespace App\Helpers;

use App\Models\CRUD;
use Illuminate\Support\Facades\Auth;

class PermissionHelper
{  
    public static function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }

    public static function get(int $subModuleId): array
    {
        $user = self::authUser();    
        if (!$user) {
            return [];
        }   
       
        $roleData = $user->role_data;
 
 
        if (isset($roleData['role_id']) && $roleData['role_id'] === 'global_user') {
            return CRUD::pluck('control_name')->toArray();
        }

        return $roleData['user_permission_ids'][$subModuleId] ?? [];
    }
}
