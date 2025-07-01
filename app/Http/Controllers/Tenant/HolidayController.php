<?php

namespace App\Http\Controllers\Tenant;

use PDO;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\Holiday;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\HolidayException;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HolidayController extends Controller
{

    public function authUser() {
      if (Auth::guard('global')->check()) {
         return Auth::guard('global')->user();
      }
      return Auth::guard('web')->user();
   }
    
    public function holidayIndex(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $tenant_id = $authUser->tenant_id ?? null;

        $permission = PermissionHelper::get(13);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
        $holidays = $accessData['holidays']->get();
        $today = Carbon::today();
        $upcomingHolidays = $holidays->map(function ($holiday) use ($today) {
            try {
                if ($holiday->date) {
                    $holiday->effective_date = Carbon::createFromFormat('Y-m-d', $holiday->date);
                } elseif ($holiday->month_day) {
                    $year = $today->format('Y');
                    $recurringDate = Carbon::createFromFormat('Y-m-d', "$year-" . $holiday->month_day);
                    if ($recurringDate->lt($today)) {
                        $recurringDate->addYear();
                    }
                    $holiday->effective_date = $recurringDate;
                } else {
                    $holiday->effective_date = null;
                }
            } catch (Exception $e) {
                $holiday->effective_date = null;
            }

            return $holiday;
        })->filter(function ($holiday) use ($today) {
            return $holiday->effective_date && $holiday->effective_date->gte($today);
        })->sortBy('effective_date')->values();

        $branches = $accessData['branches']->get();
        $departments  = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
 

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Leave settings',
                'data' => $holidays,
            ]);
        }
        return view('tenant.holiday.holiday', [
            'holidays' => $upcomingHolidays,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'permission' => $permission
        ]);
    }

    public function holidayFilter(Request $request)
    {
        $authUser = $this->authUser();
        $authUserId = $authUser->id;
        $tenant_id = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(13);

        if (!in_array('Read', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to read.'
            ]);
        }

        $dateRange = $request->input('dateRange');
        $status = $request->input('status');
        $paid = $request->input('paid');
        $holidayType = $request->input('holidayType');
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        
        $query = $accessData['holidays'];

        if ($dateRange) {
            [$start, $end] = explode(' - ', $dateRange);

            $startDate = date('Y-m-d', strtotime($start));
            $endDate = date('Y-m-d', strtotime($end));
            $startMonthDay = date('m-d', strtotime($start));
            $endMonthDay = date('m-d', strtotime($end));

            $query->where(function ($q) use ($startDate, $endDate, $startMonthDay, $endMonthDay) {
                $q->where(function ($subQ) use ($startDate, $endDate) {
                    $subQ->where('recurring', 0)
                        ->whereBetween('date', [$startDate, $endDate]);
                })
                ->orWhere(function ($subQ) use ($startMonthDay, $endMonthDay) {
                    $subQ->where('recurring', 1)
                        ->whereBetween('month_day', [$startMonthDay, $endMonthDay]);
                });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($paid !== null) {
            $query->where('is_paid', $paid);
        }
        if($holidayType){
            $query->where('type', $holidayType);
        }

        $holidays = $query->get();
        $today = Carbon::today();
        $holidays = $holidays->map(function ($holiday) use ($today) {
            try {
                if ($holiday->date) {
                    $holiday->effective_date = Carbon::createFromFormat('Y-m-d', $holiday->date);
                } elseif ($holiday->month_day) {
                    $year = $today->format('Y');
                    $recurringDate = Carbon::createFromFormat('Y-m-d', "$year-" . $holiday->month_day);
                    if ($recurringDate->lt($today)) {
                        $recurringDate->addYear();
                    }
                    $holiday->effective_date = $recurringDate;
                } else {
                    $holiday->effective_date = null;
                }
            } catch (Exception $e) {
                $holiday->effective_date = null;
            }

            return $holiday;
        })->filter(function ($holiday) use ($today) {
            return $holiday->effective_date && $holiday->effective_date->gte($today);
        })->sortBy('effective_date')->values();
            return response()->json([
                'status' => 'success',
                'html' => view('tenant.holiday.holiday_filter', compact('holidays', 'permission'))->render(),
                'permission' => $permission
            ]);
    }


    // Create/Store Holiday
    public function holidayStore(Request $request)
    {
        $authUser = $this->authUser();
        $tenant_id = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(13);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'date'      => 'required|date',
            'type'      => 'required|in:regular,special-non-working,special-working',
            'is_paid'   => 'required|boolean',
            'recurring' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors occurred.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data        = $validator->validated();
            $isRecurring = $request->boolean('recurring');
            $dt          = Carbon::parse($data['date']);
            $monthDay    = $isRecurring ? $dt->format('m-d') : null;
            $fullDate    = $isRecurring ? null : $dt->toDateString();

            // Only check for duplicates within the same tenant
            $exists = Holiday::where('tenant_id', $tenant_id)
                ->where('status', 'active')
                ->where('recurring', $isRecurring)
                ->when(
                    $isRecurring,
                    fn($q) => $q->where('month_day', $monthDay),
                    fn($q) => $q->where('date', $fullDate)
                )
                ->exists();

            if ($exists) {
                $field = $isRecurring ? 'month_day' : 'date';
                return response()->json([
                    'message' => "An active holiday on that $field already exists.",
                    'errors'  => [
                        $field => ["Holiday already defined for this $field."]
                    ],
                ], 422);
            }

            $holiday = Holiday::create([
                'name'      => $data['name'],
                'type'      => $data['type'],
                'recurring' => $isRecurring,
                'month_day' => $monthDay,
                'date'      => $fullDate,
                'is_paid'   => $data['is_paid'],
                'tenant_id' => $tenant_id,
            ]);

            // Logging Start
            $userId = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id'        => $userId,
                'global_user_id' => $globalUserId,
                'module'         => 'Holiday',
                'action'         => 'Create',
                'description'    => 'Created Holiday "' . $holiday->name . '" on ' .
                    ($holiday->recurring ? 'recurring every ' . $holiday->month_day : $holiday->date),
                'affected_id'    => $holiday->id,
                'old_data'       => null,
                'new_data'       => json_encode($holiday->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Holiday added successfully.',
                'holiday' => $holiday,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the holiday.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Update Holiday
    public function holidayUpdate(Request $request, $id)
    {
        $authUser = $this->authUser();
        $tenant_id = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(13);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ]);
        }

        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'name'      => 'required|string|max:255',
                'date'      => 'required|date',
                'type'      => 'required|in:regular,special-non-working,special-working',
                'is_paid'   => 'required|boolean',
                'recurring' => 'required|boolean',
                'status'    => 'required|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $isRecurring = $request->boolean('recurring');

            $dt = Carbon::parse($data['date']);
            if ($isRecurring) {
                $monthDay = $dt->format('m-d');
                $fullDate = null;
            } else {
                $monthDay = null;
                $fullDate = $dt->toDateString();
            }

            $holiday = Holiday::findOrFail($id);
            $oldData = $holiday->toArray();

            if ($data['status'] === 'active') {
                $exists = Holiday::where('tenant_id', $tenant_id)
                    ->where('status', 'active')
                    ->where('recurring', $isRecurring)
                    ->when(
                        $isRecurring,
                        fn($q) => $q->where('month_day', $monthDay),
                        fn($q) => $q->where('date', $fullDate)
                    )
                    ->where('id', '<>', $id)
                    ->exists();

                if ($exists) {
                    $field = $isRecurring ? 'month_day' : 'date';
                    $msg   = "An active holiday on that $field already exists.";
                    return response()->json([
                        'message' => $msg,
                        'errors'  => [$field => [$msg]],
                    ], 422);
                }
            }

            $holiday->update([
                'name'      => $data['name'],
                'type'      => $data['type'],
                'recurring' => $isRecurring,
                'month_day' => $isRecurring ? $monthDay : null,
                'date'      => $isRecurring ? null : $fullDate,
                'is_paid'   => $data['is_paid'],
                'status'    => $data['status'],
            ]);

            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Holiday',
                'action'          => 'Update',
                'description'     => 'Updated Holiday "' . $holiday->name . '" to ' .
                    ($holiday->recurring ? 'recurring every ' . $holiday->month_day : $holiday->date),
                'affected_id'     => $holiday->id,
                'old_data'        => json_encode($oldData),
                'new_data'        => json_encode($holiday->toArray()),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Holiday updated successfully.',
                'holiday' => $holiday,
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Holiday not found.',
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the holiday.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function holidayDelete($id)
    {
        $authUser = $this->authUser();
        $tenant_id = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(13);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $holiday = Holiday::findOrFail($id);
            $oldData = $holiday->toArray();
            $holiday->delete();

            $userId = Auth::guard('web')->id();
            $globalUserId = Auth::guard('global')->id();

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Holiday',
                'action'          => 'Delete',
                'description'     => 'Deleted Holiday "' . $holiday->name . '"',
                'affected_id'     => $holiday->id,
                'old_data'        => json_encode($oldData),
                'new_data'        => null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Holiday deleted successfully.',
            ], 200);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Holiday not found.',
            ], 404);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while deleting the holiday.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
 // ==================== Holiday Exceptions ==================== //
 public function getDepartments(Request $request)
{
    $authUser = $this->authUser();
    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser);

    $branchIds = $request->input('branch_ids', []);
    Log::info($branchIds);
    if ($branchIds == 'all') {
        $departments = $accessData['departments']->values();
    } else {
        $departments = $accessData['departments']
            ->whereIn('branch_id', $branchIds)
            ->values();
    } 
    return response()->json($departments);
}

    public function getDesignations(Request $request)
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);

        $deptIds = $request->input('department_ids', []);
        $designations = $accessData['designations']
            ->whereIn('department_id', $deptIds)
            ->values();

        return response()->json($designations);
    }

    public function getEmployees(Request $request)
    {
        $branchIds = $request->input('branch_ids', []);
        $deptIds = $request->input('department_ids', []);
        $desIds = $request->input('designation_ids', []);

        $employees = User::query()
        ->whereHas('employmentDetail', function ($q) use ($branchIds, $deptIds, $desIds) {
            if (!empty($branchIds)) {
                $q->whereIn('branch_id', $branchIds);
            }
            if (!empty($deptIds)) {
                $q->whereIn('department_id', $deptIds);
            }
            if (!empty($desIds)) {
                $q->whereIn('designation_id', $desIds);
            }
        })
        ->with(['personalInformation', 'employmentDetail'])
        ->get();

        return response()->json($employees);
    }

    public function holidayExceptionIndex(Request $request)
    {
        $authUser = $this->authUser();
        $tenant_id = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(13);

        $holidays = Holiday::where('tenant_id', $tenant_id)->get();

        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $holidayExceptions = $accessData['holidayException']->get();
        $branches =  $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();

        // API Response
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Holiday exceptions',
                'data'    => $holidays,
                'branches' => $branches,
                'departments' => $departments,
                'designations' => $designations,
                'holidayExceptions' => $holidayExceptions,
            ]);
        }

        // Web Response
        return view('tenant.holiday.holidayexceptions', [
            'holidays' => $holidays,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'holidayExceptions' => $holidayExceptions,
            'permission' => $permission
        ]);
    }

    // Add User Holiday Exception
    public function holidayExceptionUserStore(Request $request)
    {
        $permission = PermissionHelper::get(13);

        if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to create.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|array|min:1',
            'user_id.*'    => 'exists:users,id',
            'holiday_id'   => 'required|array|min:1',
            'holiday_id.*' => 'exists:holidays,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        foreach ($data['user_id'] as $uid) {
            foreach ($data['holiday_id'] as $hid) {
                $exists = HolidayException::where('holiday_id', $hid)
                    ->where('user_id', $uid)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'message' => 'Holiday exception already exists for this user.',
                        'errors'  => [
                            'holiday_exception' => ['Holiday exception already exists for this user.'],
                        ],
                    ], 422);
                }
            }
        }

        DB::transaction(function () use ($data) {
            $userId       = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            foreach ($data['user_id'] as $uid) {
                foreach ($data['holiday_id'] as $hid) {
                    // Create the exception
                    $he = HolidayException::create([
                        'user_id'    => $uid,
                        'holiday_id' => $hid,
                        'created_by_id' => Auth::user()->id,
                        'created_by_type' => get_class(Auth::user()),
                    ]);

                    // Immediately log it
                    UserLog::create([
                        'user_id'         => $userId,
                        'global_user_id'  => $globalUserId,
                        'module'      => 'Holiday Exception',
                        'action'      => 'Create',
                        'description' => "Created exception for user {$uid} on holiday {$hid}",
                        'affected_id' => $he->id,
                        'old_data'    => null,
                        'new_data'    => json_encode($he->toArray()),
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Holiday exceptions added successfully.'
        ], 201);
    }

    // Deactivate Holiday Exception
    public function holidayExceptionDeactivate($id)
    {
        $permission = PermissionHelper::get(13);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }
        try {
            $holidayException = HolidayException::findOrFail($id);
            $holidayException->update([
                'status' => 'inactive',
                'updated_by_type' => Auth::guard('web')->check() ? 'App\Models\User' : 'App\Models\GlobalUser',
                'updated_by_id' => Auth::id(),
            ]);

            // Logging
            $userId       = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Holiday Exception',
                'action'          => 'Deactivate',
                'description'     => "Deactivated holiday exception for user {$holidayException->user_id} on holiday {$holidayException->holiday_id}",
                'affected_id'     => $holidayException->id,
                'old_data'        => json_encode($holidayException->toArray()),
                'new_data'        => json_encode($holidayException->only(['status'])),
            ]);

            return response()->json([
                'message' => 'Holiday exception deactivated successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Holiday exception not found.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deactivating the holiday exception.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Activate Holiday Exception
    public function holidayExceptionActivate($id)
    {
        $permission = PermissionHelper::get(13);

        if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to update.'
            ], 403);
        }

        try {
            $holidayException = HolidayException::findOrFail($id);
            $holidayException->update([
                'status' => 'active',
                'updated_by_type' => Auth::guard('web')->check() ? 'App\Models\User' : 'App\Models\GlobalUser',
                'updated_by_id' => Auth::id(),
            ]);

            // Logging
            $userId       = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Holiday Exception',
                'action'          => 'Activate',
                'description'     => "Activated holiday exception for user {$holidayException->user_id} on holiday {$holidayException->holiday_id}",
                'affected_id'     => $holidayException->id,
                'old_data'        => json_encode($holidayException->toArray()),
                'new_data'        => json_encode($holidayException->only(['status'])),
            ]);

            return response()->json([
                'message' => 'Holiday exception activated successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Holiday exception not found.',
            ], 404);
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return response()->json([
                'message' => 'An error occurred while activating the holiday exception.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Delete Holiday Exception
    public function holidayExceptionDelete($id)
    {
        $permission = PermissionHelper::get(13);

        if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to delete.'
            ], 403);
        }
        try {
            $holidayException = HolidayException::findOrFail($id);
            $holidayException->delete();

            // Logging
            $userId       = null;
            $globalUserId = null;

            if (Auth::guard('web')->check()) {
                $userId = Auth::guard('web')->id();
            } elseif (Auth::guard('global')->check()) {
                $globalUserId = Auth::guard('global')->id();
            }

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Holiday Exception',
                'action'          => 'Delete',
                'description'     => "Deleted holiday exception for user {$holidayException->user_id} on holiday {$holidayException->holiday_id}",
                'affected_id'     => $holidayException->id,
                'old_data'        => json_encode($holidayException->toArray()),
                'new_data'        => null,
            ]);

            return response()->json([
                'message' => 'Holiday exception deleted successfully.',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Holiday exception not found.',
            ], 404);
        } catch (Exception $e) {
             Log::info($e->getMessage());
            return response()->json([
                'message' => 'An error occurred while deleting the holiday exception.',
                'error'   => $e->getMessage(),
            ], 500);
        }
      } 

       public function holidayExFilter(Request $request)
       {
        $authUser = $this->authUser();
        $tenant_id = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(13);

        if (!in_array('Read', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to read.'
            ]);
        }

        $holiday = $request->input('holiday');
        $status = $request->input('status');
        $branch = $request->input('branch');
        $department = $request->input('department');
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
       
        $query =  $accessData['holidayException'];
        if ($branch) {
            $query->whereHas('user.employmentDetail.branch', function ($q) use ($branch) {
                $q->where('id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('user.employmentDetail.department', function ($q) use ($department) {
                $q->where('id', $department);
            });
        }
        if ($status) {
            $query->where('status', $status);
        }
        if($holiday) {
            $query->where('holiday_id',$holiday);

        }
 
        $holidayExceptions = $query->get();

        return response()->json([
            'status' => 'success',
            'html' => view('tenant.holiday.holidayexceptions_filter', compact('holidayExceptions', 'permission'))->render(),
            'permission' => $permission
        ]);

    }
    public function getDepartmentsByBranch($branchId)
    {   
   
        if ($branchId == 'all') {
            $authUser = $this->authUser();
            $dataAccessController = new DataAccessController();
            $accessData = $dataAccessController->getAccessData($authUser);
            $departments = $accessData['departments']->get();
            
        } else {
            $departments = Department::where('branch_id', $branchId)->get();
        }

        return response()->json($departments);
    }

    public function getBranchByDepartment($departmentId)
    {
        $department = Department::with('branch')->find($departmentId);
        if (!$department) {
            return response()->json(['message' => 'Department not found'], 404);
        }

        return response()->json($department->branch);
    }


}
