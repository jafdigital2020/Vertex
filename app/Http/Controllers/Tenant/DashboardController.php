<?php

namespace App\Http\Controllers\Tenant;

use Carbon\Carbon;
use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\RoleAccessController;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    // Admin Dashboard
    public function adminDashboard()
    {

        $permission = PermissionHelper::get(1);


        return view('tenant.dashboard.admin');
    }

    // Employee Dashboard
    public function employeeDashboard()
    {

        $authUser = Auth::user();
        $authUserTenantId = $authUser->tenant_id;

        // Nearest Upcoming Holiday
        $today = Carbon::today();

        $upcomingHoliday = Holiday::where('tenant_id', $authUserTenantId)
            ->where(function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    // Holidays with a specific date (yearly or one-time)
                    $q->whereNotNull('date')
                        ->whereDate('date', '>=', $today);
                })
                    ->orWhere(function ($q) use ($today) {
                        // Holidays with only month_day (recurring yearly)
                        $q->whereNull('date')
                            ->whereNotNull('month_day')
                            ->whereRaw("STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', month_day), '%Y-%m-%d') >= ?", [$today->toDateString()]);
                    });
            })
            ->orderByRaw("
            CASE
                WHEN date IS NOT NULL THEN date
                ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', month_day), '%Y-%m-%d')
            END ASC
            ")
            ->first();

        // Branch Birthday Employee
        $branchBirthdayEmployees = collect();

        if (
            $authUser->employmentDetail &&
            $authUser->employmentDetail->branch &&
            $authUser->employmentDetail->branch->employmentDetail
        ) {
            foreach ($authUser->employmentDetail->branch->employmentDetail as $employmentDetail) {
                if (
                    $employmentDetail->user &&
                    $employmentDetail->user->personalInformation &&
                    $employmentDetail->user->personalInformation->birth_date &&
                    Carbon::parse($employmentDetail->user->personalInformation->birth_date)->isToday()
                ) {
                    $pi = $employmentDetail->user->personalInformation;
                    $fullName = trim("{$pi->first_name} {$pi->middle_name} {$pi->last_name}");

                    $branchBirthdayEmployees->push([
                        'full_name' => $fullName,
                        'designation' => $employmentDetail->designation->designation_name ?? '—'
                    ]);
                }
            }
        }
        $branchBirthdayEmployees = collect();

        if (
            $authUser->employmentDetail &&
            $authUser->employmentDetail->branch &&
            $authUser->employmentDetail->branch->employmentDetail
        ) {
            foreach ($authUser->employmentDetail->branch->employmentDetail as $employmentDetail) {
                if (
                    $employmentDetail->user &&
                    $employmentDetail->user->personalInformation &&
                    $employmentDetail->user->personalInformation->birth_date &&
                    Carbon::parse($employmentDetail->user->personalInformation->birth_date)->isToday()
                ) {
                    $pi = $employmentDetail->user->personalInformation;
                    $fullName = trim("{$pi->first_name} {$pi->middle_name} {$pi->last_name}");
                    $profilePicture = $pi->profile_picture
                        ? asset('storage/' . $pi->profile_picture)
                        : asset('build/img/users/user-35.jpg'); // fallback image

                    $branchBirthdayEmployees->push([
                        'full_name' => $fullName,
                        'designation' => $employmentDetail->designation->designation_name ?? '—',
                        'profile_picture' => $profilePicture,
                    ]);
                }
            }
        }

        // pasa mo lang yung sub_module id ng page dito sir pat

        $permission = PermissionHelper::get(1);
        if (in_array('Create', $permission)) {
        }
        if (in_array('Read', $permission)) {
        }
        if (in_array('Update', $permission)) {
        }
        if (in_array('Delete', $permission)) {
        }
        if (in_array('Import', $permission)) {
        }
        if (in_array('Export', $permission)) {
        }

        return view('tenant.dashboard.employee', [
            'upcomingHoliday' => $upcomingHoliday,
            'authUser' => $authUser,
            'permission' => $permission,
            'branchBirthdayEmployees' => $branchBirthdayEmployees
        ]);
    }
}
