<?php

namespace App\Http\Controllers\Tenant\Branch;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BranchController extends Controller
{
    public function branchIndex(Request $request)
    {
        $branches = Branch::all();

        if($request->wantsJson())
        {
            return response()->json([
                'message' => 'This is the branch index endpoint.',
                'status' => 'success',
                'branches' => $branches,
            ]);
        }

        return view('tenant.branch.branch-grid', [
            'branches' => $branches,
        ]);
    }
}
