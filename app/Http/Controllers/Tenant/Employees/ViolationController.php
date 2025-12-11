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
                    'violation_start_date' => $violation->violation_start_date
                        ? Carbon::parse($violation->violation_start_date)->format('M d, Y')
                        : null,
                    'violation_end_date' => $violation->violation_end_date
                        ? Carbon::parse($violation->violation_end_date)->format('M d, Y')
                        : null,
                    'violation_days' => $violation->violation_days,
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
                if ($user) {
                    $user->notify(
                        new UserNotification(
                            "A Disciplinary Action Memo has been uploaded for your violation."
                        )
                    );
                }
            }
 
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'DAM issued successfully with violation type: ' . str_replace('_', ' ', $request->violation_type_id) . '.',
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
        $request->validate([
            'violation_start_date' => 'required|date',
            'violation_end_date' => 'required|date|after_or_equal:violation_start_date',
            'implementation_remarks' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $violation =Violation::findOrFail($id);
            $days = Carbon::parse($request->violation_start_date)
                ->diffInDays(Carbon::parse($request->violation_end_date)) + 1;

            $violation->update([
                'violation_type' => 'without_pay',
                'violation_start_date' => $request->violation_start_date,
                'violation_end_date' => $request->violation_end_date,
                'violation_days' => $days,
                'implemented_by' => $authUser->id,
                'implementation_remarks' => $request->implementation_remarks,
                'status' => 'suspended',
            ]);

            // Update employee's employment_state to 'Suspended'
            EmploymentDetail::where('user_id', $violation->user_id)
                ->update(['employment_state' => 'Suspended']);

           ViolationAction::create([
                'violation_id' => $violation->id,
                'action_type' => 'violation_implemented',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => 'Violation implemented without pay.',
                'remarks' => 'Duration: ' . $days . ' days'
            ]);
                
            if ($violation->user_id) { 
                $user = User::find($violation->user_id); 
                if ($user) {
                    $user->notify(
                        new UserNotification(
                             "Your violation #{$violation->id} has been implemented from {$request->violation_start_date} to {$request->violation_end_date} ({$days} days)."
                        )
                    );
                }
            } 


            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Violation implemented successfully.',
                'data' => $violation
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error implementing violation: ' . $e->getMessage());

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
}
