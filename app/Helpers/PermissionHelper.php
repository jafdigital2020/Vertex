<?php

namespace App\Helpers;

use App\Models\CRUD;
use Illuminate\Support\Facades\Session;

class PermissionHelper
{
    public static function get(int $subModuleId): array
    {
        $roleData = Session::get('role_data');

        if (isset($roleData['role_id']) && $roleData['role_id'] === 'global_user') {
            return CRUD::all()->pluck('control_name')->toArray();
        }
        return $roleData['user_permission_ids'][$subModuleId] ?? [];
    }
}
