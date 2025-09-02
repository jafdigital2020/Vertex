<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Assets;
use App\Models\Categories;
use Illuminate\Http\Request;
use App\Models\AssetsDetails;
use App\Models\AssetsHistory;
use App\Models\EmployeeAssets;
use App\Models\EmploymentDetail;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
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
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');
        $condition = $request->input('condition');
 
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
         if ($status) {
            $query->whereHas('assetsDetails', function ($q) use ($status) {
                $q->where('status', $status);
            });
        } 
        if ($condition) {
            $query->whereHas('assetsDetails', function ($q) use ($condition) {
                $q->where('asset_condition', $condition);
            });
        }
        $users = $query->get(); 
        $html = view('tenant.assetsmanagement.employee_assets_filter', compact('users', 'permission'))->render();
        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    }
       public function employeeAssetsHistoryFilter(Request $request)
    {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id ?? null;
        $permission = PermissionHelper::get(49);
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 
        $branch = $request->input('branch');
        $department  = $request->input('department');
        $designation = $request->input('designation');
        $status = $request->input('status');
        $condition = $request->input('condition');
  
        $query = $accessData['assetsDetailsHistory']->with('assetDetail.assets.category','deployedToEmployee.employmentDetail')
        ->orderBy('id', 'desc');

        if ($branch) {
            $query->whereHas('assetDetail.assets', function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            });
        }
        if ($department) {
            $query->whereHas('deployedToEmployee.employmentDetail', function ($q) use ($department) {
                $q->where('department_id', $department);
            });
        }
        if ($designation) {
            $query->whereHas('deployedToEmployee.employmentDetail', function ($q) use ($designation) {
                $q->where('designation_id', $designation);
            });
        } 
         if ($status) {
            $query->whereHas('assetDetail', function ($q) use ($status) {
                $q->where('status', $status);
            });
        } 
        if ($condition) {
            $query->whereHas('assetDetail', function ($q) use ($condition) {
                $q->where('asset_condition', $condition);
            });
        }
        $assetsHistory = $query->get(); 
        $html = view('tenant.assetsmanagement.employee_assets_history_filter', compact('assetsHistory', 'permission'))->render();
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
            ->whereIn('asset_condition', ['Brand New', 'Good Working Condition'])
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
   
   public function employeeAssetsHistoryIndex(){

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(49);
        $tenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $assetsHistory = AssetsDetailsHistory::with('assetDetail.assets.category')
        ->orderBy('id', 'desc')
        ->get();
    
        return view('tenant.assetsmanagement.employee_assets_history', [ 
            'assetsHistory' => $assetsHistory, 
            'permission' => $permission,
            'branches' => $branches, 
            'departments' => $departments ,
            'designations' => $designations,
        ]);
   }

    public function employeeAssetsStore(Request $request)
    {  
        $authUser = $this->authUser();
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

                        $assetDetailsHistory = new AssetsDetailsHistory();
                        $assetDetailsHistory->asset_detail_id = $asset_details->id;
                        $assetDetailsHistory->item_no = $asset_details->order_no;
                        $assetDetailsHistory->condition =  $asset_details->asset_condition;
                        $assetDetailsHistory->status = $asset_details->status;
                        $assetDetailsHistory->deployed_to = $asset_details->deployed_to;
                        $assetDetailsHistory->deployed_date = $asset_details->deployed_date;
                        $assetDetailsHistory->process = 'remove asset from user';
                        $assetDetailsHistory->updated_by = $authUser->id;
                        $assetDetailsHistory->updated_at = Carbon::now(); 
                        $assetDetailsHistory->created_by = $asset_details->created_by;
                        $assetDetailsHistory->created_at = $asset_details->created_at;
                        $assetDetailsHistory->save();
                        
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
                        $asset_details->status = 'Deployed';
                        $asset_details->deployed_to = $employee_id;
                        $asset_details->deployed_date = Carbon::now();
                        $asset_details->save(); 
                        $assetDetailsHistory = new AssetsDetailsHistory();
                        $assetDetailsHistory->asset_detail_id = $asset_details->id;
                        $assetDetailsHistory->item_no = $asset_details->order_no;
                        $assetDetailsHistory->condition =  $asset_details->asset_condition;
                        $assetDetailsHistory->status = $asset_details->status;
                        $assetDetailsHistory->deployed_to = $asset_details->deployed_to;
                        $assetDetailsHistory->deployed_date = $asset_details->deployed_date;
                        $assetDetailsHistory->process = 'assign asset to user';
                        $assetDetailsHistory->updated_by = $authUser->id;
                        $assetDetailsHistory->updated_at = Carbon::now();
                        $assetDetailsHistory->created_by = $asset_details->created_by;
                        $assetDetailsHistory->created_at = $asset_details->created_at;
                        $assetDetailsHistory->save();

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

                        if ($condition === 'Defective' && $previousCondition !== 'Defective') {
                            $conditionRemarks = $request->input('remarks_hidden_' . $assetId);
                            $assetDetailsRemarks = new AssetsDetailsRemarks();
                            $assetDetailsRemarks->asset_detail_id = $assetId;
                            $assetDetailsRemarks->condition_remarks = $conditionRemarks;
                            $assetDetailsRemarks->save();
                        }

                        $assetDetailsHistory = new AssetsDetailsHistory();
                        $assetDetailsHistory->asset_detail_id =  $currentAsset->id;
                        $assetDetailsHistory->item_no =  $currentAsset->order_no;
                        $assetDetailsHistory->condition =   $currentAsset->asset_condition;
                        $assetDetailsHistory->condition_remarks = $assetDetailsRemarks->condition_remarks ?? null;
                        $assetDetailsHistory->status =  $currentAsset->status;
                        $assetDetailsHistory->deployed_to =  $currentAsset->deployed_to;
                        $assetDetailsHistory->deployed_date =  $currentAsset->deployed_date;
                        $assetDetailsHistory->process = 'updated asset condition';
                        $assetDetailsHistory->updated_by = $authUser->id;
                        $assetDetailsHistory->updated_at = Carbon::now(); 
                        $assetDetailsHistory->created_by = $currentAsset->created_by;
                        $assetDetailsHistory->created_at = $currentAsset->created_at;
                        $assetDetailsHistory->save();
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
    $branch = $request->input('branch');
    $manufacturer = $request->input('manufacturer');
    $status = $request->input('status');
    $condition = $request->input('condition');
    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser); 

    $query = $accessData['assets']->with('category');

    if ($category) {
        $query->where('category_id', $category);
    }
    if ($branch) {
        $query->where('branch_id', $branch);
    }
    if($manufacturer){
          $query->where('manufacturer', $manufacturer);
    }
    if ($status) {
            $query->whereHas('assetsDetails', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

    if ($condition) {
        $query->whereHas('assetsDetails', function ($q) use ($condition) {
            $q->where('asset_condition', $condition);
        });
    }

    if ($sortBy === 'last_month') {
        $query->where('created_at', '>=', now()->subMonth());
    }else if ($sortBy === 'last_7_days') {
        $query->where('created_at', '>=', now()->subDays(7));
    } 
    if ($sortBy === 'ascending') {
        $query->orderBy('created_at', 'ASC');
    } else if ($sortBy === 'descending') {
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
        $authUser = $this->authUser();
        $assetId = $request->input('assetCondition_id'); 
        $conditions = (array) $request->input('condition');
        $statuses = (array) $request->input('status');  

        $assetDetails = AssetsDetails::where('asset_id', $assetId)->get(); 

        foreach ($assetDetails as $index => $detail) { 
            $condition = $conditions[$index] ?? null;
            $status = $statuses[$index] ?? null;  
            $previousCondition = $detail->asset_condition;
            $detail->asset_condition = $condition;
            $detail->status = $status; 
            if($status == 'Available'){
                $detail->deployed_to = null;
                $detail->deployed_date = null;
            }
            $detail->save(); 
            
            if ($condition === 'Defective' && $previousCondition !== 'Defective') {
                $conditionRemarks = $request->input('assets_settings_remarks_hidden_' . $detail->id);
                $assetDetailsRemarks = new AssetsDetailsRemarks();
                $assetDetailsRemarks->asset_detail_id = $detail->id;
                $assetDetailsRemarks->condition_remarks = $conditionRemarks;
                $assetDetailsRemarks->save();
            }
            $assetDetailsHistory = new AssetsDetailsHistory();
            $assetDetailsHistory->asset_detail_id = $detail->id;
            $assetDetailsHistory->item_no = $detail->order_no;
            $assetDetailsHistory->condition = $condition;
            $assetDetailsHistory->status =   $status;
            $assetDetailsHistory->process = 'update asset condition/status';
            $assetDetailsHistory->updated_by = $authUser->id;
            $assetDetailsHistory->updated_at = Carbon::now();
            $assetDetailsHistory->save();
        }
 
        $newConditions = (array) $request->input('new_condition');
        $newStatuses = (array) $request->input('new_status');
        $newOrderNos = (array) $request->input('new_order_no');

        foreach ($newConditions as $i => $newCondition) {
            $newStatus = $newStatuses[$i] ?? null;
            $newOrderNo = $newOrderNos[$i] ?? null;

            $assetDetails = AssetsDetails::create([
                'asset_id' => $assetId,
                'asset_condition' => $newCondition,
                'status' => $newStatus,
                'order_no' => $newOrderNo,
                'deployed_to' => null,
                'deployed_date' => null,
            ]);

            $assetDetailsHistory = new AssetsDetailsHistory();
            $assetDetailsHistory->asset_detail_id = $assetDetails->id;
            $assetDetailsHistory->item_no = $assetDetails->order_no;
            $assetDetailsHistory->condition = 'Brand New';
            $assetDetailsHistory->status = 'Available';
            $assetDetailsHistory->process = 'create asset';
            $assetDetailsHistory->created_by = $authUser->id;
            $assetDetailsHistory->created_at = Carbon::now();
            $assetDetailsHistory->save();
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
        $branches = $accessData['branches']->get();
        $assets = $accessData['assets']->with('category','assetsDetails')->get();
        
        $categories = $assets->pluck('category')->unique('id')->values();    
        $manufacturers = $assets->pluck('manufacturer')->unique()->values();
        return view('tenant.assetsmanagement.assets_settings', [ 
            'assets' => $assets,
            'categories' => $categories,
            'branches' => $branches,
            'manufacturers' => $manufacturers,
            'permission' => $permission
        ]);
   }

     public function assetsSettingsHistoryFilter(Request $request)
      {
        $authUser = $this->authUser();
        $tenantId = $authUser->tenant_id;
        $permission = PermissionHelper::get(50); 

        $category = $request->input('category');
        $sortBy = $request->input('sortBy');
        $branch = $request->input('branch');
        $manufacturer = $request->input('manufacturer'); 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser); 

        $query = $accessData['assetsHistory']->with('category');

        if ($category) {
            $query->where('category_id', $category);
        }
        if ($branch) {
            $query->where('branch_id', $branch);
        }
        if($manufacturer){
            $query->where('manufacturer', $manufacturer);
        } 
        if ($sortBy === 'last_month') {
            $query->where('created_at', '>=', now()->subMonth());
        }else if ($sortBy === 'last_7_days') {
            $query->where('created_at', '>=', now()->subDays(7));
        } 
        if ($sortBy === 'ascending') {
            $query->orderBy('created_at', 'ASC');
        } else if ($sortBy === 'descending') {
            $query->orderBy('created_at', 'DESC');
        }

        $assetsHistory = $query->get();

        $html = view('tenant.assetsmanagement.assets_settings_history_filter', compact('assetsHistory', 'permission'))->render();

        return response()->json([
            'status' => 'success',
            'html' => $html
        ]);
    } 



     public function assetsSettingsHistoryIndex(){

            $authUser = $this->authUser();
            $permission = PermissionHelper::get(50);
            $tenantId = $authUser->tenant_id ?? null; 
            $dataAccessController = new DataAccessController();
            $accessData = $dataAccessController->getAccessData($authUser);
            $assetsHistory = AssetsHistory::with('category','updatedBy.employmentDetail')->get();
            $branches = $accessData['branches']->get(); 
            $categories = $assetsHistory->pluck('category')->unique('id')->values();    
            $manufacturers = $assetsHistory->pluck('manufacturer')->unique()->values();
            return view('tenant.assetsmanagement.assets_settings_history', [ 
                'assetsHistory' => $assetsHistory, 
                'permission' => $permission,
                'categories' => $categories,
                'branches' => $branches,
                'manufacturers' => $manufacturers,
            ]);
    }
  public function  assetsSettingsStore(Request $request)
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
            'item_name'      => 'required|string|max:255',
            'quantity'       => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'description'    => 'nullable|string',
            'model'          => 'nullable|string',
            'manufacturer'   => 'nullable|string',
            'serial_number'  => 'nullable|string',
            'processor'      => 'nullable|string',
            'purchase_date'  => 'required|date', 
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
            DB::beginTransaction(); // ✅ Start transaction

            if ($request->category_id === 'new') {
                $category = Categories::firstOrCreate([
                    'name'   => $request->new_category_name,
                    'prefix' => $request->new_prefix,
                ]);
            } else {
                $category = Categories::find($request->category_id);
            }

            $asset = new Assets();
            $asset->description     = $request->description;
            $asset->name            = $request->asset_name;
            $asset->item_name       = $request->item_name;
            $asset->quantity        = $request->quantity;
            $asset->price           = $request->price;
            $asset->category_id     = $category ? $category->id : null;
            $asset->branch_id       = $authUser->employmentDetail->branch_id ?? null;
            $asset->deployment_date = Carbon::now();
            $asset->model           = $request->model;
            $asset->manufacturer    = $request->manufacturer;
            $asset->serial_number   = $request->serial_number;
            $asset->processor       = $request->processor;
            $asset->purchase_date   = $request->purchase_date;
            $asset->save();

            $assetsHistory = new AssetsHistory();
            $assetsHistory->asset_id        = $asset->id;
            $assetsHistory->name            = $asset->name;
            $assetsHistory->item_name       = $asset->item_name;
            $assetsHistory->description     = $asset->description;
            $assetsHistory->category_id     = $asset->category_id;
            $assetsHistory->branch_id       = $asset->branch_id;
            $assetsHistory->quantity        = $asset->quantity;
            $assetsHistory->price           = $asset->price;
            $assetsHistory->deployment_date = $asset->deployment_date;
            $assetsHistory->model           = $asset->model;
            $assetsHistory->manufacturer    = $asset->manufacturer;
            $assetsHistory->serial_number   = $asset->serial_number;
            $assetsHistory->processor       = $asset->processor;
            $assetsHistory->purchase_date   = $asset->purchase_date;
            $assetsHistory->process         = 'create asset';
            $assetsHistory->created_by      = $authUser->id;
            $assetsHistory->save();

            for ($i = 0; $i < $request->quantity; $i++) {
                $assetDetails = new AssetsDetails();
                $assetDetails->asset_id       = $asset->id;
                $assetDetails->order_no       = $i + 1;
                $assetDetails->asset_condition= 'Brand New';
                $assetDetails->status         = 'Available';
                $assetDetails->save();

                $assetDetailsHistory = new AssetsDetailsHistory();
                $assetDetailsHistory->asset_detail_id = $assetDetails->id;
                $assetDetailsHistory->item_no         = $assetDetails->order_no;
                $assetDetailsHistory->condition       = 'Brand New';
                $assetDetailsHistory->status          = 'Available';
                $assetDetailsHistory->process         = 'create asset';
                $assetDetailsHistory->created_by      = $authUser->id;
                $assetDetailsHistory->created_at      = Carbon::now();
                $assetDetailsHistory->save();
            }

            DB::commit();  

            return redirect()->back()->with('success', 'Asset added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();  

            Log::error('Error saving asset: ' . $e->getMessage());

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
        'edit_item_name' => 'required|string|max:255', 
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
                'prefix' => $request->edit_new_prefix
            ]);
        } else {
            $category = Categories::find($request->edit_category_id);
        }
 

        $asset = Assets::find($request->edit_id);
        $asset->description = $request->edit_description;
        $asset->name = $request->edit_asset_name; 
        $asset->item_name = $request->edit_item_name;
        $asset->price = $request->edit_price;  
        $asset->category_id = $category ? $category->id : null;
        $asset->branch_id = $authUser->employmentDetail->branch_id ?? null; 
        $asset->model = $request->edit_model;
        $asset->manufacturer = $request->edit_manufacturer;
        $asset->serial_number = $request->edit_serial_number;
        $asset->processor = $request->edit_processor;
        $asset->purchase_date = $request->edit_purchase_date;
        $asset->save(); 

        $assetsHistory = new AssetsHistory();
        $assetsHistory->asset_id        = $asset->id;
        $assetsHistory->name            = $asset->name;
        $assetsHistory->item_name       = $asset->item_name;
        $assetsHistory->description     = $asset->description;
        $assetsHistory->category_id     = $asset->category_id;
        $assetsHistory->branch_id       = $asset->branch_id;
        $assetsHistory->quantity        = $asset->quantity;
        $assetsHistory->price           = $asset->price;
        $assetsHistory->deployment_date = $asset->deployment_date;
        $assetsHistory->model           = $asset->model;
        $assetsHistory->manufacturer    = $asset->manufacturer;
        $assetsHistory->serial_number   = $asset->serial_number;
        $assetsHistory->processor       = $asset->processor;
        $assetsHistory->purchase_date   = $asset->purchase_date;
        $assetsHistory->process         = 'update asset';
        $assetsHistory->updated_by      = $authUser->id;
        $assetsHistory->save();
                
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

      $authUser = $this->authUser();
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

    $assetsHistory = new AssetsHistory();
    $assetsHistory->asset_id        = $asset->id;
    $assetsHistory->name            = $asset->name;
    $assetsHistory->item_name       = $asset->item_name;
    $assetsHistory->description     = $asset->description;
    $assetsHistory->category_id     = $asset->category_id;
    $assetsHistory->branch_id       = $asset->branch_id;
    $assetsHistory->quantity        = $asset->quantity;
    $assetsHistory->price           = $asset->price;
    $assetsHistory->deployment_date = $asset->deployment_date;
    $assetsHistory->model           = $asset->model;
    $assetsHistory->manufacturer    = $asset->manufacturer;
    $assetsHistory->serial_number   = $asset->serial_number;
    $assetsHistory->processor       = $asset->processor;
    $assetsHistory->process         = 'delete asset';
    $assetsHistory->updated_by      = $authUser->id;
    $assetsHistory->save();

    $asset->delete();

    return response()->json(['status' => 'success']);
}


  
    public function exportAssetPDF($id,$user_id)
    {
        $asset = AssetsDetails::with('assets.category')->findOrFail($id);
        $user = EmploymentDetail::with('branch','department','designation')
            ->where('user_id', $user_id)
            ->first(); 
        if($asset->assets->category->name == 'Laptop'){
              return Pdf::view('tenant.assetsmanagement.pdf.laptop', compact('asset','user'))
                    ->withBrowsershot(function (Browsershot $browsershot) {
                        $browsershot->setOption('executablePath', 'C:/Program Files/Google/Chrome/Application/chrome.exe');
                    })
                    ->format('letter')
                    ->inline(); 
         }else if($asset->assets->category->name == 'Mobile Phone'){
              return Pdf::view('tenant.assetsmanagement.pdf.mobile_phone', compact('asset','user'))
                    ->withBrowsershot(function (Browsershot $browsershot) {
                        $browsershot->setOption('executablePath', 'C:/Program Files/Google/Chrome/Application/chrome.exe');
                    })
                    ->format('letter')
                    ->inline(); 
         }else if($asset->assets->category->name == 'Motorcycle'){
              return Pdf::view('tenant.assetsmanagement.pdf.motorcycle', compact('asset','user'))
                    ->withBrowsershot(function (Browsershot $browsershot) {
                        $browsershot->setOption('executablePath', 'C:/Program Files/Google/Chrome/Application/chrome.exe');
                    })
                    ->format('letter')
                    ->inline(); 
         }else{
            return Pdf::view('tenant.assetsmanagement.pdf.general_provision', compact('asset','user'))
                    ->withBrowsershot(function (Browsershot $browsershot) {
                        $browsershot->setOption('executablePath', 'C:/Program Files/Google/Chrome/Application/chrome.exe');
                    })
                    ->format('letter')
                    ->inline(); 
        }
    }
}
