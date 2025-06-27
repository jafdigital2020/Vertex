<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Holiday;
use App\Models\ShiftList;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\HolidayException;
use Illuminate\Support\Facades\Auth;

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

                $branches = Branch::where('tenant_id', $tenantId);
                $departments = Department::whereHas('branch', fn($q) => $q->where('tenant_id', $tenantId));
                $designations = Designation::whereHas('department.branch', fn($q) => 
                        $q->where('tenant_id', $tenantId))
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')]);
                break;

            case 'Branch-Level Access':
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

                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
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
                break;

            case 'Department-Level Access':
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

                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
                $departments = Department::where('id', $departmentId)
                    ->where('branch_id', $branchId)
                    ->whereHas('branch', fn($q) => $q->where('tenant_id', $tenantId))
                    ;
                $designations = Designation::whereHas('department', function ($q) use ($branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')])
                    ;
                break;

              case 'Personal Access Only':
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


                $branches = Branch::where('tenant_id', $tenantId)->where('id', $branchId);
                $departments = Department::where('id', $departmentId)
                    ->where('branch_id', $branchId)
                    ->whereHas('branch', fn($q) => $q->where('tenant_id', $tenantId));
                $designations = Designation::where('id', $designationId)
                    ->whereHas('department', function ($q) use ($branchId, $tenantId) {
                        $q->where('branch_id', $branchId)
                        ->whereHas('branch', fn($b) => $b->where('tenant_id', $tenantId));
                    })
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')]);
             break;

            default:
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
                $branches = Branch::where('tenant_id', $tenantId);
                $departments = Department::whereHas('branch', fn($q) => $q->where('tenant_id', $tenantId));
                $designations = Designation::whereHas('department.branch', fn($q) => 
                        $q->where('tenant_id', $tenantId))
                    ->withCount(['employmentDetail as active_employees_count' => fn($q) => 
                        $q->where('status', '1')]);
        } 
        return [
            'holidays' => $holidays,
            'holidayException'=> $holidayException,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,  
            'attendances' => $attendances,
            'shiftList' => $shiftList
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
