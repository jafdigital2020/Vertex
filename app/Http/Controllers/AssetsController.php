<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Assets;
use App\Models\Categories;
use Illuminate\Http\Request;
use App\Models\EmployeeAssets;
use App\Helpers\PermissionHelper;
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
 
        $query  = $accessData['employees']->with('employeeAssets.asset.category');

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
      public function list()
    {
        $authUser = $this->authUser();
        $dataAccessController = new DataAccessController();
        $assetsQuery = $dataAccessController->getAccessData($authUser)['assets']; // probably a query builder

        $assets = $assetsQuery->with('category')->get(); // get collection with eager loaded category

        $mapped = $assets->map(function ($asset) {
            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'category' => $asset->category->name ?? null,
            ];
        });

        return response()->json($mapped);
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
        $users  = $accessData['employees']->with('employeeAssets.asset.category')->get(); 
        return view('tenant.assetsmanagement.employee_assets', [ 
            'users' => $users,
            'branches' => $branches, 
            'departments' => $departments ,
            'designations' => $designations,
            'permission' => $permission
        ]);
   }
 

  public function employeeAssetsStore(Request $request)
{
    $userId = $request->input('employee-assets-id');

    // Validate input
    $request->validate([
        'employee-assets-id' => 'required|exists:users,id',
        'assets'   => 'nullable|array',
        'quantity' => 'nullable|array',
        'price'    => 'nullable|array',
        'status'   => 'nullable|array',
        'total'    => 'nullable|array',
    ]);

    // Always delete old entries first
    EmployeeAssets::where('user_id', $userId)->delete();

    // If all are empty or missing, skip saving
    if (
        empty($request->assets) &&
        empty($request->quantity) &&
        empty($request->price) &&
        empty($request->status) &&
        empty($request->total)
    ) {
        return back()->with('success', 'Assets cleared successfully.');
    }

    // Save new assets if present
    foreach ($request->assets as $index => $assetId) {
        EmployeeAssets::create([
            'user_id'     => $userId,
            'asset_id'    => $assetId,
            'quantity'    => $request->quantity[$index] ?? 1,
            'price'       => $request->price[$index] ?? 0,
            'total'       => $request->total[$index] ?? 0,
            'status'      => (isset($request->status[$index]) && $request->status[$index] === 'active') 
                            ? 'assigned' 
                            : ($request->status[$index] ?? 'assigned'),
            'assigned_at' => now(),
        ]);
    }

    return back()->with('success', 'Assets saved successfully.');
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
   public function assetsSettingsIndex(){

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(50);
        $tenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $assets = $accessData['assets']->with('category')->get();
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
    $tenantId = $authUser->tenant_id ?? null; 
    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser);

    $request->validate([
        'name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0', 
        'description' => 'nullable|string',
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
        $asset->save(); 

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
    $tenantId = $authUser->tenant_id ?? null; 
    $dataAccessController = new DataAccessController();
    $accessData = $dataAccessController->getAccessData($authUser);

    $request->validate([
        'edit_name' => 'required|string|max:255',
        'edit_quantity' => 'required|integer|min:1',
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
        $asset->quantity = $request->edit_quantity;
        $asset->price = $request->edit_price; 
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
    $asset = Assets::find($request->id);
    Log::info('yeaa');
    if (!$asset) {
        return response()->json(['status' => 'error', 'message' => 'Asset not found.'], 404);
    }

    $asset->delete();

    return response()->json(['status' => 'success']);
}
}
