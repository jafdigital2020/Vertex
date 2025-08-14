<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Assets;
use App\Models\Categories;
use Illuminate\Http\Request;
use App\Models\AssetsDetails;
use App\Models\EmployeeAssets;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AssetsDetailsHistory;
use App\Models\AssetsDetailsRemarks;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Calculation\Category;

class AssetsController extends Controller
{ 
   public function authUser() {
   if (Auth::guard('global')->check()) {
      return Auth::guard('global')->user();
   } 
   return Auth::guard('web')->user();
   }  
   public function employeeAssetsFilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(49);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $dateRange = $request->input('dateRange');
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation'); 
 
        $query  = $accessData['employees']->with('assetsDetails.assets.category');

        if ($branch) {
            $query->whereHas('employmentDetail', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        } 
        $users = $query->get(); 
        $html = view('tenant.assetsmanagement.employee_assets_filter', compact('users', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }
    public function getAssetsByCategory($id)
    {
        $assetIds = Assets::where('category_id', $id)->pluck('id');  
        $assetsDetails = AssetsDetails::with('assets.category') 
            ->whereIn('asset_id', $assetIds)
            ->whereNull('deployed_to')  
            ->where('status', 'Available')
            ->whereIn('asset_condition', ['New', 'Good'])
            ->get(); 

        return response()->json($assetsDetails);
    }

    public function employeeAssetsIndex(){ 

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(49);
        $tenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $users  = $accessData['employees']->with('assetsDetails.assets.category')->get(); 
        $asset_categories = Categories::get(); 
        return view('tenant.assetsmanagement.employee_assets', [ 
            'users' => $users,
            'branches' => $branches, 
            'departments' => $departments ,
            'designations' => $designations,
            'permission' => $permission,
            'categories' => $asset_categories
        ]);
   }
    public function employeeAssetsStore(Request $request)
    {
        $permission = PermissionHelper::get(49);
        $employee_id = $request->input('employee-id');
        $assets_details_ids = $request->input('assets_details_ids');
        $remove_assets_details_ids = $request->input('removeAssetDetails_ids');
 
        try {
            DB::beginTransaction();

            if (!empty($remove_assets_details_ids) && is_array($remove_assets_details_ids)) {
                foreach ($remove_assets_details_ids as $asset_id) {
                    $asset_details = AssetsDetails::find($asset_id);
                    if ($asset_details) { 
                        $asset_details->status = 'Available';
                        $asset_details->deployed_to = null;
                        $asset_details->deployed_date = null;
                        $asset_details->save();  
                    } 
                } 
            }

            if (!empty($assets_details_ids) && is_array($assets_details_ids)) {
                foreach ($assets_details_ids as $asset_id) {
                    $asset_details = AssetsDetails::find($asset_id);

                    if ($asset_details) {
                        Log::info("Updating asset ID {$asset_id} to Deployed for employee {$employee_id}");
                        $asset_details->status = 'Deployed';
                        $asset_details->deployed_to = $employee_id;
                        $asset_details->deployed_date = Carbon::now();
                        $asset_details->save();
                    } else {
                        Log::warning("Asset ID {$asset_id} not found");
                    }
                }
            }

            foreach ($request->all() as $key => $value) {
                if (preg_match('/^condition(\d+)$/', $key, $matches)) {
                    $assetId = $matches[1];
                    $condition = $value;
                    $status = $request->input('status' . $assetId);
                    $currentAsset = AssetsDetails::find($assetId);

                    if ($currentAsset) {
                        $previousCondition = $currentAsset->asset_condition;
                        $currentAsset->asset_condition = $condition;
                        $currentAsset->status = $status;
                        $currentAsset->save();

                        if ($condition === 'Damaged' && $previousCondition !== 'Damaged') {
                            $conditionRemarks = $request->input('remarks_hidden_' . $assetId);
                            $assetDetailsRemarks = new AssetsDetailsRemarks();
                            $assetDetailsRemarks->asset_detail_id = $assetId;
                            $assetDetailsRemarks->condition_remarks = $conditionRemarks;
                            $assetDetailsRemarks->save();
                        }
                    }
                }
            }

            DB::commit(); 
            return response()->json([
                'status' => 'success',
                'message' => 'Assets changes saved successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong while assigning assets. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
 
    public function getEmployeeAssets($id)
    {
        $assets = AssetsDetails::with(['assets.category'])
            ->where('deployed_to', $id)
            ->get(); 
        return response()->json(['data' => $assets]);
    }
     public function getLatestRemarks($id)
    { 
        $asset = AssetsDetailsRemarks::where('asset_detail_id', $id)
            ->latest()  
            ->first();
        if (!$asset) {
            return response()->json([
                'condition_remarks' => null,
                'status_remarks' => null,
                'message' => 'No remarks available.'
            ]);
        }

        return response()->json([
            'condition_remarks' => $asset->condition_remarks,
            'status_remarks' => $asset->status_remarks
        ]);
    }

  public function assetsSettingsFilter(Request $request)
{
    $authUser = $this->authUser();
    $tenantId = $authUser->tenant_id;
    $permission = PermissionHelper::get(50); 

    $category = $request->input('category');
    $sortBy = $request->input('sortBy');

    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser); 

    $query = $accessData['assets']->with('category');

    if ($category) {
        $query->where('category_id', $category);
    }

    if ($sortBy === 'last_month') {
        $query->where('created_at', '>=', now()->subMonth());
    } elseif ($sortBy === 'last_7_days') {
        $query->where('created_at', '>=', now()->subDays(7));
    }

    if ($sortBy === 'ascending') {
        $query->orderBy('created_at', 'ASC');
    } elseif ($sortBy === 'descending') {
        $query->orderBy('created_at', 'DESC');
    }

    $assets = $query->get();

    $html = view('tenant.assetsmanagement.assets_settings_filter', compact('assets', 'permission'))->render();

    return response()->json([
        'status' => 'success',
        'html' => $html
    ]);
} 

    public function assetsSettingsDetails(Request $request){
        $assets_settings_id = $request->input('id');

        $assets_details = AssetsDetails::with('user.personalInformation')->where('asset_id',$assets_settings_id)->get();

        return response()->json([
            'status' => 'success',
            'assets_details' => $assets_details
        ]);

    }

    public function assetsSettingsDetailsUpdate(Request $request)
    {
        $assetId = $request->input('assetCondition_id'); 
        $conditions = (array) $request->input('condition');
        $statuses = (array) $request->input('status');  

        $assetDetails = AssetsDetails::where('asset_id', $assetId)->get(); 

        foreach ($assetDetails as $index => $detail) { 
            $condition = $conditions[$index] ?? null;
            $status = $statuses[$index] ?? null;  

            $detail->asset_condition = $condition;
            $detail->status = $status; 
            if($status == 'Available'){
                $detail->deployed_to = null;
                $detail->deployed_date = null;
            }
            $detail->save();
        }
 
        $newConditions = (array) $request->input('new_condition');
        $newStatuses = (array) $request->input('new_status');
        $newOrderNos = (array) $request->input('new_order_no');

        foreach ($newConditions as $i => $newCondition) {
            $newStatus = $newStatuses[$i] ?? null;
            $newOrderNo = $newOrderNos[$i] ?? null;

            AssetsDetails::create([
                'asset_id' => $assetId,
                'asset_condition' => $newCondition,
                'status' => $newStatus,
                'order_no' => $newOrderNo,
                'deployed_to' =>  null,
                'deployed_date' =>null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assets updated successfully.',
        ]);
    }
 
   public function assetsSettingsIndex(){

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(50);
        $tenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $assets = $accessData['assets']->with('category','assetsDetails')->get();
        $categories = $assets->pluck('category')->unique('id')->values();    
 
        return view('tenant.assetsmanagement.assets_settings', [ 
            'assets' => $assets,
            'categories' => $categories,
            'permission' => $permission
        ]);
   }
   public function assetsSettingsStore(Request $request)
   {     
    $authUser = $this->authUser();
    $permission = PermissionHelper::get(50);
 
    if (!in_array('Create', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to create.'
            ], 403);
    }

    $tenantId = $authUser->tenant_id ?? null; 
    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser);

    $request->validate([
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0', 
        'description' => 'nullable|string',
        'model' => 'nullable|string',
        'manufacturer' => 'nullable|string',
        'serial_number' => 'nullable|string',
        'processor' => 'nullable|string'
    ]);
    if ($request->category_id !== 'new') {
         $request->validate([
            'category_id' => 'nullable|exists:categories,id',
         ]);
      } else {
         $request->validate([
            'new_category_name' => 'required|string|max:255',
         ]);
      }
 
    try {
        if ($request->category_id === 'new') { 
            $category = Categories::firstOrCreate([
                'name' => $request->new_category_name,
            ]);
        } else {
            $category = Categories::find($request->category_id);
        }
 

        $asset = new Assets();
        $asset->description = $request->description;
        $asset->name = $request->name;
        $asset->quantity = $request->quantity;
        $asset->price = $request->price; 
        $asset->category_id = $category ? $category->id : null;
        $asset->branch_id = $authUser->employmentDetail->branch_id ?? 7; 
        $asset->deployment_date = Carbon::now();
        $asset->model = $request->model;
        $asset->manufacturer = $request->manufacturer;
        $asset->serial_number = $request->serial_number;
        $asset->processor = $request->processor;
        $asset->save(); 

        for ($i = 0; $i < $request->quantity; $i++) {
            $assetDetails = new AssetsDetails();
            $assetDetails->asset_id = $asset->id;
            $assetDetails->order_no = $i+ 1;
            $assetDetails->asset_condition = 'New';
            $assetDetails->status = 'Available';
            $assetDetails->save();

            $assetDetailsHistory = new AssetsDetailsHistory();
            $assetDetailsHistory->asset_detail_id = $assetDetails->id;
            $assetDetailsHistory->item_no = $assetDetails->order_no;
            $assetDetailsHistory->condition = 'New';
            $assetDetailsHistory->status = 'Available';
            $assetDetailsHistory->process = 'create assets';
            $assetDetailsHistory->created_by = $authUser->id;
            $assetDetailsHistory->created_at = Carbon::now();
            $assetDetailsHistory->save();

        }

        return redirect()->back()->with('success', 'Asset added successfully.');

    } catch (\Exception $e) { 

        Log::error('Error saving asset: '.$e->getMessage());

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Failed to add asset. Please try again later.']);
    }
}
    public function assetsSettingsUpdate(Request $request)
    {     
    $authUser = $this->authUser();
    $permission = PermissionHelper::get(50);
 
    if (!in_array('Update', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to update.'
           ], 403);
    }
    $tenantId = $authUser->tenant_id ?? null; 
    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser);

    $request->validate([
        'edit_name' => 'required|string|max:255', 
        'edit_price' => 'required|numeric|min:0', 
        'edit_description' => 'nullable|string', 
    ]);
    if ($request->category_id !== 'new') {
         $request->validate([
            'edit_category_id' => 'nullable|exists:categories,id',
         ]);
      } else {
         $request->validate([
            'edit_new_category_name' => 'required|string|max:255',
         ]);
      }
 
    try {
        if ($request->edit_category_id === 'new') { 
            $category = Categories::firstOrCreate([
                'name' => $request->edit_new_category_name,
            ]);
        } else {
            $category = Categories::find($request->edit_category_id);
        }
 

        $asset = Assets::find($request->edit_id);
        $asset->description = $request->edit_description;
        $asset->name = $request->edit_name; 
        $asset->price = $request->edit_price; 
        $asset->status = $request->edit_status;
        $asset->category_id = $category ? $category->id : null;
        $asset->branch_id = $authUser->employmentDetail->branch_id ?? 7; 
        $asset->save(); 

        return redirect()->back()->with('success', 'Asset updated successfully.');

    } catch (\Exception $e) { 
      
        Log::error('Error updating asset: '.$e->getMessage());

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Failed to update asset. Please try again later.']);
    }
} 

public function assetsSettingsDelete(Request $request)
{   
     $permission = PermissionHelper::get(50);
 
    if (!in_array('Delete', $permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have the permission to delete.'
           ], 403);
    }

    $asset = Assets::find($request->id);
  
    if (!$asset) {
        return response()->json(['status' => 'error', 'message' => 'Asset not found.'], 404);
    }

    $asset->delete();

    return response()->json(['status' => 'success']);
}

   public function assetsHistoryIndex(){

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(50);
        $tenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $assetsHistory = AssetsDetailsHistory::with('assetDetail.assets.category')->get(); 
        
    
        return view('tenant.assetsmanagement.assets_history', [ 
            'assetsHistory' => $assetsHistory, 
            'permission' => $permission
        ]);
   }

}
