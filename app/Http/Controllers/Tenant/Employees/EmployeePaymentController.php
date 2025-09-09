<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeePaymentController extends Controller
{
    // Payment Success Index Page
    public function showPaymentStatus()
    {
        $successPage = 'employeeecreditssuccess';
        return view('tenant.employee.paymentstatus', compact('successPage'));
    }
}
