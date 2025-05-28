<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
