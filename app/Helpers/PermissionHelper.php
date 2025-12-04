<?php

namespace App\Helpers;

use App\Models\CRUD;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PermissionHelper
{

    public static function authUser()
    {
        // Check Sanctum first (for API/mobile)
        if (Auth::guard('sanctum')->check()) {
            return Auth::guard('sanctum')->user();
        }
        
        // Check global guard
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        
        // Check web guard
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        }
        
        // Fallback to default guard
        return Auth::user();
    }

    public static function get(int $subModuleId): array
    {
        $user = self::authUser();
        
        if (!$user) {
            \Log::warning('PermissionHelper: No authenticated user found');
            return [];
        }
        
        // Debug logging
        \Log::info('PermissionHelper Debug', [
            'user_id' => $user->id,
            'user_class' => get_class($user),
            'guard_used' => Auth::getDefaultDriver(),
            'has_role_data' => isset($user->role_data),
            'role_data_keys' => $user->role_data ? array_keys($user->role_data) : 'none'
        ]);
        
        $roleData = $user->role_data ?? null;
        
        if (!$roleData) {
            \Log::warning('PermissionHelper: No role_data found for user', ['user_id' => $user->id]);
            return [];
        }

        if (isset($roleData['role_id']) && $roleData['role_id'] === 'global_user') {
            return CRUD::pluck('control_name')->toArray();
        }

        return $roleData['user_permission_ids'][$subModuleId] ?? [];
    }
    

    // public static function get(int $subModuleId): array
    // {
    //     $roleData = Session::get('role_data');

    //     if (isset($roleData['role_id']) && $roleData['role_id'] === 'global_user') {
    //         return CRUD::all()->pluck('control_name')->toArray();
    //     }
    //     return $roleData['user_permission_ids'][$subModuleId] ?? [];
    // }

 
    // public static function authUser()
    // {
    //     if (Auth::guard('global')->check()) {
    //         return Auth::guard('global')->user();
    //     }
    //     return Auth::guard('web')->user();
    // }

    // public static function get(int $subModuleId): array
    // {
    //     $user = self::authUser();    
    //     if (!$user) {
    //         return [];
    //     }   
       
    //     $roleData = $user->role_data;
 
 
    //     if (isset($roleData['role_id']) && $roleData['role_id'] === 'global_user') {
    //         return CRUD::pluck('control_name')->toArray();
    //     }

    //     return $roleData['user_permission_ids'][$subModuleId] ?? [];
    // } 
}
