<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PackageController extends Controller
{
   public function packageTable()
   {
        return view('superadmin.packages.table');
   }

   public function packageGrid()
   {
        return view('superadmin.packages.grid');
   }
}
