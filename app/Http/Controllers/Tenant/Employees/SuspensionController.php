<?php

namespace App\Http\Controllers\Tenant\Employees;

use App\Helpers\PermissionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DataAccessController;
use App\Models\Suspension;
use App\Models\SuspensionAction;
use App\Models\EmploymentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuspensionController extends Controller
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
        $query = \App\Models\User::whereHas('employmentDetail', function ($q) use ($branchIds) {
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
            $suspension = Suspension::with([
                'employee.employmentDetail.branch',
                'employee.employmentDetail.department',
                'employee.employmentDetail.designation',
                'employee.personalInformation',
                'actions'
            ])->findOrFail($id);

            // Get employee reply action
            $employeeReply = $suspension->actions
                ->where('action_type', 'employee_reply')
                ->first();

            return response()->json([
                'status' => 'success',
                'suspension' => [
                    'id' => $suspension->id,
                    'employee_name' => $suspension->employee->personalInformation 
                        ? trim($suspension->employee->personalInformation->first_name . ' ' . 
                               ($suspension->employee->personalInformation->middle_name ?? '') . ' ' . 
                               $suspension->employee->personalInformation->last_name)
                        : 'N/A',
                    'employee_id' => $suspension->employee->employmentDetail->employee_id ?? 'N/A',
                    'branch' => $suspension->employee->employmentDetail->branch->name ?? 'N/A',
                    'department' => $suspension->employee->employmentDetail->department->department_name ?? 'N/A',
                    'designation' => $suspension->employee->employmentDetail->designation->designation_name ?? 'N/A',
                    'status' => $suspension->status,
                    'suspension_type' => $suspension->suspension_type,
                    'suspension_start_date' => $suspension->suspension_start_date 
                        ? Carbon::parse($suspension->suspension_start_date)->format('M d, Y') 
                        : null,
                    'suspension_end_date' => $suspension->suspension_end_date 
                        ? Carbon::parse($suspension->suspension_end_date)->format('M d, Y') 
                        : null,
                    'suspension_days' => $suspension->suspension_days,
                    'offense_details' => $suspension->offense_details,
                    'disciplinary_action' => $suspension->disciplinary_action,
                    'remarks' => $suspension->remarks,
                    'investigation_notes' => $suspension->investigation_notes,
                    'implementation_remarks' => $suspension->implementation_remarks,
                    'information_report_file' => $suspension->information_report_file,
                    'nowe_file' => $suspension->nowe_file,
                    'dam_file' => $suspension->dam_file,
                    'created_at' => $suspension->created_at ? $suspension->created_at->format('M d, Y') : null,
                    'employee_reply' => $employeeReply ? [
                        'description' => $employeeReply->description,
                        'file_path' => $employeeReply->file_path,
                        'action_date' => $employeeReply->action_date 
                            ? Carbon::parse($employeeReply->action_date)->format('M d, Y') 
                            : null,
                    ] : null,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching suspension details: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Suspension record not found.'
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
        ]);

        try {
            DB::beginTransaction();

            $suspension = Suspension::findOrFail($id);
            
            $updateData = [
                'offense_details' => $request->offense_details,
                'disciplinary_action' => $request->disciplinary_action,
                'remarks' => $request->remarks,
            ];

            // Handle file upload if provided
            if ($request->hasFile('information_report_file')) {
                $file = $request->file('information_report_file');
                $filename = 'suspension_report_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('suspensions/reports', $filename, 'public');
                $updateData['information_report_file'] = $path;
            }

            $suspension->update($updateData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Suspension updated successfully.',
                'data' => $suspension
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating suspension: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error updating suspension.'
            ], 500);
        }
    }



    public function adminSuspensionEmployeeListIndex(Request $request)
    {
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(60);

        $status = $request->input('status'); // optional filter (e.g. pending, implemented, etc.)

        $dataAccessController = new DataAccessController();

        // branches for select options (use accessData if provided, otherwise load all)
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        // Prepare a separate query/collection for employee select options so we don't interfere
        // with the main employees query below. Ensure we only return active employees.
        $employeeOptionsQuery = clone $accessData['employees'];

        $employeeOptions = $employeeOptionsQuery
            ->whereHas('employmentDetail', function ($query) {
                $query->where('status', 1); // active employees only
            })
            ->with(['personalInformation', 'employmentDetail'])
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => trim(($emp->personalInformation->first_name ?? '') . ' ' . ($emp->personalInformation->last_name ?? '')),
                    'employee_id' => $emp->employmentDetail->employee_id ?? null,
                ];
            })
            ->values();

        // Fetch employees who have suspensions
        $employees = $accessData['employees']
            ->whereHas('suspensions', function ($query) use ($status) {
                if ($status) {
                    $query->where('status', $status);
                }
            })
            ->with([
                'personalInformation',
                'employmentDetail.branch',
                'employmentDetail.department',
                'employmentDetail.designation',
                'suspensions' => function ($q) use ($status) {
                    if ($status) {
                        $q->where('status', $status);
                    }
                    $q->latest();
                },
            ])
            ->whereHas('employmentDetail', function ($query) {
                $query->where('status', 1); // active employees only
            })
            ->latest()
            ->get();

        // JSON response for your fetch() JS in the Blade
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'employees' => $employees->map(function ($emp) {
                    $latestSuspension = $emp->suspensions->first();
                    return [
                        'id' => $emp->id,
                        'name' => ($emp->personalInformation->first_name ?? '') . ' ' . ($emp->personalInformation->last_name ?? ''),
                        'employee_id' => $emp->employmentDetail->employee_id ?? null,
                        'branch' => $emp->employmentDetail->branch->name ?? null,
                        'department' => $emp->employmentDetail->department->department_name ?? null,
                        'designation' => $emp->employmentDetail->designation->designation_name ?? null,
                        'suspension_id' => $latestSuspension ? $latestSuspension->id : null,
                        'suspension_status' => $latestSuspension ? $latestSuspension->status : null,
                        'suspension_type' => $latestSuspension ? $latestSuspension->suspension_type : null,
                        'suspension_start' => $latestSuspension ? $latestSuspension->suspension_start_date : null,
                        'suspension_end' => $latestSuspension ? $latestSuspension->suspension_end_date : null,
                        'dam_file' => $latestSuspension ? $latestSuspension->dam_file : null,
                    ];
                }),
                'branches' => $branches,
                'employee_options' => $employeeOptions,
            ]);
        }

        // For non-AJAX view load
        return view('tenant.suspension.suspension-admin', [
            'permission' => $permission,
            'employees' => $employees,
            'branches' => $branches,
            'employeeOptions' => $employeeOptions,
        ]);
    }

    public function suspensionEmployeeListIndex(Request $request)
    {
        $authUser = $this->authUser(); // Logged-in user
        $permission = PermissionHelper::get(60);

        $status = $request->input('status'); // Optional filter: pending, implemented, etc.

        // Fetch suspensions for the authenticated user
        $suspensions = Suspension::with([
            'employee.personalInformation',
            'employee.employmentDetail.branch',
            'employee.employmentDetail.department',
            'employee.employmentDetail.designation',
        ])
            ->where('user_id', $authUser->id)
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->get();

        // If frontend calls via AJAX or fetch()
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'suspensions' => $suspensions->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'offense_details' => $s->offense_details,
                        'status' => $s->status,
                        'suspension_type' => $s->suspension_type,
                        'start_date' => $s->suspension_start_date,
                        'end_date' => $s->suspension_end_date,
                        'information_report_file' => $s->information_report_file,
                        'employee_name' => optional($s->employee->personalInformation)->first_name . ' ' . optional($s->employee->personalInformation)->last_name,
                        'employee_id' => optional($s->employee->employmentDetail)->employee_id,
                        'branch' => optional($s->employee->employmentDetail->branch)->name,
                        'department' => optional($s->employee->employmentDetail->department)->department_name,
                        'designation' => optional($s->employee->employmentDetail->designation)->designation_name,
                    ];
                }),
            ]);
        }

        // For non-AJAX view load
        return view('tenant.suspension.suspension-employee', [
            'permission' => $permission,
            'suspensions' => $suspensions,
        ]);
    }




    public function fileSuspensionReport(Request $request)
    {
        $authUser = $this->authUser();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'offense_details' => 'required|string|max:1000',
            'information_report_file' => 'nullable|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $filePath = null;
            if ($request->hasFile('information_report_file')) {
                $file = $request->file('information_report_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('storage/suspension_reports'), $fileName);
                $filePath = "suspension_reports/{$fileName}";
            }

            $suspension = Suspension::create([
                'user_id' => $request->user_id,
                'offense_details' => $request->offense_details,
                'information_report_file' => $filePath,
                'status' => 'pending',
            ]);

            SuspensionAction::create([
                'suspension_id' => $suspension->id,
                'action_type' => 'report_received',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => 'Information report received for offense.',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Suspension case successfully filed.',
                'data' => $suspension
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error filing suspension case: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error filing suspension case. Please try again.'
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

            $suspension = Suspension::findOrFail($id);
            $file = $request->file('nowe_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/suspension_nowe'), $fileName);
            $filePath = 'suspension_nowe/' . $fileName;

            $suspension->update([
                'status' => 'awaiting_reply',
                'remarks' => 'NOWE issued on ' . now()->format('F d, Y'),
            ]);

            SuspensionAction::create([
                'suspension_id' => $suspension->id,
                'action_type' => 'nowe_issued',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'file_path' => $filePath,
                'description' => 'Notice of Written Explanation issued to employee.'
            ]);

            DB::commit();

            Log::info('NOWE issued successfully', [
                'suspension_id' => $suspension->id,
                'user_id' => $suspension->user_id,
                'action_by' => $authUser->id,
                'file_path' => $filePath
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'NOWE successfully issued.',
                'data' => $suspension
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error issuing NOWE: ' . $e->getMessage(), [
                'suspension_id' => $id,
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

            $suspension = Suspension::findOrFail($id);
            $file = $request->file('reply_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/suspension_replies'), $fileName);

            $suspension->update([
                'status' => 'under_investigation',
                'remarks' => 'Employee reply received.',
            ]);

            SuspensionAction::create([
                'suspension_id' => $suspension->id,
                'action_type' => 'employee_reply',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'file_path' => 'suspension_replies/' . $fileName,
                'description' => 'Employee reply received and recorded.'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Employee reply successfully received.',
                'data' => $suspension
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

            $suspension = Suspension::findOrFail($id);
            $suspension->update([
                'status' => 'for_dam_issuance',
                'remarks' => 'Investigation conducted and case assessed.',
            ]);

            SuspensionAction::create([
                'suspension_id' => $suspension->id,
                'action_type' => 'investigation',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => $request->investigation_notes,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Investigation recorded successfully.',
                'data' => $suspension
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
            'suspension_type' => 'required|in:with_pay,without_pay',
        ]);

        try {
            DB::beginTransaction();

            $suspension = Suspension::find($id);
            if (!$suspension) {
                DB::rollBack();
                Log::warning('Suspension not found while issuing DAM', ['suspension_id' => $id, 'action_by' => $authUser->id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Suspension case not found.'
                ], 404);
            }

            $file = $request->file('dam_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/suspension_dam'), $fileName);
            $filePath = "suspension_dam/{$fileName}";

            $suspension->update([
                'dam_file' => $filePath,
                'dam_issued_at' => now(),
                'suspension_type' => $request->suspension_type,
                'status' => 'suspended',
            ]);

            SuspensionAction::create([
                'suspension_id' => $suspension->id,
                'action_type' => 'dam_issued',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'file_path' => $filePath,
                'description' => 'Disciplinary Action Memo issued to employee (' . str_replace('_', ' ', $request->suspension_type) . ').',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'DAM issued successfully with suspension type: ' . str_replace('_', ' ', $request->suspension_type) . '.',
                'data' => $suspension
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

    public function implementSuspension(Request $request, $id)
    {
        $authUser = $this->authUser();
        $request->validate([
            'suspension_start_date' => 'required|date',
            'suspension_end_date' => 'required|date|after_or_equal:suspension_start_date',
            'implementation_remarks' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $suspension = Suspension::findOrFail($id);
            $days = Carbon::parse($request->suspension_start_date)
                ->diffInDays(Carbon::parse($request->suspension_end_date)) + 1;

            $suspension->update([
                'suspension_type' => 'without_pay',
                'suspension_start_date' => $request->suspension_start_date,
                'suspension_end_date' => $request->suspension_end_date,
                'suspension_days' => $days,
                'implemented_by' => $authUser->id,
                'implementation_remarks' => $request->implementation_remarks,
                'status' => 'suspended',
            ]);

            // Update employee's employment_state to 'Suspended'
            EmploymentDetail::where('user_id', $suspension->user_id)
                ->update(['employment_state' => 'Suspended']);

            SuspensionAction::create([
                'suspension_id' => $suspension->id,
                'action_type' => 'suspension_implemented',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => 'Suspension implemented without pay.',
                'remarks' => 'Duration: ' . $days . ' days'
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Suspension implemented successfully.',
                'data' => $suspension
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error implementing suspension: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error implementing suspension.'
            ], 500);
        }
    }

    public function markReturnToWork(Request $request, $id)
    {
        $authUser = $this->authUser();

        try {
            DB::beginTransaction();

            $suspension = Suspension::findOrFail($id);
            $suspension->update([
                'return_to_work_at' => now(),
                'status' => 'completed',
                'remarks' => 'Employee returned to work after serving suspension.',
            ]);

            // Update employee's employment_state back to 'Active'
            EmploymentDetail::where('user_id', $suspension->user_id)
                ->update(['employment_state' => 'Active']);

            SuspensionAction::create([
                'suspension_id' => $suspension->id,
                'action_type' => 'return_to_work',
                'action_by' => $authUser->id,
                'action_date' => now(),
                'description' => 'Employee returned to office. Case closed.',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Case closed. Employee returned to work.',
                'data' => $suspension
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing suspension case: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error closing suspension case.'
            ], 500);
        }
    }
}
