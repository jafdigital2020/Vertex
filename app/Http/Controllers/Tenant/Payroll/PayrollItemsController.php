<?php

namespace App\Http\Controllers\Tenant\Payroll;

use App\Models\Branch;
use App\Models\OtTable;
use App\Models\UserLog;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Models\UserDeminimis;
use App\Helpers\PermissionHelper;
use App\Models\DeminimisBenefits;
use App\Models\WithholdingTaxTable;
use App\Http\Controllers\Controller;
use App\Models\SssContributionTable;
use Illuminate\Support\Facades\Auth;
use App\Models\PhilhealthContribution;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DataAccessController;
use Database\Seeders\SssContribution;

class PayrollItemsController extends Controller
{
    public function authUser()
    {
        if (Auth::guard('global')->check()) {
            return Auth::guard('global')->user();
        }
        return Auth::guard('web')->user();
    }  

    public function payrollItemsSSSContributionFilter(Request $request){
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $sort_by = $request->input('sort_by');

      
        $query = SssContributionTable::query();

        if ($sort_by === 'recent') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort_by === 'asc') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort_by === 'desc') {
            $query->orderBy('created_at', 'desc');
        } 

        $sssContributions = $query->get();

        $html = view('tenant.payroll.payroll-items.sss-contribution_filter', compact('sssContributions', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]); 
    }
    public function payrollItemsSSSContribution(Request $request)
    {  
        $authUser = $this->authUser();
        $permission = PermissionHelper::get(26);
        $sssContributions = SssContributionTable::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items SSS contribution',
                'data' => $sssContributions
            ]);
        } 
        return view('tenant.payroll.payroll-items.sss-contribution', compact('sssContributions','permission'));
    }

    // PhilHealth Contribution
       public function payrollItemsPhilHealthContributionFilter(Request $request){
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $sort_by = $request->input('sort_by');
 
        $query = PhilhealthContribution::query();

        if ($sort_by === 'recent') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort_by === 'asc') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort_by === 'desc') {
            $query->orderBy('created_at', 'desc');
        } 

        $philHealthContributions = $query->get();

        $html = view('tenant.payroll.payroll-items.philhealth_filter', compact('philHealthContributions', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]); 
    }

    public function payrollItemsPhilHealthContribution(Request $request)
    {
        $philHealthContributions = PhilhealthContribution::all();
        $permission = PermissionHelper::get(26);
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items PhilHealth contribution',
                'data' => $philHealthContributions
            ]);
        }

        return view('tenant.payroll.payroll-items.philhealth', compact('philHealthContributions','permission'));
    }
 
    // Withholding Tax

     public function payrollItemsWithholdingTaxFilter(Request $request){
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $sort_by = $request->input('sort_by');
 
        $query =  WithholdingTaxTable::query();

        if ($sort_by === 'recent') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort_by === 'asc') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort_by === 'desc') {
            $query->orderBy('created_at', 'desc');
        } 

        $withholdingTaxes = $query->get();

        $html = view('tenant.payroll.payroll-items.withholdingtax_filter', compact('withholdingTaxes', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]); 
    }

    public function payrollItemsWithholdingTax(Request $request)
    {
        $withholdingTaxes = WithholdingTaxTable::all();
        $permission = PermissionHelper::get(26);
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items withholding tax',
                'data' => $withholdingTaxes
            ]);
        } 
        return view('tenant.payroll.payroll-items.withholdingtax', compact('withholdingTaxes','permission'));
    }

    // Overtime Table 

    public function payrollItemsOTtableFilter(Request $request){

        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(26);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $sort_by = $request->input('sort_by');
 
        $query =  OtTable::query();

        if ($sort_by === 'recent') {
            $query->orderBy('created_at', 'desc');
        } elseif ($sort_by === 'asc') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort_by === 'desc') {
            $query->orderBy('created_at', 'desc');
        } 

        $ots = $query->get();

        $html = view('tenant.payroll.payroll-items.ot-table_filter', compact('ots', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]); 
    }

    public function payrollItemsOTtable(Request $request)
    {
        $ots = OtTable::all();
        $permission = PermissionHelper::get(26);
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items overtime table',
                'data' => $ots
            ]);
        }

        return view('tenant.payroll.payroll-items.ot-table', compact('ots','permission'));
    }

    // De minimis Table (Benefits)

    
    public function payrollItemsDeMinimisTable(Request $request)
    {
        $deMinimis = DeminimisBenefits::all();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payroll items de minimis table',
                'data' => $deMinimis
            ]);
        }

        return view('tenant.payroll.payroll-items.deminimis.benefits', compact('deMinimis'));
    }

    // User Deminimis
    public function userDeminimisIndex(Request $request)
    {
        $branches = Branch::where('status', '1')->get();
        $departments = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $deMinimis = DeminimisBenefits::all();
        $userDeminimis = UserDeminimis::with(['deminimisBenefit', 'user'])->get();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User de minimis benefits',
                'data' => $deMinimis,
                'branches' => $branches,
                'departments' => $departments,
                'designations' => $designations,
                'userDeminimis' => $userDeminimis
            ]);
        }

        return view('tenant.payroll.payroll-items.deminimis.deminimisuser', [
            'deMinimis' => $deMinimis,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'userDeminimis' => $userDeminimis
        ]);
    }

    // User Deminimis Store
    public function userDeminimisAssign(Request $request)
    {
        $rules = [
            'user_id'              => 'required|array|min:1',
            'user_id.*'            => 'integer|exists:users,id',
            'deminimis_benefit_id' => 'required|integer|exists:deminimis_benefits,id',
            'amount'               => 'required|numeric|min:0',
            'benefit_date'         => 'required|date',
            'taxable_excess'       => 'required|numeric|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $userIds       = $request->input('user_id');
        $benefitId     = $request->input('deminimis_benefit_id');
        $amount        = $request->input('amount');
        $benefitDate   = $request->input('benefit_date');
        $taxableExcess = $request->input('taxable_excess');

        $duplicates = [];
        foreach ($userIds as $uid) {
            $exists = UserDeminimis::where('user_id', $uid)
                ->where('deminimis_benefit_id', $benefitId)
                ->exists();
            if ($exists) {
                $duplicates[] = $uid;
            }
        }

        if (!empty($duplicates)) {
            // Return a friendly error listing which user IDs are duplicates
            $duplicateList = implode(', ', $duplicates);
            return response()->json([
                'message' => 'Cannot assign De Minimis: duplicate entries detected.',
                'errors'  => [
                    'duplication' => [
                        "User ID(s) {$duplicateList} already have this De Minimis benefit assigned."
                    ]
                ]
            ], 422);
        }

        $createdRecords = [];

        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        foreach ($userIds as $uid) {
            $record = UserDeminimis::create([
                'user_id'              => $uid,
                'deminimis_benefit_id' => $benefitId,
                'amount'               => $amount,
                'benefit_date'         => $benefitDate,
                'taxable_excess'       => $taxableExcess,
                'status'               => 'active',
                'created_by_id' => Auth::user()->id,
                'created_by_type' => get_class(Auth::user()),
            ]);

            UserLog::create([
                'user_id'         => $userId,
                'global_user_id'  => $globalUserId,
                'module'          => 'Deminimis Assignment',
                'action'          => 'Assign',
                'description'     => 'Assigned De Minimis Benefit (ID: ' . $benefitId . ') to User ID ' . $uid,
                'affected_id'     => $record->id,
                'old_data'        => null,
                'new_data'        => json_encode($record->toArray()),
            ]);

            $createdRecords[] = $record;
        }

        return response()->json([
            'message' => 'Deminimis assigned successfully.',
            'data'    => $createdRecords
        ], 201);
    }

    // User Deminimis Update
    public function userDeminimisUpdate(Request $request, $id)
    {

        $record = UserDeminimis::findOrFail($id);

        $rules = [
            'deminimis_benefit_id' => 'required|integer|exists:deminimis_benefits,id',
            'amount'               => 'required|numeric|min:0',
            'benefit_date'         => 'required|date',
            'taxable_excess'       => 'required|numeric|min:0',
            'status'               => 'required|in:active,inactive',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $newBenefitId    = $request->input('deminimis_benefit_id');
        $newAmount       = $request->input('amount');
        $newBenefitDate  = $request->input('benefit_date');
        $newTaxableExcess = $request->input('taxable_excess');
        $newStatus       = $request->input('status');

        $userId       = null;
        $globalUserId = null;

        if (Auth::guard('web')->check()) {
            $userId = Auth::guard('web')->id();
        } elseif (Auth::guard('global')->check()) {
            $globalUserId = Auth::guard('global')->id();
        }

        $exists = UserDeminimis::where('user_id', $record->user_id)
            ->where('deminimis_benefit_id', $newBenefitId)
            ->where('id', '<>', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Cannot update: duplicate De Minimis benefit for this user.',
                'errors'  => [
                    'duplication' => [
                        "User ID {$record->user_id} already has benefit ID {$newBenefitId} assigned."
                    ]
                ]
            ], 422);
        }

        $oldData = $record->toArray();

        $record->deminimis_benefit_id = $newBenefitId;
        $record->amount               = $newAmount;
        $record->benefit_date         = $newBenefitDate;
        $record->taxable_excess       = $newTaxableExcess;
        $record->status               = $newStatus;
        $record->updated_by_type      = get_class(Auth::user());
        $record->updated_by_id        =  Auth::user()->id;
        $record->save();

        UserLog::create([
            'user_id'        => $userId,
            'global_user_id' => $globalUserId,
            'module'         => 'Deminimis',
            'action'         => 'Update',
            'description'    => 'Updated De Minimis (Record ID: ' . $id . ').',
            'affected_id'    => $id,
            'old_data'       => json_encode($oldData),
            'new_data'       => json_encode($record->toArray()),
        ]);

        return response()->json([
            'message' => 'Deminimis record updated successfully.',
            'data'    => $record
        ], 200);
    }

    // User Deminimis Delete
    public function userDeminimisDelete($id)
    {
        $record = UserDeminimis::findOrFail($id);

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
            'module'          => 'Deminimis',
            'action'          => 'Delete',
            'description'     => 'Deleted De Minimis (Record ID: ' . $id . ').',
            'affected_id'     => $id,
            'old_data'        => json_encode($record->toArray()),
            'new_data'        => null,
        ]);

        $record->delete();

        return response()->json([
            'message' => 'Deminimis record deleted successfully.'
        ], 200);
    }
}
