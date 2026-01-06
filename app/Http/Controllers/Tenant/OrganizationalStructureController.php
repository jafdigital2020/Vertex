<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DataAccessController;

class OrganizationalStructureController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::user();
    }

    /**
     * Get complete organizational structure with branches, departments, designations, and employees
     */
    public function getOrganizationalStructure(Request $request)
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        // Get branches with nested relationships
        $branches = $accessData['branches']
            ->with([
                'departments' => function ($query) {
                    $query->where('status', 1)
                          ->with([
                              'designations' => function ($q) {
                                  $q->where('status', 1)
                                    ->with([
                                        'employees' => function ($emp) {
                                            $emp->where('status', 1)
                                                ->with([
                                                    'personalInformation:id,user_id,first_name,last_name,middle_name',
                                                    'employmentDetail:id,user_id,employee_id,designation_id,status'
                                                ]);
                                        }
                                    ]);
                              }
                          ]);
                }
            ])
            ->where('status', 1)
            ->get();

        // Transform the data for better structure
        $organizationalStructure = $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'type' => $branch->branch_type,
                'location' => $branch->location,
                'contact_number' => $branch->contact_number,
                'departments' => $branch->departments->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->department_name,
                        'code' => $department->department_code,
                        'employee_count' => $department->designations->sum(function ($designation) {
                            return $designation->employees->count();
                        }),
                        'designations' => $department->designations->map(function ($designation) {
                            return [
                                'id' => $designation->id,
                                'name' => $designation->designation_name,
                                'description' => $designation->description,
                                'employee_count' => $designation->employees->count(),
                                'employees' => $designation->employees->map(function ($employee) {
                                    $personalInfo = $employee->personalInformation;
                                    $employmentDetail = $employee->employmentDetail;
                                    return [
                                        'id' => $employee->id,
                                        'employee_id' => $employmentDetail ? $employmentDetail->employee_id : null,
                                        'name' => $personalInfo 
                                            ? trim($personalInfo->first_name . ' ' . 
                                                   ($personalInfo->middle_name ? $personalInfo->middle_name . ' ' : '') .
                                                   $personalInfo->last_name)
                                            : 'No Name',
                                        'email' => $employee->email,
                                        'status' => $employee->status,
                                    ];
                                })->values()
                            ];
                        })->values()
                    ];
                })->values(),
                'total_employees' => $branch->departments->sum(function ($department) {
                    return $department->designations->sum(function ($designation) {
                        return $designation->employees->count();
                    });
                })
            ];
        });

        return response()->json([
            'message' => 'Organizational structure retrieved successfully.',
            'status' => 'success',
            'data' => $organizationalStructure,
            'summary' => [
                'total_branches' => $organizationalStructure->count(),
                'total_departments' => $organizationalStructure->sum(function ($branch) {
                    return count($branch['departments']);
                }),
                'total_designations' => $organizationalStructure->sum(function ($branch) {
                    return collect($branch['departments'])->sum(function ($dept) {
                        return count($dept['designations']);
                    });
                }),
                'total_employees' => $organizationalStructure->sum('total_employees')
            ]
        ]);
    }

    /**
     * Get filtered organizational structure
     */
    public function getFilteredStructure(Request $request)
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $designationId = $request->input('designation_id');

        $query = $accessData['branches'];

        // Apply filters
        if ($branchId) {
            $query->where('id', $branchId);
        }

        $branches = $query->with([
            'departments' => function ($deptQuery) use ($departmentId) {
                $deptQuery->where('status', 1);
                if ($departmentId) {
                    $deptQuery->where('id', $departmentId);
                }
                $deptQuery->with([
                    'designations' => function ($desigQuery) use ($designationId) {
                        $desigQuery->where('status', 1);
                        if ($designationId) {
                            $desigQuery->where('id', $designationId);
                        }
                        $desigQuery->with([
                            'employees' => function ($empQuery) {
                                $empQuery->where('status', 1)
                                         ->with([
                                             'personalInformation:id,user_id,first_name,last_name,middle_name',
                                             'employmentDetail:id,user_id,employee_id,designation_id,status'
                                         ]);
                            }
                        ]);
                    }
                ]);
            }
        ])->where('status', 1)->get();

        // Same transformation logic as above
        $filteredStructure = $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'departments' => $branch->departments->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->department_name,
                        'designations' => $department->designations->map(function ($designation) {
                            return [
                                'id' => $designation->id,
                                'name' => $designation->designation_name,
                                'employees' => $designation->employees->map(function ($employee) {
                                    $personalInfo = $employee->personalInformation;
                                    $employmentDetail = $employee->employmentDetail;
                                    return [
                                        'id' => $employee->id,
                                        'employee_id' => $employmentDetail ? $employmentDetail->employee_id : null,
                                        'name' => $personalInfo 
                                            ? trim($personalInfo->first_name . ' ' . 
                                                   ($personalInfo->middle_name ? $personalInfo->middle_name . ' ' : '') .
                                                   $personalInfo->last_name)
                                            : 'No Name',
                                        'email' => $employee->email,
                                    ];
                                })->values()
                            ];
                        })->values()
                    ];
                })->values()
            ];
        });

        return response()->json([
            'message' => 'Filtered organizational structure retrieved successfully.',
            'status' => 'success',
            'data' => $filteredStructure
        ]);
    }

    /**
     * Get hierarchical dropdown data for forms
     */
    public function getHierarchicalDropdownData(Request $request)
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');

        $result = [];

        // Always get branches
        $result['branches'] = $accessData['branches']
            ->where('status', 1)
            ->get(['id', 'name', 'branch_type'])
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'type' => $branch->branch_type
                ];
            });

        // Get departments if branch is selected
        if ($branchId) {
            $result['departments'] = $accessData['departments']
                ->where('branch_id', $branchId)
                ->where('status', 1)
                ->get(['id', 'department_name', 'department_code'])
                ->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->department_name,
                        'code' => $dept->department_code
                    ];
                });
        }

        // Get designations if department is selected
        if ($departmentId) {
            $result['designations'] = $accessData['designations']
                ->where('department_id', $departmentId)
                ->where('status', 1)
                ->get(['id', 'designation_name', 'description'])
                ->map(function ($desig) {
                    return [
                        'id' => $desig->id,
                        'name' => $desig->designation_name,
                        'description' => $desig->description
                    ];
                });
        }

        return response()->json([
            'message' => 'Hierarchical dropdown data retrieved successfully.',
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * Get lightweight organizational data for frontend filtering
     * Returns minimal data suitable for dropdowns and filtering
     */
    public function getLightweightOrganizationalData(Request $request)
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $includeEmployees = $request->query('include_employees', false);

        $branches = $accessData['branches']->where('status', 1);

        if ($includeEmployees) {
            // Include employees for small organizations
            $branches = $branches->with([
                'departments' => function ($query) {
                    $query->where('status', 1)
                          ->select('id', 'department_name', 'department_code', 'branch_id')
                          ->with([
                              'designations' => function ($q) {
                                  $q->where('status', 1)
                                    ->select('id', 'designation_name', 'department_id')
                                    ->with([
                                        'employees' => function ($emp) {
                                            $emp->where('status', 1)
                                                ->select('id', 'employee_id', 'designation_id')
                                                ->with('personalInformation:id,user_id,first_name,last_name');
                                        }
                                    ]);
                              }
                          ]);
                }
            ]);
        } else {
            // Only structure without employees
            $branches = $branches->with([
                'departments' => function ($query) {
                    $query->where('status', 1)
                          ->select('id', 'department_name', 'department_code', 'branch_id')
                          ->withCount(['designations' => function ($q) {
                              $q->where('status', 1);
                          }])
                          ->with([
                              'designations' => function ($q) {
                                  $q->where('status', 1)
                                    ->select('id', 'designation_name', 'department_id')
                                    ->withCount(['employees' => function ($emp) {
                                        $emp->where('status', 1);
                                    }]);
                              }
                          ]);
                }
            ]);
        }

        $lightweightData = $branches->get(['id', 'name', 'branch_type'])->map(function ($branch) use ($includeEmployees) {
            $branchData = [
                'id' => $branch->id,
                'name' => $branch->name,
                'type' => $branch->branch_type,
                'departments' => $branch->departments->map(function ($department) use ($includeEmployees) {
                    $deptData = [
                        'id' => $department->id,
                        'name' => $department->department_name,
                        'code' => $department->department_code,
                        'designations' => $department->designations->map(function ($designation) use ($includeEmployees) {
                            $desigData = [
                                'id' => $designation->id,
                                'name' => $designation->designation_name,
                            ];

                            if ($includeEmployees) {
                                $desigData['employees'] = $designation->employees->map(function ($employee) {
                                    $personalInfo = $employee->personalInformation;
                                    return [
                                        'id' => $employee->id,
                                        'employee_id' => $employee->employee_id,
                                        'name' => $personalInfo 
                                            ? trim($personalInfo->first_name . ' ' . $personalInfo->last_name)
                                            : 'No Name'
                                    ];
                                });
                            } else {
                                $desigData['employee_count'] = $designation->employees_count ?? 0;
                            }

                            return $desigData;
                        })
                    ];

                    if (!$includeEmployees) {
                        $deptData['designation_count'] = $department->designations_count ?? 0;
                        $deptData['total_employees'] = $department->designations->sum('employees_count');
                    }

                    return $deptData;
                })
            ];

            return $branchData;
        });

        return response()->json([
            'message' => 'Lightweight organizational data retrieved successfully.',
            'status' => 'success',
            'data' => $lightweightData,
            'meta' => [
                'includes_employees' => $includeEmployees,
                'total_branches' => $lightweightData->count()
            ]
        ]);
    }
}
