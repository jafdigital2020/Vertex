<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Package;
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
       
       return response()->json(['status' => 'success', 'message' => 'Login successful']);
   }
   public function packageGrid()
   {
        return view('superadmin.packages.grid');
   }
}
