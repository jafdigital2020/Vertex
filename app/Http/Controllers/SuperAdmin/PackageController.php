<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Package;
use App\Models\Package_Feature;
use App\Models\Package_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
 

class PackageController extends Controller
{
   public function packageTable()
   {      
        $packages = Package::orderBy('id')->get(); 
        $packageType = Package_Type::orderBy('id')->get();
      
        return view('superadmin.packages.table',['packages' => $packages, 'package_type' => $packageType]);
   }
   public function getPackageDetails(Request $request) {
       $data = $request->all(); 
       
       $validator = Validator::make($data,[
          'package_id' => 'required'
       ]);

       if($validator->fails()){
          return response()->json(['status' => 'error', 'message' => 'Package ID is required']);
       } 
       $id = $data['package_id'];
       $package = Package::find($id); 
       $package_features = Package_Feature::with('feature_det')->get();
       
       return response()->json(['status' => 'success', 'message' => 'Login successful','package' => $package,'package_features' => $package_features ]);
   }

   public function editPackage(Request $request){

      $data = $request->all(); 
       
      $edit_feature_ids = collect($request->all())
         ->filter(function ($value, $key) {
            return str_starts_with($key, 'edit_feature_id');
         })
         ->mapWithKeys(function ($value, $key) { 
            $id = (int) str_replace('edit_feature_id', '', $key);
            return [$id => $value];
         })
         ->map(function ($value, $key) {
            return "$key-$value";
         })
         ->implode(','); 
      
      $package = Package::find($data['edit_package_id']);
      $package->package_name = $data['edit_package_name'];
      $package->package_type_id = $data['edit_package_type'];
      $package->package_feature_ids = $edit_feature_ids;
      $package->employee_limit = isset($data['edit_employee_limit']) ? $data['edit_employee_limit'] : 0 ;
      $package->monthly_pricing = isset($data['edit_monthly_pricing']) ? $data['edit_monthly_pricing'] : 0 ;
      $package->yearly_pricing = isset($data['edit_yearly_pricing']) ? $data['edit_yearly_pricing']: 0; 
      $package->status = isset($data['edit_status']) ? 1 : 0 ;  
      $package->save();

      return redirect()->back()->with('success','Package edited successfully');
   }
   public function packageGrid()
   {
        return view('superadmin.packages.grid');
   }
}
