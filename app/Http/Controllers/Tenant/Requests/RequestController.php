<?php

namespace App\Http\Controllers\Tenant\Requests;

use App\Http\Controllers\Controller;

class RequestController extends Controller
{
    /**
     * Show the loan request page
     */
    public function loanIndex()
    {
        return view('tenant.requests.loan.index');
    }

    /**
     * Show the budget request page
     */
    public function budgetIndex()
    {
        return view('tenant.requests.budget.index');
    }

    /**
     * Show the asset request page
     */
    public function assetIndex()
    {
        return view('tenant.requests.asset.index');
    }

    /**
     * Show the HMO request page
     */
    public function hmoIndex()
    {
        return view('tenant.requests.hmo.index');
    }

    /**
     * Show the COE request page
     */
    public function coeIndex()
    {
        return view('tenant.requests.coe.index');
    }
}