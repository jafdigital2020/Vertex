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
   public function packageGrid()
   {
        return view('superadmin.packages.grid');
   }
}
