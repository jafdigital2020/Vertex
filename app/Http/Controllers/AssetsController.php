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
 
        try {
            DB::beginTransaction(); 
            if (!empty($assets_details_ids) && is_array($assets_details_ids)) {
                foreach ($assets_details_ids as $asset_id) { 
                    $asset_details = AssetsDetails::find($asset_id);

                    if ($asset_details) { 
                        $asset_details->status = 'Deployed';
                        $asset_details->deployed_to = $employee_id;
                        $asset_details->deployed_date = Carbon::now();
                        $asset_details->save();
 
                    }  
                }
            }
 
            foreach ($request->all() as $key => $value) {
                if (preg_match('/^condition(\d+)$/', $key, $matches)) {
                    $assetId = $matches[1];
                    $condition = $value;
                    $status = $request->input('status' . $assetId);
 
                    AssetsDetails::where('id', $assetId)
                        ->update([
                            'asset_condition' => $condition,
                            'status' => $status,
                        ]);
 
                }
            }
 
            if (empty($logData['new_assets']) && empty($logData['old_assets'])) { 
                return back()->with('warning', 'No assets were added or updated.');
            } 

            DB::commit(); 

            return back()->with('success', 'Assets changes saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Asset assignment failed. Transaction rolled back.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Something went wrong while assigning assets. Please try again.');
        }
    }
  
    public function getEmployeeAssets($id)
    {
        $assets = AssetsDetails::with(['assets.category'])
            ->where('deployed_to', $id)
            ->get();

        return response()->json(['data' => $assets]);
    }

  public function assetsSettingsFilter(Request $request)
{
    $authUser = $this->authUser();
    $tenantId = $authUser->tenant_id;
    $permission = PermissionHelper::get(50); 

    $status = $request->input('status');
    $sortBy = $request->input('sortBy');

    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser); 

    $query = $accessData['assets']->with('category');

    if ($status) {
        $query->where('status', $status);
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
            $detail->save();
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
        'edit_quantity' => 'required|integer|min:1',
        'edit_price' => 'required|numeric|min:0', 
        'edit_description' => 'nullable|string',
        'edit_status' => 'required|in:active,broken,maintenance,retired',
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
        $asset->quantity = $request->edit_quantity;
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

}
