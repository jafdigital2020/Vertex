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
        $authUserTenantId = $authUser && isset($authUser->tenant_id) ? $authUser->tenant_id : null;

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

        $employmentDetail = $authUser->employmentDetail ?? null;
        $branch = $employmentDetail && isset($employmentDetail->branch) ? $employmentDetail->branch : null;
        $branchEmploymentDetails = $branch && isset($branch->employmentDetail) ? $branch->employmentDetail : [];

        if ($branchEmploymentDetails && is_iterable($branchEmploymentDetails)) {
            foreach ($branchEmploymentDetails as $employmentDetail) {
                $user = $employmentDetail->user ?? null;
                $pi = $user && isset($user->personalInformation) ? $user->personalInformation : null;
                $birthDate = $pi && isset($pi->birth_date) ? $pi->birth_date : null;

                if ($birthDate && Carbon::parse($birthDate)->isToday()) {
                    $firstName = $pi->first_name ?? '';
                    $middleName = $pi->middle_name ?? '';
                    $lastName = $pi->last_name ?? '';
                    $fullName = trim("{$firstName} {$middleName} {$lastName}");

                    $profilePicture = isset($pi->profile_picture) && $pi->profile_picture
                        ? asset('storage/' . $pi->profile_picture)
                        : asset('build/img/users/user-35.jpg'); // fallback image

                    $designation = isset($employmentDetail->designation) && isset($employmentDetail->designation->designation_name)
                        ? $employmentDetail->designation->designation_name
                        : '—';

                    $branchBirthdayEmployees->push([
                        'full_name' => $fullName,
                        'designation' => $designation,
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
