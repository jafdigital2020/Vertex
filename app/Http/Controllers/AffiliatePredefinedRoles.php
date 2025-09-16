<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliatePredefinedRoles extends Controller
{
    public function store(Request $request, $tenant_id, $branch_id = null) {
        if (!is_numeric($tenant_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid tenant_id. It must be an integer.'
            ], 422);
        }

        if ($branch_id !== null && !is_numeric($branch_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid branch_id. It must be an integer.'
            ], 422);
        }

        $tenant_id = (int) $tenant_id;
        $branch_id = $branch_id !== null ? (int) $branch_id : null;
        $now = Carbon::now();

        DB::table('role')->insert([
            [
                'role_name' => 'Admin', 
                'tenant_id' => $tenant_id,
                'branch_id' => $branch_id,
                'data_access_id' => 2,
                'menu_ids' => '1,2,3,4,5',
                'module_ids'=> '1,3,4,6,7,10,11,13,19',
                'role_permission_ids'=> '1-1,1-2,1-3,1-4,1-5,1-6,2-1,2-2,2-3,2-4,2-5,2-6,8-1,8-2,8-3,8-4,8-5,8-6,9-1,9-2,9-3,9-4,9-5,9-6,10-1,10-2,10-3,10-4,10-5,10-6,11-1,11-2,11-3,11-4,11-5,11-6,53-1,53-2,53-3,53-4,53-5,53-6,57-1,57-2,57-3,57-4,57-5,57-6,14-1,14-2,14-3,14-4,14-5,14-6,15-1,15-2,15-3,15-4,15-5,15-6,17-1,17-2,17-3,17-4,17-5,17-6,45-1,45-2,45-3,45-4,45-5,45-6,19-1,19-2,19-3,19-4,19-5,19-6,20-1,20-2,20-3,20-4,20-5,20-6,24-1,24-2,24-3,24-4,24-5,24-6,25-1,25-2,25-3,25-4,25-5,25-6,26-1,26-2,26-3,26-4,26-5,26-6,27-1,27-2,27-3,27-4,27-5,27-6,30-1,30-2,30-3,30-4,30-5,30-6,54-1,54-2,54-3,54-4,54-5,54-6,55-1,55-2,55-3,55-4,55-5,55-6,56-1,56-2,56-3,56-4,56-5,56-6',
                'status'=> 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],   
            [
                'role_name' => 'Employee', 
                'tenant_id' => $tenant_id,
                'branch_id' => $branch_id,
                'data_access_id' => 4,
                'menu_ids' => '1,2,3',
                'module_ids'=> '1,6,7,11',
                'role_permission_ids'=> '2-1,2-2,2-3,2-4,2-5,2-6,15-1,15-2,45-1,45-2,45-3,20-1,20-2,20-3,27-2,27-6',
                'status'=> 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'role_name' => 'Supervisor', 
                'tenant_id' => $tenant_id,
                'branch_id' => $branch_id,
                'data_access_id' => 3,
                'menu_ids' => '1,2,3',
                'module_ids'=> '1,6,7,11',
                'role_permission_ids'=> '2-1,2-2,2-3,2-4,2-5,2-6,14-1,14-2,14-3,15-1,15-2,18-1,18-2,18-3,45-1,45-2,19-1,19-2,19-3,20-1,20-2,27-2,27-6',
                'status'=> 1,
                'created_at' => $now,
                'updated_at' => $now,
            ], 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Predefined roles created successfully'
        ], 201);
    }
}
