<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Models\User;
use App\Models\Violation;
use Illuminate\Http\Request;
use App\Models\ViolationTypes;
use Illuminate\Support\Carbon;
use App\Models\ViolationAction;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use App\Models\ViolationAttachment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Notifications\UserNotification;
use App\Http\Controllers\DataAccessController;
use App\Http\Controllers\Tenant\Payroll\PayrollController;
use App\Models\Payroll;
use App\Models\UserEarning;

class ViolationController extends Controller
{
    //
    public function authUser(){
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
      
        }
        return Auth::user();
    }

    public function getEmployeesByBranch(Request $request)
    {
        $authUser = $this->authUser();
        $branchId = $request->input('branch_id');

        // Handle multiple branches or "all"
        $branchIds = [];
        if ($branchId) {
            if ($branchId === 'all') {
                // Don't filter by branch - get all
                $branchIds = [];
            } else {
                // Split comma-separated branch IDs
                $branchIds = explode(',', $branchId);
                $branchIds = array_filter($branchIds); // Remove empty values
            }
        }

        // Get all active employees from database, optionally filtered by branch_id(s)
        $query =  User::whereHas('employmentDetail', function ($q) use ($branchIds) {
            $q->where('status', 1); // only active employees
            if (!empty($branchIds)) {
                $q->whereIn('branch_id', $branchIds);
            }
        })
        ->with(['personalInformation', 'employmentDetail.branch']);

        $employees = $query->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => trim(($emp->personalInformation->first_name ?? '') . ' ' . ($emp->personalInformation->last_name ?? '')),
                    'employee_id' => $emp->employmentDetail->employee_id ?? null,
                    'branch' => $emp->employmentDetail->branch->name ?? 'N/A',
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'employees' => $employees,
        ]);
    }

    public function show($id)
    {
        try {
            $violation = Violation::with([
                'violationType',
                'employee.employmentDetail.branch',
                'employee.employmentDetail.department',
                'employee.employmentDetail.designation',
                'employee.personalInformation',
                'actions',
                'attachments.uploader.personalInformation'
            ])->findOrFail($id);

            // Get employee reply action
            $employeeReply = $violation->actions
                ->where('action_type', 'employee_reply')
                ->first();

            return response()->json([
                'status' => 'success',
                'violation' => [
                    'id' => $violation->id,
                    'employee_name' => $violation->employee->personalInformation
                        ? trim($violation->employee->personalInformation->first_name . ' ' .
                               ($violation->employee->personalInformation->middle_name ?? '') . ' ' .
                               $violation->employee->personalInformation->last_name)
                        : 'N/A',
                    'employee_id' => $violation->employee->employmentDetail->employee_id ?? 'N/A',
                    'branch' => $violation->employee->employmentDetail->branch->name ?? 'N/A',
                    'department' => $violation->employee->employmentDetail->department->department_name ?? 'N/A',
                    'designation' => $violation->employee->employmentDetail->designation->designation_name ?? 'N/A',
                    'status' => $violation->status,
                    'violation_type' => $violation->violationType->name ?? '',
                    'verbal_reprimand_date' => $violation->verbal_reprimand_date
                    ? Carbon::parse($violation->verbal_reprimand_date)->format('M d, Y')
                    : null,
                    'written_reprimand_date' => $violation->written_reprimand_date
                    ? Carbon::parse($violation->written_reprimand_date)->format('M d, Y')
                    : null, 
                    'suspension_start_date' => $violation->suspension_start_date
                        ? Carbon::parse($violation->suspension_start_date)->format('M d, Y')
                        : null,
                    'suspension_end_date' => $violation->suspension_end_date
                        ? Carbon::parse($violation->suspension_end_date)->format('M d, Y')
                        : null,
                    'suspension_days' => $violation->suspension_days,
                    'termination_date' => $violation->termination_date
                        ? Carbon::parse($violation->termination_date)->format('M d, Y')
                        : null,
                    'offense_details' => $violation->offense_details,
                    'disciplinary_action' => $violation->disciplinary_action,
                    'remarks' => $violation->remarks,
                    'investigation_notes' => $violation->investigation_notes,
                    'implementation_remarks' => $violation->implementation_remarks,
                    'information_report_file' => $violation->information_report_file,
                    'nowe_file' => $violation->nowe_file,
                    'dam_file' => $violation->dam_file,
                    'created_at' => $violation->created_at ? $violation->created_at->format('M d, Y') : null,
                    'employee_reply' => $employeeReply ? [
                        'description' => $employeeReply->description,
                        'file_path' => $employeeReply->file_path,
                        'action_date' => $employeeReply->action_date
                            ? Carbon::parse($employeeReply->action_date)->format('M d, Y')
                            : null,
                    ] : null,
                    'attachments' => $violation->attachments->map(function ($attachment) {
                        return [
                            'id' => $attachment->id,
                            'file_name' => $attachment->file_name,
                            'file_path' => $attachment->file_path,
                            'file_type' => $attachment->file_type,
                            'file_size' => $attachment->file_size,
                            'attachment_type' => $attachment->attachment_type,
                            'uploaded_by' => $attachment->uploader
                                ? trim(($attachment->uploader->personalInformation->first_name ?? '') . ' ' .
                                       ($attachment->uploader->personalInformation->last_name ?? ''))
                                : 'N/A',
                            'uploaded_at' => $attachment->created_at ? $attachment->created_at->format('M d, Y h:i A') : null,
                        ];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching violation details: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Violation record not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $authUser = $this->authUser();

        $request->validate([
            'offense_details' => 'required|string|max:2000',
            'disciplinary_action' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
            'information_report_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'attachments.*' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $violation = Violation::findOrFail($id);

            $updateData = [
                'offense_details' => $request->offense_details,
                'disciplinary_action' => $request->disciplinary_action,
                'remarks' => $request->remarks,
            ];

            // Handle file upload if provided
            if ($request->hasFile('information_report_file')) {
                $file = $request->file('information_report_file');
                $filename = 'violation_report_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('violations/reports', $filename, 'public');
                $updateData['information_report_file'] = $path;
            }

            // Handle multiple attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    // Get file info before moving
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getClientMimeType();
                    $fileSize = $file->getSize();

                    $fileName = time() . '_' . $index . '_' . $originalName;
                    $file->move(public_path('storage/violation_reports'), $fileName);
                    $filePath = "violation_reports/{$fileName}";

                    ViolationAttachment::create([
                        'violation_id' => $violation->id,
                        'file_name' => $originalName,
                        'file_path' => $filePath,
                        'file_type' => $mimeType,
                        'file_size' => $fileSize,
                        'attachment_type' => 'information_report',
                        'uploaded_by' => $authUser->id,
                    ]);
                }
            }

            $violation->update($updateData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Violation updated successfully.',
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating violation: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error updating violation.'
            ], 500);
        }
    }

     public function filter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(60);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
 
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');
        $type = $request->input('type');
        $query =Violation::
            with([
                'employee.personalInformation',
                'employee.employmentDetail.branch',
                'employee.employmentDetail.department',
                'employee.employmentDetail.designation', 
            ])
            ->whereHas('employee.employmentDetail', function ($q) {
                $q->where('status', 1); 
            })
            ->latest(); 
          
        if ($branch) {
            $query->whereHas('employee.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
 
        if ($department) {
            $query->whereHas('employee.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
 
        if ($designation) {
            $query->whereHas('employee.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        } 
        if (!is_null($status)) {
            $query->where('status', $status);
        }
        if (!is_null($type)) {
            $query->where('violation_type_id', $type);
        }

 
        $violation = $query->get(); 
   

        $html = view('tenant.violation.violation-admin-filter', [
            'violation' => $violation,
            'permission' => $permission
        ])->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }


    public function adminViolationEmployeeListIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(60);
 
        $dataAccessController = new DataAccessController(); 
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get(); 
        $departments  = $accessData['departments']->get();
        $designations = $accessData['designations']->get(); 
        $employeeOptions = [];
 
        $violation =Violation::
             with([
                'employee.personalInformation',
                'employee.employmentDetail.branch',
                'employee.employmentDetail.department',
                'employee.employmentDetail.designation', 
            ])
            ->whereHas('employee.employmentDetail', function ($query) {
                $query->where('status', 1); 
            }) 
            ->latest()->get();
 
        $violationTypes = ViolationTypes::all();
 
        return view('tenant.violation.violation-admin', [
            'permission' => $permission,
            'violation' => $violation,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'employeeOptions' => $employeeOptions,
            'violationTypes' => $violationTypes,
        ]);
    }

        public function empfilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(61);

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
  
        $status = $request->input('status');
        $type =  $request->input('type');
 
        $query = Violation::with([
            'employee.personalInformation',
            'employee.employmentDetail.branch',
            'employee.employmentDetail.department',
            'employee.employmentDetail.designation',
        ])
        ->where('user_id', $authUser->id) 
        ->latest(); 
           
        if (!is_null($status)) {
            $query->where('status', $status);
        }
 
        if (!is_null($type)) {
            $query->where('violation_type_id', $type);
        }
        $violation = $query->get(); 
   

        $html = view('tenant.violation.violation-employee-filter', [
            'violations' => $violation,
            'permission' => $permission
        ])->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }



    public function violationEmployeeListIndex(Request $request)
    {
        $authUser = $this->authUser(); 
        $permission = PermissionHelper::get(61);
 
        
        $violations =Violation::with([
            'employee.personalInformation',
            'employee.employmentDetail.branch',
            'employee.employmentDetail.department',
            'employee.employmentDetail.designation',
        ])
        ->where('user_id', $authUser->id) 
        ->latest()
        ->get();

        $violationTypes  = ViolationTypes::all();

        // // If frontend calls via AJAX or fetch()
        // if ($request->wantsJson()) {
        //     return response()->json([
        //         'status' => 'success',
        //         'violations' => $violations->map(function ($s) {
        //             return [
        //                 'id' => $s->id,
        //                 'offense_details' => $s->offense_details,
        //                 'status' => $s->status,
        //                 'violation_type' => $s->violation_type,
        //                 'start_date' => $s->violation_start_date,
        //                 'end_date' => $s->violation_end_date,
        //                 'information_report_file' => $s->information_report_file,
        //                 'employee_name' => optional($s->employee->personalInformation)->first_name . ' ' . optional($s->employee->personalInformation)->last_name,
        //                 'employee_id' => optional($s->employee->employmentDetail)->employee_id,
        //                 'branch' => optional($s->employee->employmentDetail->branch)->name,
        //                 'department' => optional($s->employee->employmentDetail->department)->department_name,
        //                 'designation' => optional($s->employee->employmentDetail->designation)->designation_name,
        //             ];
        //         }),
        //     ]);
        // }

        // For non-AJAX view load
        return view('tenant.violation.violation-employee', [
            'permission' => $permission,
            'violations' => $violations,
            'violationTypes' => $violationTypes
        ]);
    }




    public function fileViolationReport(Request $request)
    {
        $authUser = $this->authUser();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'offense_details' => 'required|string|max:1000',
            'information_report_file' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'attachments.*' => 'nullable|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Keep backward compatibility with single file upload
            $filePath = null;
            if ($request->hasFile('information_report_file')) {
                $file = $request->file('information_report_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('storage/violation_reports'), $fileName);
                $filePath = "violation_reports/{$fileName}";
            }

            $violation = Violation::create([
                'user_id' => $request->user_id,
                'offense_details' => $request->offense_details,
                'information_report_file' => $filePath,
                'status' => 'pending',
            ]);

            // Handle multiple attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    // Get file info before moving
                    $originalName = $file->getClientOriginalName();
                    $mimeType = $file->getClientMimeType();
                    $fileSize = $file->getSize();

                    $fileName = time() . '_' . $index . '_' . $originalName;
                    $file->move(public_path('storage/violation_reports'), $fileName);
                    $filePath = "violation_reports/{$fileName}";

                    ViolationAttachment::create([
                        'violation_id' => $violation->id,
                        'file_name' => $originalName,
                        'file_path' => $filePath,
                        'file_type' => $mimeType,
                        'file_size' => $fileSize,
                        'attachment_type' => 'information_report',
                        'uploaded_by' => $authUser->id,
                    ]);
                }
            }

            ViolationAction::create([
                'violation_id' => $violation->id,
                'action_type' => 'report_received',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => 'Information report received for offense.',
            ]);

            if($request->user_id){
                $user = User::find($request->user_id);
                if ($user) {
                    $user->notify(
                        new UserNotification(
                            "A violation has been filed against you. Please wait for the Notice of Written Explanation (NOWE) to be uploaded by the admin before you can submit your response."
                        )
                    );
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Violation case successfully filed.',
                'data' => $violation
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error filing violation case: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['information_report_file', 'attachments']),
                'has_main_file' => $request->hasFile('information_report_file'),
                'has_attachments' => $request->hasFile('attachments'),
                'attachments_count' => $request->hasFile('attachments') ? count($request->file('attachments')) : 0
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error filing violation case: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    public function issueNOWE(Request $request, $id)
    {
        $authUser = $this->authUser();
        $request->validate([
            'nowe_file' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $violation =Violation::findOrFail($id);
            $file = $request->file('nowe_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/violation_nowe'), $fileName);
            $filePath = 'violation_nowe/' . $fileName;

            $violation->update([
                'status' => 'awaiting_reply',
                'remarks' => 'NOWE issued on ' . now()->format('F d, Y'),
            ]);

           ViolationAction::create([
                'violation_id' => $violation->id,
                'action_type' => 'nowe_issued',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'file_path' => $filePath,
                'description' => 'Notice of Written Explanation issued to employee.'
            ]);
            if ($violation->user_id) { 
                $user = User::find($violation->user_id); 
                if ($user) {
                    $user->notify(
                        new UserNotification(
                            "A NOWE has been uploaded for your violation. You may now submit your response."
                        )
                    );
                }
            }
 
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'NOWE successfully issued.',
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error issuing NOWE: ' . $e->getMessage(), [
                'violation_id' => $id,
                'user_id' => $authUser->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error issuing NOWE. Please try again.'
            ], 500);
        }
    }

    public function receiveReply(Request $request, $id)
    {
        $authUser = $this->authUser();
        $request->validate([
            'reply_file' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $violation =Violation::findOrFail($id);
            $file = $request->file('reply_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/violation_replies'), $fileName);

            $violation->update([
                'status' => 'under_investigation',
                'remarks' => 'Employee reply received.',
            ]);

           ViolationAction::create([
                'violation_id' => $violation->id,
                'action_type' => 'employee_reply',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'file_path' => 'violation_replies/' . $fileName,
                'description' => 'Employee reply received and recorded.'
            ]);
            
             if ($violation->user_id) {
             
                $violation_admin_id =ViolationAction::where('violation_id', $violation->id)
                    ->where('action_type', 'nowe_issued')
                    ->value('action_by');

                $adminUser = User::find($violation_admin_id);
 
                $employeeName = 
                (Auth::user()->personalInformation->first_name ?? '') . ' ' . 
                (Auth::user()->personalInformation->last_name ?? '');

                if ($adminUser) {
                    $adminUser->notify(
                        new UserNotification(
                            "{$employeeName} has submitted a reply for violation #{$violation->id}."
                        )
                    );
                }
            }
 
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee reply successfully received.',
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error receiving employee reply: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error receiving employee reply.'
            ], 500);
        }
    }

    public function conductInvestigation(Request $request, $id)
    {
        $authUser = $this->authUser();
        $request->validate([
            'investigation_notes' => 'required|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $violation =Violation::findOrFail($id);
            $violation->update([
                'status' => 'for_dam_issuance',
                'remarks' => 'Investigation conducted and case assessed.',
            ]);

           ViolationAction::create([
                'violation_id' => $violation->id,
                'action_type' => 'investigation',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => $request->investigation_notes,
            ]);

            if ($violation->user_id) { 
                $user = User::find($violation->user_id); 
                if ($user) {
                    $user->notify(
                        new UserNotification(
                            "An investigation report has been issued for your violation. Please review it accordingly."
                        )
                    );
                }
            }
 
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Investigation recorded successfully.',
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during investigation: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error recording investigation.'
            ], 500);
        }
    }

    public function issueDAM(Request $request, $id)
    {
        $authUser = $this->authUser();
        $request->validate([
            'dam_file' => 'required|mimes:pdf,doc,docx|max:2048',
            'violation_type_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $violation =Violation::find($id);
            if (!$violation) {
                DB::rollBack();
                Log::warning('Violation not found while issuing DAM', ['violation_id' => $id, 'action_by' => $authUser->id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Violation case not found.'
                ], 404);
            }

            $file = $request->file('dam_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/violation_dam'), $fileName);
            $filePath = "violation_dam/{$fileName}";

            $violation->update([
                'dam_file' => $filePath,
                'dam_issued_at' => now(),
                'violation_type_id' => $request->violation_type_id,
                'status' => 'dam_issued',
            ]);

           ViolationAction::create([
                'violation_id' => $violation->id,
                'action_type' => 'dam_issued',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'file_path' => $filePath,
                'description' => 'Disciplinary Action Memo issued to employee (' . str_replace('_', ' ', $request->violation_type_id) . ').',
            ]);
            
            if ($violation->user_id) { 
                $user = User::find($violation->user_id); 

                if ($user && $request->violation_type_id !== null ) { 

                   $violationName = ViolationTypes::where('id', $request->violation_type_id)
                    ->value('name');

                    if ($violationName) {
                        $user->notify(
                            new UserNotification(
                                "A Disciplinary Action Memo has been uploaded for your violation: {$violationName}."
                            )
                        );
                    }

                }
            }
 
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'DAM issued successfully with violation type: ' .  $violationName . '.',
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error issuing DAM: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error issuing DAM.'
            ], 500);
        }
    }

    
    public function implementViolation(Request $request, $id)
    {
        $authUser = $this->authUser();

        $violation = Violation::findOrFail($id);
        $user = User::find($violation->user_id);   

        $rules = [
            'implementation_remarks' => 'nullable|string|max:1000',
        ];

        switch ($violation->violationType->name) {
            case 'Verbal Reprimand':

                $rules['verbal_reprimand_date'] = 'required|date';
                $rules['verbal_reprimand_file'] = 'nullable|array';
                $rules['verbal_reprimand_file.*'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';

                break;

            case 'Written Reprimand':

                $rules['written_reprimand_date'] = 'nullable|date';
                $rules['written_reprimand_file'] = 'required|array';
                $rules['written_reprimand_file.*'] = 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
                break;

            case 'Suspension':

                $rules['suspension_start_date'] = 'required|date';
                $rules['suspension_end_date'] = 'required|date|after_or_equal:suspension_start_date';
                break;

            case 'Termination':

                $rules['termination_date'] = 'required|date';
                break;
        }
 
        try {
            $request->validate($rules);
        } catch (ValidationException $e) {
            Log::warning('Violation validation failed', [
                'violation_id'   => $id,
                'violation_type' => $violation->violationType->name,
                'errors'         => $e->errors(),
                'user_id'        => $authUser->id ?? null,
            ]);
            throw $e;
        } 
        try {
            DB::beginTransaction();

            switch ($violation->violationType->name) { 

                case 'Verbal Reprimand':

                    try {
                        $violation->update([ 
                            'verbal_reprimand_date' => $request->verbal_reprimand_date, 
                            'status' => 'implemented',
                            'implemented_by' => $authUser->id,
                            'implementation_remarks' => $request->implementation_remarks,
                        ]);
  
                        if ($user) { 
                            $user->notify(
                            new UserNotification(
                                "HR has scheduled a verbal reprimand meeting with your department head / reporting to on {$request->verbal_reprimand_date}."
                            )
                            );
                            $employment = $user->employmentDetail;
                            $fullName = trim(
                                ($user->personalInformation->first_name ?? '') . ' ' . 
                                ($user->personalInformation->last_name ?? '')
                            );

                            if ($employment && $employment->reporting_to) {
                                $reportingTo = User::find($employment->reporting_to);

                                if ($reportingTo) {
                                    
                                    $reportingTo->notify(
                                        new UserNotification(
                                          "HR has scheduled a verbal reprimand meeting with {$fullName} on {$request->verbal_reprimand_date}."
                                        )
                                    );
                                }
                            }  
                            if ($employment && $employment->department?->head_of_department) {
                                $deptHead = User::find($employment->department->head_of_department);

                                if ($deptHead) {
                                    $deptHead->notify(
                                        new UserNotification(
                                          "HR has scheduled a verbal reprimand meeting with {$fullName} on {$request->verbal_reprimand_date}."
                                        )
                                    );
                                }
                            }
                        }  

                    } catch (\Exception $e) {
                        Log::error('Verbal Reprimand update failed', [
                            'violation_id' => $violation->id,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }

                    if ($request->hasFile('verbal_reprimand_file')) {
                        try {
                            $files = is_array($request->file('verbal_reprimand_file'))
                                ? $request->file('verbal_reprimand_file')
                                : [$request->file('verbal_reprimand_file')];

                         foreach ($files as $index => $file) { 
                            $originalName = $file->getClientOriginalName();
                            $mimeType     = $file->getClientMimeType();
                            $fileSize     = $file->getSize();   
                            $fileName = time().'_'.$index.'_'.$originalName;
                            $file->move(public_path('storage/violation_reports'), $fileName); 
                            $filePath = 'violation_reports/'.$fileName; 
                            ViolationAttachment::create([
                                'violation_id'   => $violation->id,
                                'file_name'      => $originalName,
                                'file_path'      => $filePath,
                                'file_type'      => $mimeType,
                                'file_size'      => $fileSize,
                                'attachment_type' => 'verbal_reprimand_file',
                                'uploaded_by'    => $authUser->id,
                            ]);
                        }
                        } catch (\Exception $e) {
                            Log::error('Verbal Reprimand file upload failed', [
                                'violation_id' => $violation->id,
                                'error' => $e->getMessage(),
                            ]);
                            throw $e;
                        }
                    }
                    break;
 
                case 'Written Reprimand':
                    try {
                         $violation->update([ 
                            'written_reprimand_date' => $request->written_reprimand_date ?? null, 
                            'status' => 'implemented',
                            'implemented_by' => $authUser->id,
                            'implementation_remarks' => $request->implementation_remarks,
                        ]);

                        
                        if($request->written_reprimand_date !== null ){

                         if ($user) { 
                            
                            $user->notify(
                            new UserNotification(
                                "HR has scheduled a written reprimand meeting with your department head / reporting to on {$request->written_reprimand_date}."
                            )
                            );
                            $employment = $user->employmentDetail;
                            $fullName = trim(
                                ($user->personalInformation->first_name ?? '') . ' ' . 
                                ($user->personalInformation->last_name ?? '')
                            );

                            if ($employment && $employment->reporting_to) {
                                $reportingTo = User::find($employment->reporting_to);

                                if ($reportingTo) {
                                    
                                    $reportingTo->notify(
                                        new UserNotification(
                                          "HR has scheduled a written reprimand meeting with {$fullName} on {$request->written_reprimand_date}."
                                        )
                                    );
                                }
                            }  
                            if ($employment && $employment->department?->head_of_department) {
                                $deptHead = User::find($employment->department->head_of_department);

                                if ($deptHead) {
                                    $deptHead->notify(
                                        new UserNotification(
                                          "HR has scheduled a written reprimand meeting with {$fullName} on {$request->written_reprimand_date}."
                                        )
                                    );
                                }
                            }
                        } 
                    }


                    } catch (\Exception $e) {
                        Log::error('Written Reprimand update failed', [
                            'violation_id' => $violation->id,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }

                    try {
                        if ($request->hasFile('written_reprimand_file')) {
                        try {
                            $files = is_array($request->file('written_reprimand_file'))
                                ? $request->file('written_reprimand_file')
                                : [$request->file('written_reprimand_file')];

                         foreach ($files as $index => $file) { 
                            $originalName = $file->getClientOriginalName();
                            $mimeType     = $file->getClientMimeType();
                            $fileSize     = $file->getSize();   
                            $fileName = time().'_'.$index.'_'.$originalName;
                            $file->move(public_path('storage/violation_reports'), $fileName); 
                            $filePath = 'violation_reports/'.$fileName; 
                            ViolationAttachment::create([
                                'violation_id'   => $violation->id,
                                'file_name'      => $originalName,
                                'file_path'      => $filePath,
                                'file_type'      => $mimeType,
                                'file_size'      => $fileSize,
                                'attachment_type' => 'written_reprimand_file',
                                'uploaded_by'    => $authUser->id,
                            ]);
                        }
                        } catch (\Exception $e) {
                            Log::error('Verbal Reprimand file upload failed', [
                                'violation_id' => $violation->id,
                                'error' => $e->getMessage(),
                            ]);
                            throw $e;
                        }
                    }
                    } catch (\Exception $e) {
                        Log::error('Written Reprimand file upload failed', [
                            'violation_id' => $violation->id,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                    break; 

                case 'Suspension':
                    try {
                         
                        $days = Carbon::parse($request->suspension_start_date)
                            ->diffInDays(Carbon::parse($request->suspension_end_date)) + 1;

                        $violation->update([ 
                            'suspension_start_date' => $request->suspension_start_date,
                            'suspension_end_date' => $request->suspension_end_date,
                            'suspension_days' => $days,
                            'implemented_by' => $authUser->id,
                            'implementation_remarks' => $request->implementation_remarks,
                            'status' => 'implemented',
                        ]);

                        EmploymentDetail::where('user_id', $violation->user_id)
                            ->update(['employment_state' => 'Suspended']);

                        $user->notify(
                            new UserNotification(
                                "HR has implemented a suspension for you from {$request->suspension_start_date} to {$request->suspension_end_date} ({$days} day(s))."
                            )
                        ); 

                    } catch (\Exception $e) {
                        Log::error('Suspension implementation failed', [
                            'violation_id' => $violation->id,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                    break; 

                case 'Termination':
                    try {
                        $violation->update([
                            'termination_date' => $request->termination_date,
                            'implementation_remarks' => $request->implementation_remarks,
                            'implemented_by' => $authUser->id,
                            'status' => 'implemented',
                        ]);

                        EmploymentDetail::where('user_id', $violation->user_id)
                            ->update(['employment_state' => 'Terminated']);

                        $user->notify(
                            new UserNotification(
                                "HR has terminated your employment effective from {$request->termination_date}. Please contact HR if you have any questions regarding this action."
                            )
                        ); 

                    } catch (\Exception $e) {
                        Log::error('Termination implementation failed', [
                            'violation_id' => $violation->id,
                            'error' => $e->getMessage(),
                        ]);
                        throw $e;
                    }
                    break;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Violation implemented successfully.',
                'data' => $violation
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::critical('Violation implementation failed', [
                'violation_id' => $id,
                'violation_type' => $violation->violationType->name ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $authUser->id ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error implementing violation.'
            ], 500);
        }
    }


    public function markReturnToWork(Request $request, $id)
    {
        $authUser = $this->authUser();

        try {
            DB::beginTransaction();

            $violation =Violation::findOrFail($id);
            $violation->update([
                'return_to_work_at' => now(),
                'status' => 'completed',
                'remarks' => 'Employee returned to work after serving violation.',
            ]);

            // Update employee's employment_state back to 'Active'
            EmploymentDetail::where('user_id', $violation->user_id)
                ->update(['employment_state' => 'Active']);

           ViolationAction::create([
                'violation_id' => $violation->id,
                'action_type' => 'return_to_work',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => 'Employee returned to office. Case closed.',
            ]);

            if ($violation->user_id) { 
                $user = User::find($violation->user_id); 
                if ($user) {
                    $user->notify(
                        new UserNotification(
                             "Your violation has been completed. You have been marked as returned to work."
                        )
                    );
                }
            }  

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Case closed. Employee returned to work.',
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing violation case: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error closing violation case.'
            ], 500);
        }
    }
    

    public function processFinalPay(Request $request)
     {

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(24);
        $tenantId = $authUser->tenant_id ?? null;
        $payrollController = new PayrollController();


        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
        }

        $data = $request->validate([
            'user_id'    => 'required', 
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);
 
        $pagibigOption = $request->input('pagibig_option');
        $sssOption = $request->input('sss_option');
        $philhealthOption = $request->input('philhealth_option');
        $cuttoffOption = $request->input('cutoff_period');
        $payrollType = $request->input('payroll_type', 'normal_payroll');
        $payrollPeriod = $request->input('payroll_period', null);
        $paymentDate = $request->input('payment_date', now()->toDateString());


        if ($payrollType === 'normal_payroll') {
            $attendances = $payrollController->getAttendances($tenantId, $data);
            $overtimes = $payrollController->getOvertime($tenantId, $data);
            $totals = $payrollController->sumMinutes($tenantId, $data);
            $salaryData = $payrollController->getSalaryData($data['user_id']);
            $deductions = $payrollController->calculateDeductions($data['user_id'], $totals, $salaryData);
            $holidayInfo = $payrollController->calculateHolidayPay($attendances, $data, $salaryData);
            $nightDiffInfo = $payrollController->calculateNightDifferential($data['user_id'], $data, $salaryData);
            $overtimePay = $payrollController->calculateOvertimePay($data['user_id'], $data, $salaryData);
            $overtimeNightDiffPay = $payrollController->calculateOvertimeNightDiffPay($data['user_id'], $data, $salaryData);
            $userEarnings = $payrollController->calculateEarnings($data['user_id'], $data, $salaryData);
            $userAllowances = $payrollController->calculateAllowance($data['user_id'], $data, $salaryData);
            $userDeductions = $payrollController->calculateDeduction($data['user_id'], $data, $salaryData);
            $basicPay = $payrollController->calculateBasicPay($data['user_id'], $data, $salaryData);
            $grossPay = $payrollController->calculateGrossPay($data['user_id'], $data, $salaryData);
            $sssContributions = $payrollController->calculateSSSContribution($data['user_id'], $data, $salaryData, $sssOption, $cuttoffOption);
            $philhealthContributions = $payrollController->calculatePhilhealthContribution($data['user_id'], $data, $salaryData, $philhealthOption, $cuttoffOption);
            $pagibigContributions = $payrollController->calculatePagibigContribution($data['user_id'], $data, $salaryData, $pagibigOption);
            $withholdingTax = $payrollController->calculateWithholdingTax($data['user_id'], $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cuttoffOption);
            $leavePay = $payrollController->calculateLeavePay($data['user_id'], $data, $salaryData);
            $deminimisBenefits = $payrollController->calculateUserDeminimis($data['user_id'], $data, $salaryData);
            $totalDeductions = $payrollController->calculateTotalDeductions($data['user_id'], $data, $salaryData, $pagibigOption, $sssOption, $philhealthOption, $cuttoffOption);
            $totalEarnings = $payrollController->calculateTotalEarnings($data['user_id'], $data, $salaryData);
            $netPay = $payrollController->calculateNetPay($data['user_id'], $basicPay, $totalEarnings, $totalDeductions);
            $thirteenthMonth = $payrollController->calculateThirteenthMonthPay($data['user_id'], $data, $salaryData);
            $salaryBond = $payrollController->calculateSalaryBondDeduction($data['user_id'], $data, $salaryData);

            foreach ($data['user_id'] as $userId) {
                $payroll = Payroll::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                        'payroll_period_start' => $data['start_date'],
                        'payroll_period_end' => $data['end_date'],
                        'payroll_type' => $payrollType,
                    ],
                    [
                        'payroll_period' => $payrollPeriod,
                        'total_worked_minutes' => $totals['work'][$userId] ?? 0,
                        'total_late_minutes' => $totals['late'][$userId] ?? 0,
                        'total_undertime_minutes' => $totals['undertime'][$userId] ?? 0,
                        'total_overtime_minutes' => $overtimePay[$userId]['total_ot_minutes'] ?? 0,
                        'total_night_differential_minutes' => $totals['night_diff'][$userId] ?? 0,
                        'total_overtime_night_diff_minutes' => $overtimeNightDiffPay[$userId]['total_night_diff_minutes'] ?? 0,
                        'total_worked_days' => $totals['work_days'][$userId] ?? 0,
                        'total_absent_days' => $totals['absent'][$userId] ?? 0,

                        // Pay breakdown
                        'holiday_pay' => $holidayInfo[$userId]['holiday_pay_amount'] ?? 0,
                        'leave_pay' => $leavePay[$userId]['leave_pay_amount'] ?? 0,
                        'overtime_pay' => ($overtimePay[$userId]['ordinary_pay'] ?? 0) + ($overtimePay[$userId]['holiday_pay'] ?? 0),

                        'night_differential_pay' => ($nightDiffInfo[$userId]['ordinary_pay'] ?? 0) + ($nightDiffInfo[$userId]['rest_day_pay'] ?? 0)
                            + ($nightDiffInfo[$userId]['holiday_pay'] ?? 0)
                            + ($nightDiffInfo[$userId]['holiday_rest_day_pay'] ?? 0),

                        'overtime_night_diff_pay' => ($overtimeNightDiffPay[$userId]['ordinary_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['rest_day_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['holiday_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['holiday_rest_day_pay'] ?? 0) +
                            ($overtimeNightDiffPay[$userId]['holiday_rest_day_pay'] ?? 0),

                        'late_deduction' => $deductions['lateDeductions'][$userId] ?? 0,
                        'overtime_restday_pay' => ($overtimePay[$userId]['rest_day_pay'] ?? 0) + ($overtimePay[$userId]['holiday_rest_day_pay'] ?? 0),
                        'undertime_deduction' => $deductions['undertimeDeductions'][$userId] ?? 0,
                        'absent_deduction' => $deductions['absentDeductions'][$userId] ?? 0,
                        'earnings' => isset($userEarnings[$userId]['earning_details']) ? json_encode($userEarnings[$userId]['earning_details']) : null,
                        'total_earnings' => $totalEarnings[$userId]['total_earnings'] ?? 0,
                        'allowance' => isset($userAllowances[$userId]['allowance_details']) ? json_encode($userAllowances[$userId]['allowance_details']) : null,
                        'taxable_income' => 0,

                        // De Minimis
                        'deminimis' => isset($deminimisBenefits[$userId]['details']) ? json_encode($deminimisBenefits[$userId]['details']) : null,

                        // Deductions
                        'sss_contribution' => $sssContributions[$userId]['employee_total'] ?? 0,
                        'philhealth_contribution' => $philhealthContributions[$userId]['employee_total'] ?? 0,
                        'pagibig_contribution' => $pagibigContributions[$userId]['employee_total'] ?? 0,
                        'withholding_tax' => $withholdingTax[$userId]['withholding_tax'] ?? 0,
                        'salary_bond' => $salaryBond[$userId]['total_salary_bond_deduction'] ?? 0,
                        'loan_deductions' => null,
                        'deductions' => isset($userDeductions[$userId]['deduction_details']) ? json_encode($userDeductions[$userId]['deduction_details']) : null,
                        'total_deductions' => ($totalDeductions[$userId]['total_deductions'] ?? 0) + ($salaryBond[$userId]['total_salary_bond_deduction'] ?? 0),

                        // Salary Breakdown
                        'basic_pay' => $basicPay[$userId]['basic_pay'] ?? 0,
                        'gross_pay' => $grossPay[$userId]['gross_pay'] ?? 0,
                        'net_salary' => ($netPay[$userId]['net_pay'] ?? 0) - ($salaryBond[$userId]['total_salary_bond_deduction'] ?? 0),

                        // 13th month
                        'thirteenth_month_pay' => $thirteenthMonth[$userId]['thirteenth_month'] ?? 0,

                        // Payment Info
                        'payment_date' => $paymentDate,
                        'processor_type' => Auth::user() ? get_class(Auth::user()) : null,
                        'processor_id' => Auth::id(),
                        'status' => 'Pending',
                        'remarks' => null,
                    ]
                );
            }

            UserEarning::where('user_id', $data['user_id'][0])
                ->where('frequency', 'one_time')
                ->where('status', 'terminated')
                ->whereBetween('effective_start_date', [
                    Carbon::parse($data['start_date'])->startOfDay(),
                    Carbon::parse($data['end_date'])->endOfDay()
                ])
                ->update(['status' => 'completed']);   

            return response()->json([
                'attendances'       => $attendances,
                'totals'            => $totals['work'],
                'late_totals'       => $totals['late'],
                'undertime_totals'  => $totals['undertime'],
                'night_diff_totals' => $totals['night_diff'],
                'absent_days'       => $totals['absent'],
                'work_days'         => $totals['work_days'],
                'deductions'        => $deductions,
                'holiday'           => $holidayInfo,
                'night_diff_pay'    => $nightDiffInfo,
                'overtimes'         => $overtimes,
                'message'           => 'Last Pay processed successfully.',
            ]);
        } else {
            return response()->json([
                'message' => 'Payroll type not supported yet.',
            ], 422);
        }
    }

}
