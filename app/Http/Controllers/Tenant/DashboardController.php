<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    // Admin Dashboard
    public function adminDashboard()
    {   
       
        return view('tenant.dashboard.admin');
    }

    // Employee Dashboard
    public function employeeDashboard()
    {  
        return view('tenant.dashboard.employee');
    }
}
