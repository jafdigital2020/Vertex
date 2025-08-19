<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\User;

class AffiliateBranchController extends Controller
{
    public function createAffiliateIndex()
    {
        return view('affiliate.register');
    }
    //

    
}