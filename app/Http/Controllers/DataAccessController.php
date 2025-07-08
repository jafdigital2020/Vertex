<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Role;
use App\Models\User;
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
use App\Models\DeductionType;
use App\Models\UserDeduction;
use App\Models\UserDeminimis;
use App\Models\ShiftAssignment;
use App\Models\HolidayException;
use App\Models\DeminimisBenefits;
use App\Models\WithholdingTaxTable;
use App\Models\SssContributionTable;
use Illuminate\Support\Facades\Auth;
use App\Models\PhilhealthContribution;

class DataAccessController extends Controller
{  
     public function authUser() {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        } 
        return Auth::guard('web')->user();
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
        ->whereHas('targets', function ($query) use ($branchId, $departmentId, $authUserId) {
            $query->where(function ($q) {
                $q->where('target_type', 'company-wide');
            })->orWhere(function ($q) use ($branchId) {
                $q->where('target_type', 'branch')
                ->where('target_id', $branchId);
            })->orWhere(function ($q) use ($departmentId) {
                $q->where('target_type', 'department')
                ->where('target_id', $departmentId);
            })->orWhere(function ($q) use ($authUserId) {
                $q->where('target_type', 'employee')
                ->where('target_id', $authUserId);
            });
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
        switch ($accessName) {
            case 'Organization-Wide Access':
              
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
                
                $branchesQuery = Branch::where('tenant_id', $tenantId); 
                $branchIds = []; 
                if (!empty($authUser->userPermission->user_permission_access->access_ids)) {
                    $branchIds = explode(',', $authUser->userPermission->user_permission_access->access_ids); 
                    $branchIds = array_filter($branchIds, fn($id) => $id !== '');
                }

                if (count($branchIds) > 0) {
                    $branchesQuery->whereIn('id', $branchIds);
                } else {  
                    $branchesQuery->whereRaw('0=1');  
                }

                $branches = $branchesQuery;
                $geofences = Geofence::whereIn('branch_id',$branchIds);
                $holidayException = HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                        $q->where('tenant_id', $tenantId);
                    })
                    ->whereHas('user.employmentDetail', function ($q) use ($branchIds) {
                        $q->whereIn('branch_id', $branchIds);
                    })
                    ->with([
                        'holiday',
                        'user.personalInformation',
                        'user.employmentDetail.branch',
                        'user.employmentDetail.department'
                    ]); 
         
                $overtimes = Overtime::with('user.employmentDetail')  
                ->whereHas('user', function ($query) use ($tenantId, $branchIds) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($q) use ($branchIds) {
                            $q->whereIn('branch_id', $branchIds);
                        });
                })
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc');


                $attendances = Attendance::with([
                        'user.personalInformation',
                        'user.employmentDetail.branch',
                        'user.employmentDetail.department',
                        'shift',
                    ])
                    ->whereHas('user', function ($userQ) use ($tenantId, $branchIds) {
                        $userQ->where('tenant_id', $tenantId)
                            ->whereHas('employmentDetail', function ($edQ) use ($branchIds) {
                                $edQ->where('status', '1');

                                if (!empty($branchIds)) {
                                    $edQ->whereIn('branch_id', $branchIds);
                                }
                            });
                });
                
                $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation',
                ]) ->whereHas('employmentDetail.branch', function ($q) use ($branchIds) {
                    $q->whereIn('id', $branchIds);
                });

                $shiftList = ShiftList::whereHas('branch', function ($query) use ($authUser, $branchIds) {
                    $query->where('tenant_id', $authUser->tenant_id)
                        ->whereIn('id', $branchIds);
                });
               
                $departments = Department::whereHas('branch', function ($q) use ($tenantId, $branchIds) {
                    $q->where('tenant_id', $tenantId)
                    ->whereIn('id', $branchIds);
                });
                $designations = Designation::whereHas('department.branch', function ($q) use ($tenantId, $branchIds) {
                    $q->where('tenant_id', $tenantId)
                    ->whereIn('id', $branchIds);
                })
                ->withCount(['employmentDetail as active_employees_count' => function ($q) {
                    $q->where('status', '1');
                }]);

                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail.branch', function ($q) use ($branchIds) {
                    $q->whereIn('id', $branchIds);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                $userDeminimis = UserDeminimis::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$branchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $branchIds);
                })->with(['deminimisBenefit', 'user']);
                $userEarnings = UserEarning::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$branchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $branchIds);
                });
                $userDeductions = UserDeduction::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$branchIds) {
                    $q->where('tenant_id', $tenantId);
                    $q->whereIn('id', $branchIds);
                });
                break;

            case 'Branch-Level Access':
                $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation',
                ])->whereHas('employmentDetail', fn($q) => $q->where('branch_id', $branchId));
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
                $holidayException =  HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                  })->with([
                        'holiday',
                        'user.personalInformation',      
                        'user.employmentDetail.branch',  
                        'user.employmentDetail.department'  
                    ])->whereHas('user.employmentDetail.branch', function ($q) use ($branchId) {
                    $q->where('id', $branchId);
                });
                $attendances = Attendance::with('user.employmentDetail','user.personalInformation', 'user.employmentDetail.department','shift')
                        ->whereHas('user', function ($query) use ($tenantId, $branchId) {
                            $query->where('tenant_id', $tenantId)
                                ->whereHas('employmentDetail', function ($subQuery) use ($branchId) {
                                    $subQuery->where('status', '1')
                                            ->where('branch_id', $branchId);
                                });
                        }); 
                
                $shiftList = ShiftList::where('branch_id', $branchId)->whereHas('branch', function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                });  
                $overtimes = Overtime::with('user')
                ->whereHas('user', function ($query) use ($tenantId, $branchId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereHas('employmentDetail', function ($q) use ($branchId) {
                            $q->where('branch_id', $branchId);
                        });
                })
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc');
                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
                $geofences = Geofence::whereIn('branch_id',$branchId);
                $departments = Department::where('branch_id', $branchId)
                    ->whereHas('branch', fn($q) => $q->where('tenant_id', $tenantId))
                    ;
                $designations = Designation::whereHas('department', function ($q) use ($branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')])
                    ; 
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail.branch', function ($q) use ($branchId) {
                    $q->where('id', $branchId);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);

                $userDeminimis = UserDeminimis::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                })->with(['deminimisBenefit', 'user']);
                 $userEarnings = UserEarning::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                $userDeductions = UserDeduction::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId,$branchId) {
                    $q->where('tenant_id', $tenantId);
                    $q->where('id', $branchId);
                });
                break;

            case 'Department-Level Access':
                 $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation',
                ])->whereHas('employmentDetail', fn($q) =>
                     $q->where('branch_id', $branchId)->where('department_id', $departmentId)
                 );

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
                 $holidayException =  HolidayException::whereHas('holiday', function ($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId);
                    })->with([
                            'holiday',
                            'user.personalInformation',      
                            'user.employmentDetail.branch',  
                            'user.employmentDetail.department'  
                        ])->whereHas('user.employmentDetail.branch', function ($q) use ($branchId) {
                        $q->where('id', $branchId);
                        })->whereHas('user.employmentDetail.department', function ($q) use ($departmentId) {
                        $q->where('id', $departmentId);  
                    });
                $attendances = Attendance::with('user.employmentDetail','user.personalInformation', 'user.employmentDetail.department','shift')
                        ->whereHas('user', function ($query) use ($tenantId, $branchId,$departmentId) {
                            $query->where('tenant_id', $tenantId)
                                ->whereHas('employmentDetail', function ($subQuery) use ($branchId,$departmentId) {
                                    $subQuery->where('status', '1')
                                            ->where('branch_id', $branchId)
                                            ->where('department_id',$departmentId);
                                });
                        }); 
                $shiftList = ShiftList::where('branch_id', $branchId)->whereHas('branch', function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                });  
                $overtimes = Overtime::with('user')
                    ->whereHas('user', function ($query) use ($tenantId, $branchId, $departmentId) {
                        $query->where('tenant_id', $tenantId)
                            ->whereHas('employmentDetail', function ($q) use ($branchId, $departmentId) {
                                $q->where('branch_id', $branchId);
                                $q->where('department_id', $departmentId);
                            });
                    }) 
                ->orderByRaw("FIELD(status, 'pending') DESC")
                ->orderBy('overtime_date', 'desc');
                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
                $geofences = Geofence::where('branch_id',$branchId);
                $departments = Department::where(function ($query) use ($departmentId, $branchId, $tenantId,$authUserId) {
                    $query->where(function ($q) use ($departmentId, $branchId, $tenantId) {
                        $q->where('id', $departmentId)
                        ->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })->orWhere(function ($q) use ($departmentId, $authUserId) {
                        $q->where('id', $departmentId)
                        ->where('head_of_department', $authUserId);
                    });
                })->get();
                $designations = Designation::whereHas('department', function ($q) use ($branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')])
                    ;
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail', function ($q) use ($branchId,$departmentId) {
                    $q->where('branch_id', $branchId);
                    $q->where('department_id',$departmentId);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                $userDeminimis = UserDeminimis::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })->with(['deminimisBenefit', 'user']);
                $userEarnings = UserEarning::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                $userDeductions = UserDeduction::whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                break;

              case 'Personal Access Only': 
                  
                 $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation',
                ])->where('id', $authUser->id);
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

                $shiftList = ShiftList::where('branch_id', $branchId)->whereHas('branch', function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                });  
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

                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
                $geofences = Geofence::where('branch_id',$branchId);
                $departments = Department::where(function ($query) use ($departmentId, $branchId, $tenantId, $authUserId) {
                    $query->where(function ($q) use ($departmentId, $branchId, $tenantId) {
                        $q->where('id', $departmentId)
                        ->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })->orWhere(function ($q) use ($departmentId, $authUserId) {
                        $q->where('id', $departmentId)
                        ->where('head_of_department', $authUserId);
                    });
                })->get();

                $designations = Designation::where('id', $designationId)
                    ->whereHas('department', function ($q) use ($branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')]);
                $geofenceUsers = GeofenceUser::whereHas('user.employmentDetail', function ($q) use ($branchId,$departmentId,$authUserId) {
                    $q->where('branch_id', $branchId);
                    $q->where('department_id',$departmentId);
                    $q->where('user_id',$authUserId);
                })->with(['geofence', 'user.personalInformation', 'user.employmentDetail.branch']);
                $userDeminimis = UserDeminimis::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })->with(['deminimisBenefit', 'user']);
                $userEarnings = UserEarning::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
                $userDeductions = UserDeduction::where('user_id',$authUserId)->whereHas('user.employmentDetail.branch', function ($q) use ($tenantId, $branchId) {
                        $q->where('tenant_id', $tenantId)
                        ->where('id', $branchId);
                    })->whereHas('user.employmentDetail', function ($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    }); 
             break;

            default:
                $policy = Policy::where('tenant_id', $tenantId);
                $employees = User::where('tenant_id', $authUser->tenant_id)
                ->with([
                    'personalInformation',
                    'employmentDetail.branch',
                    'role',
                    'userPermission',
                    'designation',
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
                $shiftList = ShiftList::whereHas('branch', function ($query) use ($authUser) {
                    $query->where('tenant_id', $authUser->tenant_id);
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
            'userDeductions' => $userDeductions
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
