<?php

namespace App\Http\Controllers\Tenant\Employees;

use tenant;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\SubModule;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Resignation;
use Illuminate\Http\Request;
use App\Models\AssetsDetails;
use App\Models\ResignationHR;
use App\Models\UserPermission;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\AssetsDetailsHistory;
use App\Models\AssetsDetailsRemarks;
use Illuminate\Support\Facades\Auth;
use App\Models\ResignationAttachment;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use App\Models\ResignationAttachmentRemarks;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\DataAccessController;

class ResignationController extends Controller
{ 
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }

        return Auth::user();
    }
    public function resignationEmployeeIndex(Request $request)
    {  
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(58);
        $resignations = Resignation::with(['personalInformation', 'hrResignationAttachments'])
        ->where('user_id', $authUser->id)
        ->latest('id') 
        ->get(); 
    
        return view('tenant.resignation.resignation-employee',['permission' => $permission, 'resignations'=> $resignations]);
    }   
    
    // submit resignation - employee uploading of resignation letter and reason
    public function submitResignation(Request $request)
    {
        $authUser = $this->authUser();

        try {
            DB::beginTransaction();
            
            $pendingResignations = Resignation::where('user_id', $authUser->id)
            ->where(function ($query) {
                $query->where('status', 0)
                    ->orWhere(function ($q) {
                        $q->where('status', 1)
                        ->whereNull('accepted_by');
                    })
                    ->orWhere(function ($q) {
                        $q->where('status', 1)
                        ->whereNotNull('accepted_by')
                        ->where('cleared_status', 0)
                        ->where('resignation_date', '>', Carbon::now());
                    });
            })
            ->exists(); 

            if ($pendingResignations) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending resignation request.',
                ], 400);
            }


            $request->validate([
                'resignation_letter' => 'required|mimes:pdf,doc,docx|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:5120',
                'resignation_reason' => 'nullable|string|max:500',
            ]);

            $fileName = time() . '_' . $request->file('resignation_letter')->getClientOriginalName();
            $request->file('resignation_letter')->move(public_path('storage/resignation_letters'), $fileName);

            Resignation::create([
                'date_filed'       => Carbon::now(),
                'user_id'          => $authUser->id,
                'resignation_file' => 'resignation_letters/' . $fileName,
                'reason'           => $request->resignation_reason,
                'status'           => 0,
            ]);

            DB::commit(); 
            
            $userEmploymentDetail = EmploymentDetail::where('user_id', $authUser->id)->first();

            $reportingTo_userid = $userEmploymentDetail->reporting_to;
            $deptHead_userid = $userEmploymentDetail->department->head_of_department;

            $firstName = $authUser->personalInformation->first_name ?? '';
            $lastName = $authUser->personalInformation->last_name?? '';

            $employeeName = $firstName . ' ' . $lastName; 

            if ($reportingTo_userid) {
                if ($reportingTo = User::find($reportingTo_userid)) {
                    $reportingTo->notify(
                        new UserNotification("$employeeName has filed a resignation request. Pending your approval.")
                    );
                }
            }

            if ($deptHead_userid) {
                if ($deptHead = User::find($deptHead_userid)) {
                    $deptHead->notify(
                        new UserNotification("$employeeName has filed a resignation request. Pending your approval.")
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Resignation submitted successfully.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {  
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Resignation submission failed: ' . $e->getMessage(), [
                'user_id' => $authUser->id ?? null,
                'trace'   => $e->getTraceAsString(),
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while submitting your resignation. Please try again.',
            ], 500);
        }
    }

    // edit resignation 
    public function update(Request $request, $id)
    {
        $resignation = Resignation::findOrFail($id);

        $request->validate([
            'resignation_letter' => 'nullable|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:5120',
            'resignation_reason' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('resignation_letter')) {
            $file = $request->file('resignation_letter');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('resignations', $fileName, 'public');
            $resignation->resignation_file = 'resignations/' . $fileName;
        }

        $resignation->reason = $request->input('resignation_reason');
        $resignation->save();

        return response()->json(['message' => 'Resignation updated successfully!']);
    }


    // delete resignation

    public function destroy($id)
    {    

        try {
            $resignation = Resignation::findOrFail($id);

            $resignation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Resignation deleted successfully.'
            ]);

        } catch (\Exception $e) {
        
            Log::error('Resignation deletion failed', [
                'resignation_id' => $id,
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString() 
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete resignation. Please contact support.'
            ], 500);
        }
    }
    //employee upload attachments 
    public function uploadAttachments(Request $request, $id)
    {  
        $authUser = $this->authUser();

        $request->validate([
            'attachments.*' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:5120'
        ]);

        $resignation = Resignation::findOrFail($id);

        foreach ($request->file('attachments') as $file) {
            
            
            $originalName = $file->getClientOriginalName(); 
            $cleanName = preg_replace('/[^A-Za-z0-9_\-\. ]/', '',     $originalName); 
            $filename = time() . '_' . $cleanName;

            $file->move(public_path('storage/resignation_attachments'), $filename);

            ResignationAttachment::create([
                'resignation_id' => $resignation->id,
                'uploaded_by' => $authUser->id,
                'uploader_role' => 'employee',
                'filename' => $filename,
                'filepath' => 'storage/resignation_attachments/' . $filename,
                'filetype' => $file->getClientOriginalExtension(),
            ]); 
        }

         $myUploads = ResignationAttachment::where('resignation_id', $resignation->id)
        ->where('uploader_role', 'employee')
        ->get();

        $html = view('tenant.resignation.resignation-employee-attachments-partials', compact('myUploads'))->render();

        $submodule_resignation_hr_ids = SubModule::whereIn('sub_module_name', [
                'Resignation HR',
                'Resignation Settings'
            ])->pluck('id')->toArray();

        if (!empty($submodule_resignation_hr_ids)) {
            $userIds = UserPermission::where(function ($query) use ($submodule_resignation_hr_ids) {
                foreach ($submodule_resignation_hr_ids as $id) { 
                    $query->orWhere('user_permission_ids', 'like', "%{$id}-%");
                }
            })
            ->pluck('user_id')
            ->unique();

            $users = User::whereIn('id', $userIds)->get();

            if ($users->isNotEmpty()) {
             
                $requestorName = trim(
                    ($resignation->personalInformation->first_name ?? '') . ' ' .
                    ($resignation->personalInformation->last_name ?? '')
                );

                $message = "{$requestorName} has uploaded new clearance attachments for their resignation. Please review and validate."; 
                Notification::send($users, new UserNotification($message));
            }     
        }

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }
    // employee attachment remarks

     public function storeEmployeeAttachmentRemarks(Request $request, $id)
     {     
        $authUser = $this->authUser();
        ResignationAttachmentRemarks::create([
            'resignation_attachment_id' => $id,
            'remarks_from'              => $authUser->id,
            'remarks_from_role'         => 'Employee',
            'remarks'                   => $request->remarks,
        ]); 

        $remarks = ResignationAttachmentRemarks::where('resignation_attachment_id', $id)->get();

        $html = view('tenant.resignation.resignation-employee-attachment-remarks', compact('remarks'))->render();
        
        return response()->json(['html' => $html]);
    }



    // employee return assets
    
    public function saveEmployeeAssets(Request $request)
    {
        $authUser = $this->authUser(); 
        $conditions = $request->input('condition', []);
        $statuses = $request->input('status', []);
        $hasChanges = false;  

        foreach ($conditions as $assetId => $condition) {
            $status = $statuses[$assetId] ?? null;
            $currentAsset = AssetsDetails::find($assetId);

            if (!$currentAsset) {
                continue;
            }

            $previousCondition = $currentAsset->asset_condition;
            $previousStatus = $currentAsset->status;
    
            if ($previousCondition === $condition && $previousStatus === $status) {
                continue;
            }
    
            $currentAsset->asset_condition = $condition;
            $currentAsset->status = $status;
            $currentAsset->save();
    
            $assetDetailsHistory = new AssetsDetailsHistory();
            $assetDetailsHistory->asset_detail_id = $currentAsset->id;
            $assetDetailsHistory->item_no = $currentAsset->order_no;
            $assetDetailsHistory->condition = $currentAsset->asset_condition;
            $assetDetailsHistory->condition_remarks = null;
            $assetDetailsHistory->status = $currentAsset->status;
            $assetDetailsHistory->deployed_to = $currentAsset->deployed_to;
            $assetDetailsHistory->deployed_date = $currentAsset->deployed_date;
            $assetDetailsHistory->process = 'Updated asset condition/status';
            $assetDetailsHistory->updated_by = $authUser->id ?? null;
            $assetDetailsHistory->updated_at = now();
            $assetDetailsHistory->created_by = $currentAsset->created_by;
            $assetDetailsHistory->created_at = $currentAsset->created_at;
            $assetDetailsHistory->save();

            $hasChanges = true;  
        }
    
        if ($hasChanges) {
            $submodule_resignation_hr_ids = SubModule::whereIn('sub_module_name', [
                'Resignation HR',
                'Resignation Settings'
            ])->pluck('id')->toArray();

            if (!empty($submodule_resignation_hr_ids)) {
                $userIds = UserPermission::where(function ($query) use ($submodule_resignation_hr_ids) {
                    foreach ($submodule_resignation_hr_ids as $id) { 
                        $query->orWhere('user_permission_ids', 'like', "%{$id}-%");
                    }
                })
                ->pluck('user_id')
                ->unique();

                $users = User::whereIn('id', $userIds)->get();

                if ($users->isNotEmpty()) { 
                    $requestorName = trim(
                        ($authUser->personalInformation->first_name ?? '') . ' ' .
                        ($authUser->personalInformation->last_name ?? '')
                    );

                    $message = "{$requestorName} has updated the asset condition and status for their resignation clearance. Please review and validate.";
                    Notification::send($users, new UserNotification($message));
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Assets return form submitted successfully.',
        ]);
    }

    // save employee asset remarks
   public function saveRemark(Request $request)
    {
        $authUser = $this->authUser();

        $request->validate([
            'asset_id' => 'required|integer|exists:assets_details,id',
            'condition_remarks' => 'required|string|max:500',
        ]);

        $asset = AssetsDetails::findOrFail($request->asset_id);

        $asset->remarks()->create([
            'asset_detail_id' => $request->asset_id,
            'asset_holder_id' => $asset->deployed_to,
            'item_no' => $asset->order_no,  
            'remarks_from' => 'Employee',
            'condition_remarks' => $request->condition_remarks,
        ]);
    
        $asset->load('remarks');
    
        $html = view('tenant.resignation.resignation-employee_asset_remarks_list', [
            'asset' => $asset
        ])->render();

        return response()->json([
            'message' => 'Remark saved successfully.',
            'html' => $html,
        ]);
    }



    // resignation admin index
    public function resignationAdminIndex(Request $request)
    {   
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(22); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments  = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $resignations = Resignation::with([
                'personalInformation',
                'employmentDetail.branch',
                'employmentDetail.department.designations',
            ])
            ->where(function ($query) use ($authUser) { 
                $query->whereHas('employmentDetail.department', function ($q) use ($authUser) {
                    $q->where('head_of_department', $authUser->id);
                }) 
                ->orWhereHas('employmentDetail', function ($q) use ($authUser) {
                    $q->where('reporting_to', $authUser->id);
                });  
            })
            ->get(); 
 

        return view('tenant.resignation.resignation-admin',['permission' => $permission , 'resignations'=> $resignations, 'branches' => $branches, 'departments' => $departments, 'designations' => $designations ]);
    } 

        public function filter(Request $request)
      {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(22);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  =  Resignation::with([
                'personalInformation',
                'employmentDetail.branch',
                'employmentDetail.department.designations',
            ])
            ->where(function ($query) use ($authUser) { 
                $query->whereHas('employmentDetail.department', function ($q) use ($authUser) {
                    $q->where('head_of_department', $authUser->id);
                }) 
                ->orWhereHas('employmentDetail', function ($q) use ($authUser) {
                    $q->where('reporting_to', $authUser->id);
                });  
            });

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('date_filed', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($branch) {
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }
        
        if (!is_null($status)) {
            switch ($status) {
                case 0:
                    $query->where('status', 0);
                    break;

                case 1:
                    $query->where('status', 1)
                        ->whereNull('accepted_by');
                    break;

                case 2:
                    $query->where('status', 2);
                    break;

                case 3:
                    $query->where('status', 1)
                        ->whereNotNull('accepted_by')
                        ->where('cleared_status', 0);
                    break;
                case 4:
                    $query->where('status', 1)
                        ->whereNotNull('accepted_by')
                        ->where('cleared_status', 1)
                        ->where('resignation_date', '>', Carbon::today());
                    break; 
                case 5:
                    $query->where('status', 1)
                        ->whereNotNull('accepted_by')
                        ->where('cleared_status', 1)
                        ->where('resignation_date', '<=', Carbon::today());
                    break;

            }
        }


        $resignations = $query->get();
        
        $html = view('tenant.resignation.resignation-admin-filter', compact('resignations', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }


    
    // head or reporting to approve  
    public function approve(Request $request, $id)
    {  

        $authUser = $this->authUser();

        $request->validate([
            'status_remarks' => 'nullable|string|max:500',
        ]); 

        try {

            DB::beginTransaction();

            $resignation = Resignation::findOrFail($id);
            $resignation->status = 1;  
            $resignation->status_remarks = $request->status_remarks;
            $resignation->status_date = Carbon::now();
            $resignation->save();

            DB::commit();

            $approverName = trim(
                        (Auth::user()->personalInformation->first_name ?? '') . ' ' .
                        (Auth::user()->personalInformation->last_name ?? '')
                    );

            if ($resignation->user) {
                $resignation->user->notify(
                        new UserNotification("{$approverName} approved your resignation request.It is now for HR acceptance.")
                    );
            }
   
           $submodule_resignation_hr_ids = SubModule::whereIn('sub_module_name', [
                'Resignation HR',
                'Resignation Settings'
            ])->pluck('id')->toArray();

            if (!empty($submodule_resignation_hr_ids)) {
                $userIds = UserPermission::where(function ($query) use ($submodule_resignation_hr_ids) {
                    foreach ($submodule_resignation_hr_ids as $id) { 
                        $query->orWhere('user_permission_ids', 'like', "%{$id}-%");
                    }
                })
                ->pluck('user_id')
                ->unique();

                $users = User::whereIn('id', $userIds)->get();

                if ($users->isNotEmpty()) {
                    $approverName = trim(
                        (Auth::user()->personalInformation->first_name ?? '') . ' ' .
                        (Auth::user()->personalInformation->last_name ?? '')
                    );

                    $requestorName = trim(
                        ($resignation->personalInformation->first_name ?? '') . ' ' .
                        ($resignation->personalInformation->last_name ?? '')
                    );

                    $message = "{$approverName} approved the resignation request of {$requestorName}. Pending your acceptance.";
            
                    Notification::send($users, new UserNotification($message));
                }         

            }
            return response()->json([
                'success' => true,
                'message' => 'Resignation has been approved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
    
            Log::error('Error approving resignation', [
                'resignation_id' => $id,
                'user_id' => $authUser->id,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while approving resignation.'
            ], 500);
        }
    }
    


    // head or reporting to reject
    public function reject(Request $request, $id)

    {   
        $authUser = $this->authUser();

        $request->validate([
            'status_remarks' => 'nullable|string|max:500',
        ]); 

        try {
            DB::beginTransaction();

            $resignation = Resignation::findOrFail($id);
            $resignation->status = 2; 
            $resignation->status_remarks = $request->status_remarks; 
            $resignation->status_date = Carbon::now();
            $resignation->save();

            DB::commit();
            $approverName = trim(
                        (Auth::user()->personalInformation->first_name ?? '') . ' ' .
                        (Auth::user()->personalInformation->last_name ?? '')
                    );
            if ($resignation->user) {
                $resignation->user->notify(
                            new UserNotification("{$approverName} rejected your resignation request.")
                        );
            }
  
            return response()->json([
                'success' => true,
                'message' => 'Resignation has been rejected successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error approving resignation', [
                'resignation_id' => $id,
                'user_id' => $authUser->id,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);


            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        } 
    }
    
    // resignation hr index
    public function resignationHRIndex(Request $request)
    {   
 
        $authUser = $this->authUser(); 
        $permission = PermissionHelper::get(59);
        $branches =  Branch::where('tenant_id',  $authUser->tenant_id)->get();  
        $departments = Department::get();  
        $designations = Designation::get();
        $resignations = Resignation::with([
            'personalInformation',
            'employmentDetail.branch',
            'employmentDetail.department.designations',
        ])->where('status',1)->get(); 

        return view('tenant.resignation.resignation-hr',['resignations' => $resignations,'permission' => $permission,'branches' =>$branches, 'departments' => $departments, 'designations'=> $designations]);
    }

    // resignation hr autofilter branch
      public function HRfromBranch(Request $request)
    {
      
        $branchId = $request->input('branch_id');

        if (empty($branchId)) {
            $departments =Department::get()->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->department_name,
            ]);

            $designations = Designation::get()->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->designation_name,
            ]);
        } else {
            $departments =Department::where('branch_id', $branchId)
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->department_name,
                ]);

            $designations = Designation::whereHas('department', fn ($q) => $q->where('branch_id', $branchId))
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

     // resignation hr autofilter department

    public function HRfromDepartment(Request $request)
    {     
        
        $departmentId = $request->input('department_id');
        $branchId = $request->input('branch_id');  

        if (empty($departmentId)) {  

            if (!empty($branchId)) {
                $departments = Department::where('branch_id', $branchId);
            }

            $departmentIds = $departments->pluck('id')->toArray();

            $designations = Designation::whereIn('department_id', $departmentIds)
                ->get()
                ->map(fn($d) => ['id' => $d->id, 'name' => $d->designation_name])
                ->values();

            return response()->json([
                'status' => 'success',
                'branch_id' => $branchId,
                'designations' => $designations,
            ]);
        }
 
        $department = Department::firstWhere('id', $departmentId);

        $designations = Designation::
             where('department_id', $departmentId)
            ->get()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->designation_name])
            ->values();

        return response()->json([
            'status' => 'success',
            'branch_id' => $department?->branch_id,
            'designations' => $designations,
        ]);
    } 
     // resignation hr autofilter designation 
 
  public function HRfromDesignation(Request $request)
    {
        $designationId = $request->input('designation_id');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');

        // If no specific designation is selected, filter available designations
        if (empty($designationId)) {
            $designationsQuery = Designation::query();

            if (!empty($departmentId)) {
                $designationsQuery->where('department_id', $departmentId);
            } elseif (!empty($branchId)) {
                $designationsQuery->whereHas('department', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }

            $designations = $designationsQuery->get(['id', 'designation_name'])
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->designation_name,
                ]);

            return response()->json([
                'status' => 'success',
                'designations' => $designations,
            ]);
        }

        // If a designation is selected, get its department and branch
        $designation = Designation::with('department')->find($designationId);
        $department = $designation?->department;

        return response()->json([
            'status' => 'success',
            'branch_id' => $department?->branch_id,
            'department_id' => $designation?->department_id,
        ]);
    }

    // hr filter
    public function HRfilter(Request $request)
     {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(59);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');


        $query  = Resignation::with([
            'personalInformation',
            'employmentDetail.branch',
            'employmentDetail.department.designations',
        ])->where('status',1);

        if ($dateRange) {
            try {
                [$start, $end] = explode(' - ', $dateRange);
                $start = Carbon::createFromFormat('m/d/Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('m/d/Y', trim($end))->endOfDay();

                $query->whereBetween('date_filed', [$start, $end]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date range format.'
                ]);
            }
        }
        if ($branch) {
            $query->whereHas('user.employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('user.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        }
        
        if (!is_null($status)) {
            switch ($status) { 

                case 1:
                    $query->where('status', 1)
                        ->whereNull('accepted_by');
                    break; 
                case 3:
                    $query->where('status', 1)
                        ->whereNotNull('accepted_by')
                        ->where('cleared_status', 0);
                    break;
                case 4:
                    $query->where('status', 1)
                        ->whereNotNull('accepted_by')
                        ->where('cleared_status', 1)
                        ->where('resignation_date', '>', Carbon::today());
                    break; 
                case 5:
                    $query->where('status', 1)
                        ->whereNotNull('accepted_by')
                        ->where('cleared_status', 1)
                        ->where('resignation_date', '<=', Carbon::today());
                    break;

            }
        }


        $resignations = $query->get();
        
        $html = view('tenant.resignation.resignation-hr-filter', compact('resignations', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }

    // hr accept resignation 
    public function acceptByHR(Request $request, $id)
    {
        $authUser = $this->authUser();
    

        $request->validate([
            'accepted_remarks' => 'required|string|max:500',
            'resignation_date' => 'required|date',
            'accepted_instruction' => 'nullable|string|max:1000',
            'resignation_attachment.*' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:5120'
        ]);

        try {
            DB::beginTransaction();

            $resignation = Resignation::findOrFail($id);

            if ($resignation->status !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resignation must be approved by the department head/reporting to first.'
                ], 400);
            }
    
            $resignation->update([
                'resignation_date' => $request->resignation_date,
                'accepted_date' => now(),
                'accepted_by' => $authUser->id,
                'accepted_remarks' => $request->accepted_remarks,
                'instruction' => $request->accepted_instruction,
            ]);
    
            if ($request->hasFile('resignation_attachment')) {
                foreach ($request->file('resignation_attachment') as $file) {
                    if ($file->isValid()) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $destinationPath = public_path('storage/resignation_attachments');

                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0777, true);
                        }

                        $file->move($destinationPath, $fileName);

                        ResignationAttachment::create([
                            'resignation_id' => $resignation->id,
                            'uploaded_by' => $authUser->id,
                            'uploader_role' => 'hr',
                            'filename' => $fileName,
                            'filepath' => 'storage/resignation_attachments/' . $fileName,
                            'filetype' => $file->getClientOriginalExtension(),
                        ]);

                    
                    }  
                }
            } else {
                Log::warning("⚠️ No files detected in the request for resignation ID {$id}");
            }


            DB::commit();
          
            if ($resignation->user) {
                $resignation->user->notify(
                    new UserNotification("HR accepted your resignation effective {$request->resignation_date}. View it in Resignation Employee for clearance details.")
                );
            }
 
            return response()->json([
                'success' => true,
                'message' => 'Resignation has been accepted by HR successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error accepting resignation by HR (ID {$id}): " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


    // upload hr attachments 


    public function uploadHrAttachments(Request $request, $id)
    { 
        $authUser = $this->authUser(); 
        $request->validate([
            'hr_resignation_attachment.*' => 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:5120'
        ]);

        $resignation = Resignation::findOrFail($id);

        foreach ($request->file('hr_resignation_attachment') as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/resignation_attachments'), $filename);

            ResignationAttachment::create([
                'resignation_id' => $resignation->id,
                'uploaded_by' => $authUser->id,
                'uploader_role' => 'hr',
                'filename' => $filename,
                'filepath' => 'storage/resignation_attachments/' . $filename,
                'filetype' => $file->getClientOriginalExtension(),
            ]); 
        } 
        $attachments = $resignation->hrResignationAttachments()->get();
        $html = view('tenant.resignation.resignation-hr-attachments-partials', compact('attachments'))->render();

        if ($resignation->user) {
            $resignation->user->notify(
                new UserNotification("HR has uploaded a new clearance attachment related to your resignation. Please review it at your earliest convenience.")
            );
        }


        return response()->json(['success' => true, 'html' => $html]);
    }

    // show resignation remarks
    public function getRemarks($id)
    {
        $resignation = Resignation::find($id);

        if (!$resignation) {
            return response()->json(['success' => false, 'message' => 'Resignation not found.']);
        }

        return response()->json([
            'success' => true,
            'status_remarks' => $resignation->status_remarks,
            'accepted_remarks' => $resignation->accepted_remarks,
            'instruction' => $resignation->instruction
        ]);
    }
       

// HR receive assets
  public function saveHRAssets(Request $request)
    {
    $authUser = $this->authUser();

    $conditions = $request->input('condition', []);
    $statuses = $request->input('status', []);
    $hasChanges = false;  

    foreach ($conditions as $assetId => $condition) {
        $status = $statuses[$assetId] ?? null;
        $currentAsset = AssetsDetails::find($assetId);

        if (!$currentAsset) {
            continue;
        } 
        $previousCondition = $currentAsset->asset_condition;
        $previousStatus = $currentAsset->status;
 
        if ($previousCondition === $condition && $previousStatus === $status) {
            continue;
        }
 
        $currentAsset->asset_condition = $condition;
        $currentAsset->status = $status; 
        $currentAsset->save();
        
        $assetDetailsHistory = new AssetsDetailsHistory();
        $assetDetailsHistory->asset_detail_id = $currentAsset->id;
        $assetDetailsHistory->item_no = $currentAsset->order_no;
        $assetDetailsHistory->condition = $currentAsset->asset_condition;
        $assetDetailsHistory->condition_remarks = null;
        $assetDetailsHistory->status = $currentAsset->status;
        $assetDetailsHistory->deployed_to = $currentAsset->deployed_to;
        $assetDetailsHistory->deployed_date = $currentAsset->deployed_date;
        $assetDetailsHistory->process = 'Updated asset condition/status';
        $assetDetailsHistory->updated_by = $authUser->id ?? null;
        $assetDetailsHistory->updated_at = now();
        $assetDetailsHistory->created_by = $currentAsset->created_by;
        $assetDetailsHistory->created_at = $currentAsset->created_at;
        $assetDetailsHistory->save();

        $hasChanges = true;  
    }

    if($hasChanges){
        $currentAsset->user->notify(
                new UserNotification("HR has updated the asset condition and status for your resignation clearance. Please review the changes in your records.")
            );
    }

    return response()->json([
        'success' => true,
        'message' => 'Assets return form submitted successfully.',
    ]);
}
 
public function saveHRRemark(Request $request)
{
    $authUser = $this->authUser();

    $request->validate([
        'asset_id' => 'required|integer|exists:assets_details,id',
        'condition_remarks' => 'required|string|max:500',
    ]);

    $asset = AssetsDetails::findOrFail($request->asset_id);
 
    $asset->remarks()->create([
        'asset_detail_id' => $request->asset_id,
        'asset_holder_id' => $asset->deployed_to,
        'item_no' => $asset->order_no,  
        'remarks_from' => 'HR',
        'condition_remarks' => $request->condition_remarks,
    ]);
 
    $asset->load('remarks');

    $html = view('tenant.resignation.resignation-hr_asset_remarks_list', [
        'asset' => $asset
    ])->render();

    return response()->json([
        'message' => 'Remark saved successfully.',
        'html' => $html
    ]);
}
public function updateAttachmentStatuses(Request $request, $resignationId)
{
    $validated = $request->validate([
        'statuses' => 'required|array',
        'statuses.*' => 'in:approved,pending,rejected',
    ]);

    $updatedCount = 0;

    foreach ($validated['statuses'] as $id => $newStatus) {
        $attachment = ResignationAttachment::find($id);

        if ($attachment && $attachment->status !== $newStatus) {
            $attachment->update(['status' => $newStatus]);
            $updatedCount++;
        }
    }

    if ($updatedCount === 0) {
        return response()->json([
            'message' => 'No changes detected.'
        ], 200);
    }else{
        $attachment->resignation->user->notify(
            new UserNotification("HR has validated the status of your uploaded resignation attachments. Please review the updates in your records.")
        );

    }

    return response()->json([
        'message' => 'Attachment statuses updated successfully.',
        'updated' => $updatedCount
    ]);
} 
     // send HR attachment remarks 
    public function storeAttachmentRemarks(Request $request, $id)
    {     
        $authUser = $this->authUser();
        ResignationAttachmentRemarks::create([
            'resignation_attachment_id' => $id,
            'remarks_from'              => $authUser->id,
            'remarks_from_role'         => 'HR',
            'remarks'                   => $request->remarks,
        ]); 

        $remarks = ResignationAttachmentRemarks::where('resignation_attachment_id', $id)->get();

        $html = view('tenant.resignation.resignation-hr-attachment-remarks', compact('remarks'))->render();
        return response()->json(['html' => $html]);
    }

    public function markCleared($id)
    {
        try {
            $authUser = $this->authUser();
            $resignation = Resignation::findOrFail($id);
            $employee = $resignation->user;
            $employeeId = $resignation->user_id;
    

                $resignation->update([
                    'cleared_status' => 1,
                    'cleared_by' => $authUser->id,
                    'cleared_date' => Carbon::now(),
                ]);

                $assets = AssetsDetails::where('deployed_to', $employeeId)->get();
                if($assets){
                foreach ($assets as $asset) {
                    AssetsDetailsHistory::create([
                        'asset_detail_id' => $asset->id,
                        'item_no' => $asset->order_no,
                        'condition' => $asset->asset_condition,
                        'condition_remarks' => $asset->condition_remarks,
                        'status' => $asset->status,
                        'deployed_to' => $asset->deployed_to,
                        'deployed_date' => $asset->deployed_date,
                        'process' => 'Cleared employee assets during resignation',
                        'updated_by' => $authUser->id,
                        'updated_at' => Carbon::now(),
                        'created_by' => $asset->created_by,
                        'created_at' => $asset->created_at,
                    ]);

                    $asset->update([
                        'asset_status' => 'Available',
                        'deployed_to' => null,
                        'deployed_date' => null,
                    ]);
                }
            }

            ResignationAttachment::where('resignation_id', $id)
                ->where('uploaded_by', $resignation->user_id)
                ->where('status', 'pending')
                ->update(['status' => 'approved']); 
                
            if ($employee) {
                $employee->notify(
                    new UserNotification("HR has confirmed that your resignation clearance has been completed. All your submitted attachments and company assets have been validated.")
                );
            } 
            return response()->json([
                'success' => true,
                'message' => 'Resignation cleared successfully. All received assets have been marked as available and recorded in history.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while clearing resignation: ' . $e->getMessage()
            ], 500);
        }
    }
    // hr resignation attachment remarks mark as read
    public function markRemarksAsRead($id)
    {
        try {
            ResignationAttachmentRemarks::where('resignation_attachment_id', $id)
                ->where('remarks_from_role','Employee')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Remarks marked as read.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark remarks as read: ' . $e->getMessage()
            ], 500);
        }
    }

 }
