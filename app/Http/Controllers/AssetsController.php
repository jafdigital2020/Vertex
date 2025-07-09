<?php

namespace App\Http\Controllers;

use App\Models\Assets;
use App\Models\Categories;
use Illuminate\Http\Request;
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

   public function employeeAssetsIndex(){

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(50);
        $tenantId = $authUser->tenant_id ?? null; 
        $dataAccessController = new DataAccessController();
        $accessData = $dataAccessController->getAccessData($authUser);
        $branches = $accessData['branches']->get();
        $departments = $accessData['departments']->get();
        $designations = $accessData['designations']->get();
        $users  = $accessData['employees']->get(); 
        return view('tenant.assetsmanagement.employee_assets', [
            'departments' => $departments ,
            'designations' => $designations,
            'users' => $users,
            'branches' => $branches, 
            'permission' => $permission
        ]);
   }

   public function assetsSettingsIndex(){

        $authUser = $this->authUser();
        $permission = PermissionHelper::get(51);
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
    $permission = PermissionHelper::get(51);
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
        $asset->branch_id = $authUser->employmentDetail->branch_id ?? 2; 
        $asset->save(); 

        return redirect()->back()->with('success', 'Asset added successfully.');

    } catch (\Exception $e) { 
        Log::error('Error saving asset: '.$e->getMessage());

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Failed to add asset. Please try again later.']);
    }
}

}
