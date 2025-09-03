<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Role;
use App\Models\User;
use App\Models\Assets;
use App\Models\Branch;
use App\Models\Policy;
use App\Models\Holiday;
use App\Models\OtTable;
use App\Models\Payroll;
use App\Models\Geofence;
use App\Models\Overtime;
use App\Models\LeaveType;
use App\Models\ShiftList;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\CustomField;
use App\Models\Designation;
use App\Models\EarningType;
use App\Models\UserEarning;
use App\Models\GeofenceUser;
use Illuminate\Http\Request;
use App\Models\AssetsHistory;
use App\Models\DeductionType;
use App\Models\UserDeduction;
use App\Models\UserDeminimis;
use App\Models\BulkAttendance;
use App\Models\ShiftAssignment;
use App\Models\HolidayException;
use App\Models\OfficialBusiness;
use App\Models\DeminimisBenefits;
use App\Models\RequestAttendance;
use App\Models\WithholdingTaxTable;
use App\Models\AssetsDetailsHistory;
use App\Models\PayrollBatchSettings;
use App\Models\SssContributionTable;
use Illuminate\Support\Facades\Auth;
use App\Models\PhilhealthContribution;

class DataAccessController extends Controller
{  
     public function authUser() {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }
    public function isGlobalUser() {
        return Auth::guard('global')->check();
    }

     public function getAccessData($authUser)
    {   
        $authUserId = $authUser->id;
        $tenantId = $authUser->tenant_id ?? null;
        $branchId = $authUser->employmentDetail->branch_id ?? null;
        $departmentId = $authUser->employmentDetail->department_id ?? null;
        $designationId = $authUser->employmentDetail->designation_id ?? null;

        $accessName = $authUser->userPermission->data_access_level->access_name ?? null;

        $branches = collect();
        $departments = collect();
        $designations = collect();
        
        $policy = Policy::where('tenant_id', $tenantId)
        ->where(function ($query) use ($branchId, $departmentId, $authUserId) {
            $query->whereHas('targets', function ($q) use ($branchId, $departmentId, $authUserId) {
                $q->where(function ($subQ) {
                    $subQ->where('target_type', 'company-wide');
                })->orWhere(function ($subQ) use ($branchId) {
                    $subQ->where('target_type', 'branch')
                        ->where('target_id', $branchId);
                })->orWhere(function ($subQ) use ($departmentId) {
                    $subQ->where('target_type', 'department')
                        ->where('target_id', $departmentId);
                })->orWhere(function ($subQ) use ($authUserId) {
                    $subQ->where('target_type', 'employee')
                        ->where('target_id', $authUserId);
                });
            })
            ->orWhere('created_by', $authUserId); 
        });
        $banks = Bank::where('tenant_id', $tenantId);
        $leaveTypes = LeaveType::where('tenant_id',$tenantId);
        $roles =  Role::where('tenant_id',$authUser->tenant_id);

        $payslips = Payroll::where('tenant_id', $tenantId)
            ->where('status', 'Paid')
            ->orderBy('payment_date', 'desc')
            ->latest('id');

        $customFields = CustomField::where('tenant_id', $tenantId);
        $sssContributions  =  SssContributionTable::query();
        $philHealthContributions = PhilhealthContribution::query();
        $withHoldingTax =  WithholdingTaxTable::query();
        $ots = OtTable::query();
        $benefits =  DeminimisBenefits::query();
        $earningType = EarningType::where('tenant_id',$tenantId);
        $deductionType = DeductionType::where('tenant_id',$tenantId);
        $payrollBatchSettings = PayrollBatchSettings::where('tenant_id',$tenantId);

        switch ($accessName) {
            case 'Organization-Wide Access':
                // ordwide holidays 
                $holidays = Holiday::where('tenant_id', $tenantId) 
                ->whereDoesntHave('holidayExceptions', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                })
                ->with([
                    'holidayExceptions' => function ($q) {
                        $q->with([
                            'user.personalInformation',
                            'user.employmentDetail.branch',
                            'user.employmentDetail.department',
                        ]);
                    }
                ]);
 
                $branchIds = [];
                if (!empty($authUser->userPermission->user_permission_access->access_ids)) {
                    $branchIds = explode(',', $authUser->userPermission->user_permission_access->access_ids); 
                    $branchIds = array_filter($branchIds, fn($id) => $id !== '');
                }

                $hodBranchIds = Department::where('head_of_department', $authUserId)
                ->pluck('branch_id')
                ->toArray();

                $allBranchIds = array_unique(array_merge($branchIds, $hodBranchIds));
 
                $branchesQuery = Branch::where('tenant_id', $tenantId);

                if (!empty($allBranchIds)) {
                    $branchesQuery->whereIn('id', $allBranchIds);
                } else { 
                    $branchesQuery->whereRaw('0=1');
                } 
                $branches = $branchesQuery;

                // orgwide geofences
                $geofences = Geofence::whereIn('branch_id',$allBranchIds);
                // orgwide holiday exception
                $holidayException = HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId);
                    })
                    ->whereHas('user.employmentDetail', function ($q) use ($allBranchIds) {
                        $q->whereIn('branch_id', $allBranchIds);
                    })
                    ->with([
                        'holiday',
                        'user.personalInformation',
                        'user.employmentDetail.branch',
                        'user.employmentDetail.department'
                    ]); 
                // orgwide overtimes
                $overtimes = Overtime::with('user.employmentDetail')  
                ->whereHas('user', function ($query) use ($tenantId, $allBranchIds) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($q) use ($allBranchIds) {
                            $q->whereIn('branch_id', $allBranchIds);
                        });
                })
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc');

                // orgwide attendance
                $attendances = Attendance::with([
                        'user.personalInformation',
                        'user.employmentDetail.branch',
                        'user.employmentDetail.department',
                        'shift',
                    ])
                    ->whereHas('user', function ($userQ) use ($tenantId,$allBranchIds) {
                        $userQ->where('tenant_id', $tenantId)
                            ->whereHas('employmentDetail', function ($edQ) use ($allBranchIds) {
                                $edQ->where('status', '1'); 
                                if (!empty($allBranchIds)) {
                                    $edQ->whereIn('branch_id', $allBranchIds);
                                }
                            });
                  });
                // orgwide employees 
                $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation', 
                    'payrollBatchUsers'
                ]) ->whereHas('employmentDetail.branch', function ($q) use ($allBranchIds) {
                    $q->whereIn('id', $allBranchIds);
                });
                // orgwide shiftlist
                $shiftList = ShiftList::where(function ($q) use ($authUser) {
                    $q->where('tenant_id', $authUser->tenant_id)
                    ->orWhereNull('tenant_id');  
                })
                ->where(function ($q) use ($authUser, $allBranchIds) {
                    $q->whereHas('branch', function ($query) use ($authUser, $allBranchIds) {
                        $query->where(function ($sub) use ($authUser, $allBranchIds) {
                            $sub->where('tenant_id', $authUser->tenant_id)
                                ->orWhereNull('tenant_id'); 
                        })->whereIn('id', $allBranchIds);
                    })
                    ->orWhereNull('branch_id');  
                });

                //  orgwide departments
                $departments = Department::whereHas('branch', function ($q) use ($tenantId, $allBranchIds) {
                    $q->where('tenant_id', $tenantId)
                    ->whereIn('id', $allBranchIds);
                });
                // orgwide designations
                $designations = Designation::whereHas('department.branch', function ($q) use ($tenantId, $allBranchIds) {
                    $q->where('tenant_id', $tenantId)
                    ->whereIn('id', $allBranchIds);
                })
                ->withCount(['employmentDetail as active_employees_count' => function ($q) {
                    $q->where('status', '1');
                }]);
                // orgwide geonfenceusers
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail.branch', function ($q) use ($allBranchIds) {
                    $q->whereIn('id', $allBranchIds);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                // orgwide userdeminimis
                $userDeminimis = UserDeminimis::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                })->with(['deminimisBenefit', 'user']);
                // orgwide user earnings
                $userEarnings = UserEarning::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                }); 
                // orgwide user deductions
                $userDeductions = UserDeduction::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                });
                // orgwide official business
                $obEntries = OfficialBusiness::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                })->orderBy('ob_date', 'desc');
                // orgwide assets
                $assets = Assets::whereHas('branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                });
                $assetsHistory = AssetsHistory::whereHas('branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                });
                $assetsDetailsHistory = AssetsDetailsHistory::whereHas('assetDetail.assets.branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                });
                // orgwide bulk attendances
                $bulkAttendances = BulkAttendance::whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($edQ) use ($allBranchIds ) {
                            $edQ->where('status', '1')
                                ->whereIn('branch_id', $allBranchIds );
                        });
                });
                // orgwide user attendances
                $userAttendances = RequestAttendance::whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                                    $query->where('tenant_id', $tenantId)
                                        ->whereHas('employmentDetail', function ($edQ) use ($allBranchIds ) {
                                            $edQ->where('status', '1')
                                                ->whereIn('branch_id', $allBranchIds );
                                        });
                                })
                                ->orderByRaw("FIELD(status, 'pending') DESC")
                                ->orderBy('request_date', 'desc');
                  // orgwide payrolls 
                $payrolls = Payroll::where('status', 'Pending')->whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($edQ) use ($allBranchIds ) {
                            $edQ->whereIn('branch_id', $allBranchIds );
                        });
                });
                
                break;

            case 'Branch-Level Access':
                //  branch level departments
                $departments = Department::where(function ($query) use ($departmentId, $branchId, $tenantId,$authUserId) {
                    $query->where(function ($q) use ($departmentId, $branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })->orWhere(function ($q) use ($authUserId) {
                        $q->where('head_of_department', $authUserId);
                    });
                }); 

                $departmentIds = $departments->pluck('id');   

                // branch level branches  
                $hodBranchIds = Department::where('head_of_department', $authUserId)
                    ->pluck('branch_id')
                    ->toArray(); 
                $allBranchIds = array_unique(array_merge($hodBranchIds, [$branchId]));  
                $branches = Branch::where('tenant_id', $tenantId)
                    ->whereIn('id', $allBranchIds); 

                //branch level employees
                $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation', 
                    'payrollBatchUsers'
                ])->whereHas('employmentDetail', fn($q) => $q->whereIn('branch_id',  $allBranchIds ));
                // branch level holidays
                 $holidays = Holiday::where('tenant_id', $tenantId) 
                ->whereDoesntHave('holidayExceptions', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                })
                ->with([
                    'holidayExceptions' => function ($q) {
                        $q->with([
                            'user.personalInformation',
                            'user.employmentDetail.branch',
                            'user.employmentDetail.department',
                        ]);
                    }
                ]);
                // branch level holiday exception
                $holidayException =  HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                  })->with([
                        'holiday',
                        'user.personalInformation',
                        'user.employmentDetail.branch',
                        'user.employmentDetail.department'
                    ])->whereHas('user.employmentDetail.branch', function ($q) use ($allBranchIds ) {
                    $q->whereIn('id', $allBranchIds);
                });
                // branch level attendances
                $attendances = Attendance::with('user.employmentDetail','user.personalInformation', 'user.employmentDetail.department','shift')
                        ->whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                            $query->where('tenant_id', $tenantId)
                                ->whereHas('employmentDetail', function ($subQuery) use ($allBranchIds ) {
                                    $subQuery->where('status', '1')
                                            ->whereIn('branch_id', $allBranchIds );
                                });
                        }); 
                // branch level shiftlist
                 $shiftList = ShiftList::where(function ($q) use ($allBranchIds, $tenantId) {
                    $q->whereIn('branch_id', $allBranchIds)
                    ->whereHas('branch', function ($query) use ($tenantId) {
                        $query->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id');  
                        });
                    })
                    ->orWhere(function ($q2) use ($tenantId) {
                        $q2->whereNull('branch_id')
                        ->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id'); 
                        });
                    });
                });

                // branch level overtimes 
                $overtimes = Overtime::with('user')
                ->whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($q) use ($allBranchIds ) {
                            $q->whereIn('branch_id', $allBranchIds );
                        });
                })
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc'); 
                // branch level geofences
                $geofences = Geofence::whereIn('branch_id',$allBranchIds );
                
                // branch level designations
                $designations = Designation::whereHas('department', function ($q) use ($allBranchIds , $tenantId) {
                        $q->whereIn('branch_id',$allBranchIds )
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')])
                    ; 
                // branch level geofence users
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail.branch', function ($q) use ($allBranchIds ) {
                    $q->whereIn('id', $allBranchIds );
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                // branch level user deminimis
                $userDeminimis = UserDeminimis::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds ) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id',$allBranchIds );
                })->with(['deminimisBenefit', 'user']);
                // branch level user earnings
                 $userEarnings = UserEarning::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds ) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds );
                });
                // branch level user deductions
                $userDeductions = UserDeduction::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds ) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds );
                });
                // branch level official business
                $obEntries = OfficialBusiness::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$allBranchIds ) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds );
                });
                // branch level assets
                $assets = Assets::whereHas('branch', function ($q) use ($tenantId,$allBranchIds ) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds );
                }); 
                $assetsHistory = AssetsHistory::whereHas('branch', function ($q) use ($tenantId,$allBranchIds ) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds );
                }); 
                $assetsDetailsHistory = AssetsDetailsHistory::whereHas('assetDetail.assets.branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                });
                // branch level bulk attendances
                $bulkAttendances = BulkAttendance::whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($edQ) use ($allBranchIds ) {
                            $edQ->where('status', '1')
                                ->whereIn('branch_id', $allBranchIds );
                        });
                });
                // branch level user attendances
                $userAttendances = RequestAttendance::whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                                    $query->where('tenant_id', $tenantId)
                                        ->whereHas('employmentDetail', function ($edQ) use ($allBranchIds ) {
                                            $edQ->where('status', '1')
                                                ->whereIn('branch_id', $allBranchIds );
                                        });
                                })
                                ->orderByRaw("FIELD(status, 'pending') DESC")
                                ->orderBy('request_date', 'desc');
                 // branch level payrolls  
                $payrolls = Payroll::where('status', 'Pending')->whereHas('user', function ($query) use ($tenantId, $allBranchIds ) {
                        $query->where('tenant_id', $tenantId)
                            ->whereHas('employmentDetail', function ($edQ) use ($allBranchIds ) {
                                $edQ->whereIn('branch_id', $allBranchIds );
                            });
                    }); 
                break;

            case 'Department-Level Access':
                // department level departments
                $departments = Department::where(function ($query) use ($departmentId, $branchId, $tenantId,$authUserId) {
                    $query->where(function ($q) use ($departmentId, $branchId, $tenantId) {
                        $q->where('id', $departmentId)
                        ->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })->orWhere(function ($q) use ($authUserId) {
                        $q->where('head_of_department', $authUserId);
                    });
                });
              
                $departmentIds = $departments->pluck('id');  

                // department level branches
                $hodBranchIds = Department::where('head_of_department', $authUserId)
                    ->pluck('branch_id')
                    ->toArray(); 
                $allBranchIds = array_unique(array_merge($hodBranchIds, [$branchId]));  
                $branches = Branch::where('tenant_id', $tenantId)
                    ->whereIn('id', $allBranchIds);

                // department level employees
                 $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation',
                    'payrollBatchUsers'
                ])->whereHas('employmentDetail', fn($q) =>
                     $q->whereIn('department_id', $departmentIds)
                 );
                //  department level holidays
                $holidays = Holiday::where('tenant_id', $tenantId) 
                ->whereDoesntHave('holidayExceptions', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                })
                ->with([
                    'holidayExceptions' => function ($q) {
                        $q->with([
                            'user.personalInformation',
                            'user.employmentDetail.branch',
                            'user.employmentDetail.department',
                        ]);
                    }
                ]);
                // department level holiday exception
                $holidayException =  HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                    })->with([
                            'holiday',
                            'user.personalInformation',
                            'user.employmentDetail.branch',
                            'user.employmentDetail.department'
                        ])->whereHas('user.employmentDetail.department', function ($q) use ($departmentIds) {
                        $q->whereIn('id', $departmentIds);
                    });
                // department  level attendances
                $attendances = Attendance::with('user.employmentDetail','user.personalInformation', 'user.employmentDetail.department','shift')
                        ->whereHas('user', function ($query) use ($tenantId,$departmentIds) {
                            $query->where('tenant_id', $tenantId)
                                ->whereHas('employmentDetail', function ($subQuery) use ($departmentIds) {
                                    $subQuery->where('status', '1') 
                                            ->whereIn('department_id',$departmentIds);
                                });
                        }); 
                // department level shiftlist
                $shiftList = ShiftList::where(function ($q) use ($allBranchIds, $tenantId) {
                    $q->whereIn('branch_id', $allBranchIds)
                    ->whereHas('branch', function ($query) use ($tenantId) {
                        $query->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id');  
                        });
                    })
                    ->orWhere(function ($q2) use ($tenantId) {
                        $q2->whereNull('branch_id')
                        ->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id'); 
                        });
                    });
                });

                // department level overtimes
                $overtimes = Overtime::with('user')
                    ->whereHas('user', function ($query) use ($tenantId,$departmentIds) {
                        $query->where('tenant_id', $tenantId)
                            ->whereHas('employmentDetail', function ($q) use ($departmentIds) {
                                $q->whereIn('department_id', $departmentIds);
                            });
                    }) 
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc');
            
                // department level geofences
                $geofences = Geofence::whereIn('branch_id',$allBranchIds );
                
                // department level designations
                $designations = Designation::whereIn('department_id', $departmentIds)
                    ->withCount([
                        'employmentDetail as active_employees_count' => fn($q) =>
                            $q->where('status', '1')
                    ]);
                // department level geofences
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail', function ($q) use ($departmentIds) { 
                    $q->whereIn('department_id',$departmentIds);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                // department level user deminimis 
                $userDeminimis = UserDeminimis::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentIds) {
                        $q->whereIn('department_id', $departmentIds);
                    })->with(['deminimisBenefit', 'user']);
                // department level user earnings
                $userEarnings = UserEarning::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentIds) {
                        $q->whereIn('department_id', $departmentIds);
                    });
                // department level user deductions
                $userDeductions = UserDeduction::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentIds) {
                        $q->whereIn('department_id', $departmentIds);
                    });
                // department level official business
                $obEntries = OfficialBusiness::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentIds) {
                        $q->whereIn('department_id', $departmentIds);
                    });
                // department level assets
                $assets = Assets::whereHas('branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                });
                $assetsHistory = AssetsHistory::whereHas('branch', function ($q) use ($tenantId,$allBranchIds ) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds );
                }); 
                 $assetsDetailsHistory = AssetsDetailsHistory::whereHas('assetDetail.assets.branch', function ($q) use ($tenantId,$allBranchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $allBranchIds);
                });
                
                // department level bulk attendances
                $bulkAttendances = BulkAttendance::whereHas('user', function ($query) use ($tenantId, $departmentIds) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($edQ) use ($departmentIds) {
                            $edQ->where('status', '1') 
                                ->whereIn('department_id', $departmentIds);
                        });
                });
                // department level user attendances
                 $userAttendances = RequestAttendance::whereHas('user', function ($query) use ($tenantId,$departmentIds) {
                                    $query->where('tenant_id', $tenantId)
                                        ->whereHas('employmentDetail', function ($edQ) use ($departmentIds) {
                                            $edQ->where('status', '1') 
                                                ->whereIn('department_id', $departmentIds);
                                        });
                                })
                                ->orderByRaw("FIELD(status, 'pending') DESC")
                                ->orderBy('request_date', 'desc');
               //department level access payrolls
                $payrolls = Payroll::where('status', 'Pending')->whereHas('user', function ($query) use ($tenantId,$departmentIds ) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($edQ) use ($departmentIds ) {
                            $edQ->whereIn('department_id', $departmentIds);
                        });
                }); 
 
                break;

              case 'Personal Access Only': 
                //   personal access employees
                 $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation', 
                    'payrollBatchUsers'
                ])->where('id', $authUser->id);
                // personal access holidays
                 $holidays = Holiday::where('tenant_id', $tenantId) 
                ->whereDoesntHave('holidayExceptions', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                })
                ->with([
                    'holidayExceptions' => function ($q) {
                        $q->with([
                            'user.personalInformation',
                            'user.employmentDetail.branch',
                            'user.employmentDetail.department',
                        ]);
                    }
                ]);
                // personal access holiday exception
                $holidayException =  HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                    })->with([
                            'holiday',
                            'user.personalInformation',      
                            'user.employmentDetail.branch',  
                            'user.employmentDetail.department'  
                        ])->where('user_id',$authUser->id)->whereHas('user.employmentDetail.branch', function ($q) use ($branchId) {
                        $q->where('id', $branchId);
                        })->whereHas('user.employmentDetail.department', function ($q) use ($departmentId) {
                        $q->where('id', $departmentId);  
                    });
                // personal access attemdances
                $attendances = Attendance::with('user.employmentDetail','user.personalInformation', 'user.employmentDetail.department','shift')
                        ->whereHas('user', function ($query) use ($tenantId, $branchId,$departmentId,$authUserId) {
                            $query->where('tenant_id', $tenantId)
                                ->whereHas('employmentDetail', function ($subQuery) use ($branchId,$departmentId,$authUserId) {
                                    $subQuery->where('status', '1')
                                            ->where('branch_id', $branchId)
                                            ->where('department_id',$departmentId)
                                            ->where('user_id',$authUserId);
                                });
                        }); 
                //  personal access shiftlist
                 $shiftList = ShiftList::where(function ($q) use ($branchId, $tenantId) {
                    $q->where('branch_id', $branchId)
                    ->whereHas('branch', function ($query) use ($tenantId) {
                        $query->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id'); 
                        });
                    })
                    ->orWhere(function ($q2) use ($tenantId) {
                        $q2->whereNull('branch_id')
                        ->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id');  
                        });
                    });
                });

                // personal access overtimes 
               $overtimes = Overtime::with('user')
                ->whereHas('user', function ($query) use ($tenantId, $branchId, $departmentId, $authUserId) {
                    $query->where('tenant_id', $tenantId)
                        ->where('id', $authUserId) // Filter by user_id here
                        ->whereHas('employmentDetail', function ($q) use ($branchId, $departmentId) {
                            $q->where('branch_id', $branchId);
                            $q->where('department_id', $departmentId);
                        });
                }) 
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc');
                //  personal access branches
                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
                // personal access geofences
                $geofences = Geofence::where('branch_id',$branchId);
                // personal access departments
                $departments = Department::where(function ($query) use ($departmentId, $branchId, $tenantId, $authUserId) {
                    $query->where(function ($q) use ($departmentId, $branchId, $tenantId) {
                        $q->where('id', $departmentId)
                        ->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })->orWhere(function ($q) use ($departmentId, $authUserId) {
                        $q->where('id', $departmentId)
                        ->where('head_of_department', $authUserId);
                    });
                });
                // personal access designations
                $designations = Designation::where('id', $designationId)
                    ->whereHas('department', function ($q) use ($branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')]);
                // personal access geofence users
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail', function ($q) use ($branchId,$departmentId,$authUserId) {
                    $q->where('branch_id', $branchId);
                    $q->where('department_id',$departmentId);
                    $q->where('user_id',$authUserId);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                // personal access user deminimis
                $userDeminimis = UserDeminimis::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })->with(['deminimisBenefit', 'user']);
                // personal access user earnings
                $userEarnings = UserEarning::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
                // personal access user deductions
                $userDeductions = UserDeduction::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
                // personal access official business
                $obEntries = OfficialBusiness::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
                // personal access assets
                $assets = Assets::whereHas('branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                $assetsHistory = AssetsHistory::whereHas('branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                $assetsDetailsHistory = AssetsDetailsHistory::whereHas('assetDetail.assets.branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                // personal access bulk attendances
                $bulkAttendances = BulkAttendance::where('user_id', $authUserId)
                ->whereHas('user', function ($query) use ($tenantId, $branchId, $departmentId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($edQ) use ($branchId, $departmentId) {
                            $edQ->where('status', '1')
                                ->where('branch_id', $branchId)
                                ->where('department_id', $departmentId);
                        });
                });
                // personal access user attendances
                $userAttendances = RequestAttendance::where('user_id', $authUserId)->whereHas('user', function ($query) use ($tenantId, $branchId,$departmentId) {
                                    $query->where('tenant_id', $tenantId)
                                        ->whereHas('employmentDetail', function ($edQ) use ($branchId,$departmentId) {
                                            $edQ->where('status', '1')
                                                ->where('branch_id', $branchId)
                                                ->where('department_id', $departmentId);
                                        });
                                })
                                ->orderByRaw("FIELD(status, 'pending') DESC")
                                ->orderBy('request_date', 'desc');
                
                 // personal access payrolls  
                $payrolls = Payroll::where('user_id',$authUserId)->where('status', 'Pending');

             break;

            default:
            // defaults   
            // if global user 
            if ($this->isGlobalUser()) {
                $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation', 
                    'payrollBatchUsers'
                ]); 
                $holidays = Holiday::where('tenant_id', $tenantId); 
                $holidayException =  HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                  })->with([
                        'holiday',
                        'user.personalInformation',      
                        'user.employmentDetail.branch',  
                        'user.employmentDetail.department'  
                  ]);
                $attendances = Attendance::with('user.employmentDetail','user.personalInformation', 'user.employmentDetail.department','shift')
                    ->whereHas('user', function ($userQ) use ($tenantId) {
                        $userQ->where('tenant_id', $tenantId)
                            ->whereHas('employmentDetail', function ($edQ) {
                                $edQ->where('status', '1'); 
                            });
                    });
                $shiftList = ShiftList::where(function ($q) use ($authUser) { 
                            $q->whereHas('branch', function ($query) use ($authUser) {
                                $query->where('tenant_id', $authUser->tenant_id);
                            }) 
                            ->orWhere(function ($q2) use ($authUser) {
                                $q2->whereNull('branch_id')
                                ->where('tenant_id', $authUser->tenant_id);
                            });
                        });
                $overtimes = Overtime::with('user')
                    ->whereHas('user', function ($query) use ($tenantId) {
                        $query->where('tenant_id', $tenantId);
                    })
                    ->orderByRaw("FIELD(status, 'pending') DESC")
                    ->orderBy('overtime_date', 'desc');

                $branches = Branch::where('tenant_id', $tenantId);
                $geofences = Geofence::whereHas('branch', function ($query) use ($tenantId) {
                                $query->where('tenant_id', $tenantId);
                            });
                $departments = Department::whereHas('branch', fn($q) => $q->where('tenant_id', $tenantId));
                $designations = Designation::whereHas('department.branch', fn($q) => 
                        $q->where('tenant_id', $tenantId))
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')]);
                  
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                });
                $userDeminimis = UserDeminimis::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId); 
                })->with(['deminimisBenefit', 'user']);
                $userEarnings = UserEarning::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId); 
                });  
                $userDeductions = UserDeduction::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId); 
                });
               $obEntries = OfficialBusiness::whereHas('user', function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                })
                    ->orderBy('ob_date', 'desc');
                $assets = Assets::whereHas('branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId); 
                });     
                $assetsHistory = AssetsHistory::whereHas('branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId); 
                });   
                $assetsDetailsHistory = AssetsDetailsHistory::whereHas('assetDetail.assets.branch', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId); 
                });
                $bulkAttendances = BulkAttendance::whereHas('user', function ($query) use ($tenantId) {
                        $query->where('tenant_id', $tenantId)
                            ->whereHas('employmentDetail', fn($edQ) => $edQ->where('status', '1'));
                    });
                $userAttendances =   RequestAttendance::whereHas('user', function ($query) use ($tenantId) {
                                        $query->where('tenant_id', $tenantId);
                                    })
                                        ->orderByRaw("FIELD(status, 'pending') DESC")
                                        ->orderBy('request_date', 'desc');
            
                $payrolls = Payroll::where('tenant_id',$tenantId)->where('status', 'Pending');
            } else {
               // default access employees
                $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation', 
                    'payrollBatchUsers'
                ])->where('id', $authUser->id);
                // default access holidays
                 $holidays = Holiday::where('tenant_id', $tenantId) 
                ->whereDoesntHave('holidayExceptions', function ($query) use ($authUserId) {
                    $query->where('user_id', $authUserId);
                })
                ->with([
                    'holidayExceptions' => function ($q) {
                        $q->with([
                            'user.personalInformation',
                            'user.employmentDetail.branch',
                            'user.employmentDetail.department',
                        ]);
                    }
                ]);
                // default access holiday exception
                $holidayException =  HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                    })->with([
                            'holiday',
                            'user.personalInformation',
                            'user.employmentDetail.branch',
                            'user.employmentDetail.department'
                        ])->where('user_id',$authUser->id)->whereHas('user.employmentDetail.branch', function ($q) use ($branchId) {
                        $q->where('id', $branchId);
                        })->whereHas('user.employmentDetail.department', function ($q) use ($departmentId) {
                        $q->where('id', $departmentId);
                    });
                // default access attemdances
                $attendances = Attendance::with('user.employmentDetail','user.personalInformation', 'user.employmentDetail.department','shift')
                        ->whereHas('user', function ($query) use ($tenantId, $branchId,$departmentId,$authUserId) {
                            $query->where('tenant_id', $tenantId)
                                ->whereHas('employmentDetail', function ($subQuery) use ($branchId,$departmentId,$authUserId) {
                                    $subQuery->where('status', '1')
                                            ->where('branch_id', $branchId)
                                            ->where('department_id',$departmentId)
                                            ->where('user_id',$authUserId);
                                });
                        }); 
                // default access shiftlist
               $shiftList = ShiftList::where(function ($q) use ($branchId, $tenantId) {
                    $q->where('branch_id', $branchId)
                    ->whereHas('branch', function ($query) use ($tenantId) {
                        $query->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id'); 
                        });
                    })
                    ->orWhere(function ($q2) use ($tenantId) {
                        $q2->whereNull('branch_id')
                        ->where(function ($sub) use ($tenantId) {
                            $sub->where('tenant_id', $tenantId)
                                ->orWhereNull('tenant_id');  
                        });
                    });
                });
                // default access overtimes 
               $overtimes = Overtime::with('user')
                ->whereHas('user', function ($query) use ($tenantId, $branchId, $departmentId, $authUserId) {
                    $query->where('tenant_id', $tenantId)
                        ->where('id', $authUserId) // Filter by user_id here
                        ->whereHas('employmentDetail', function ($q) use ($branchId, $departmentId) {
                            $q->where('branch_id', $branchId);
                            $q->where('department_id', $departmentId);
                        });
                })
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc');
                //default access branches
                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
                // default access geofences
                $geofences = Geofence::where('branch_id',$branchId);
                //default access departments
                $departments = Department::where(function ($query) use ($departmentId, $branchId, $tenantId, $authUserId) {
                    $query->where(function ($q) use ($departmentId, $branchId, $tenantId) {
                        $q->where('id', $departmentId)
                        ->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })->orWhere(function ($q) use ($departmentId, $authUserId) {
                        $q->where('id', $departmentId)
                        ->where('head_of_department', $authUserId);
                    });
                });
                // default access designations
                $designations = Designation::where('id', $designationId)
                    ->whereHas('department', function ($q) use ($branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) =>
                        $q->where('status', '1')]);
                //default access geofence users
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail', function ($q) use ($branchId,$departmentId,$authUserId) {
                    $q->where('branch_id', $branchId);
                    $q->where('department_id',$departmentId);
                    $q->where('user_id',$authUserId);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                // default access user deminimis
                $userDeminimis = UserDeminimis::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })->with(['deminimisBenefit', 'user']);
                //default access user earnings
                $userEarnings = UserEarning::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
                // default access user deductions
                $userDeductions = UserDeduction::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
                // default access official business
                $obEntries = OfficialBusiness::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
                // default access assets
                $assets = Assets::whereHas('branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                $assetsHistory = AssetsHistory::whereHas('branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                $assetsDetailsHistory = AssetsDetailsHistory::whereHas('assetDetail.assets.branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                // default access bulk attendances
                $bulkAttendances = BulkAttendance::where('user_id', $authUserId)
                ->whereHas('user', function ($query) use ($tenantId, $branchId, $departmentId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($edQ) use ($branchId, $departmentId) {
                            $edQ->where('status', '1')
                                ->where('branch_id', $branchId)
                                ->where('department_id', $departmentId);
                        });
                });
                // default access user attendances
                $userAttendances = RequestAttendance::where('user_id', $authUserId)->whereHas('user', function ($query) use ($tenantId, $branchId,$departmentId) {
                                    $query->where('tenant_id', $tenantId)
                                        ->whereHas('employmentDetail', function ($edQ) use ($branchId,$departmentId) {
                                            $edQ->where('status', '1')
                                                ->where('branch_id', $branchId)
                                                ->where('department_id', $departmentId);
                                        });
                                })
                                ->orderByRaw("FIELD(status, 'pending') DESC")
                                ->orderBy('request_date', 'desc');
                // default access payroll
                $payrolls = Payroll::where('user_id',$authUserId)->where('status', 'Pending');

            } 
                break;
        } 
        return [
            'holidays' => $holidays,
            'holidayException'=> $holidayException,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,  
            'attendances' => $attendances,
            'shiftList' => $shiftList,
            'employees' => $employees,
            'overtimes' => $overtimes,
            'policy' => $policy,
            'banks' => $banks,
            'leaveTypes' => $leaveTypes,
            'roles' => $roles,
            'payslips' => $payslips,
            'customFields' => $customFields,
            'geofences' => $geofences,
            'geofenceUsers' => $geofenceUsers,
            'sssContributions' => $sssContributions,
            'philHealthContributions' => $philHealthContributions,
            'withHoldingTax' => $withHoldingTax,
            'ots' => $ots,
            'benefits' => $benefits, 
            'userDeminimis' => $userDeminimis,
            'earningType' => $earningType,
            'userEarnings' => $userEarnings,
            'deductionType'=> $deductionType,
            'userDeductions' => $userDeductions,
            'obEntries' => $obEntries,
            'assets' => $assets,
            'assetsHistory' => $assetsHistory,
            'assetsDetailsHistory' => $assetsDetailsHistory,
            'bulkAttendances' => $bulkAttendances,
            'userAttendances' => $userAttendances,
            'payrollBatchSettings' => $payrollBatchSettings,
            'payrolls' => $payrolls
        ];
    } 

    public function fromBranch(Request $request)
    {
        $authUser = $this->authUser();
        $accessData = $this->getAccessData($authUser);
        $branchId = $request->input('branch_id');

        if (empty($branchId)) {
            $departments = $accessData['departments']->get()->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->department_name,
            ]);

            $designations = $accessData['designations']->get()->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->designation_name,
            ]);
        } else {
            $departments = $accessData['departments']
                ->where('branch_id', $branchId)
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->department_name,
                ]);

            $designations = $accessData['designations']
                ->whereHas('department', fn ($q) => $q->where('branch_id', $branchId))
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->designation_name,
                ]);
        }

        return response()->json([
            'status' => 'success',
            'departments' => $departments,
            'designations' => $designations,
        ]);
    }


    public function fromDepartment(Request $request)
    {     
        $authUser = $this->authUser();
        $accessData = $this->getAccessData($authUser);
        $departmentId = $request->input('department_id');
        $branchId = $request->input('branch_id');  

        if (empty($departmentId)) { 
            $departments = $accessData['departments'];

            if (!empty($branchId)) {
                $departments = $departments->where('branch_id', $branchId);
            }

            $departmentIds = $departments->pluck('id')->toArray();

            $designations = $accessData['designations']
                ->whereIn('department_id', $departmentIds)
                ->get()
                ->map(fn($d) => ['id' => $d->id, 'name' => $d->designation_name])
                ->values();

            return response()->json([
                'status' => 'success',
                'branch_id' => $branchId,
                'designations' => $designations,
            ]);
        }
 
        $department = $accessData['departments']->firstWhere('id', $departmentId);

        $designations = $accessData['designations']
            ->where('department_id', $departmentId)
            ->get()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->designation_name])
            ->values();

        return response()->json([
            'status' => 'success',
            'branch_id' => $department?->branch_id,
            'designations' => $designations,
        ]);
    } 
    public function fromDesignation(Request $request)
    {
        $authUser = $this->authUser();
        $accessData = $this->getAccessData($authUser);

        $designationId = $request->input('designation_id');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
 
        if (empty($designationId)) {
            $designations = $accessData['designations'];
 
            if (!empty($departmentId)) {
                $designations = $designations->where('department_id', $departmentId);
            } 
            elseif (!empty($branchId)) {
                $designations = $designations->filter(function ($d) use ($branchId) {
                    return $d->department && $d->department->branch_id == $branchId;
                });
            }

            $designations = $designations->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->designation_name,
            ])->values();

            return response()->json([
                'status' => 'success',
                'designations' => $designations,
            ]);
        }
 
        $designation = $accessData['designations']->firstWhere('id', $designationId);
        $department = $designation?->department;

        return response()->json([
            'status' => 'success',
            'branch_id' => $department?->branch_id,
            'department_id' => $designation?->department_id,
        ]);
    }


}
