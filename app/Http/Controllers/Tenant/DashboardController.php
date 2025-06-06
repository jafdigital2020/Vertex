<?php

namespace App\Http\Controllers\Tenant;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\RoleAccessController;

class DashboardController extends Controller
{
    // Admin Dashboard
    public function adminDashboard()
    {  
         
        $permission = PermissionHelper::get(1);
 
    
        return view('tenant.dashboard.admin');
    }

    // Employee Dashboard
    public function employeeDashboard(){
        
        // pasa mo lang yung sub_module id ng page dito sir pat
        
        $permission = PermissionHelper::get(1);
        if(in_array('Create', $permission)){

        }
        if(in_array('Read', $permission)){

        }
        if(in_array('Update', $permission)){

        }
        if(in_array('Delete', $permission)){

        }
        if(in_array('Import', $permission)){

        }
        if(in_array('Export', $permission)){

        }

        return view('tenant.dashboard.employee');
    }
}
