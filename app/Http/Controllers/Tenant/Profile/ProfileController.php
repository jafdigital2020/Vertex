<?php

namespace App\Http\Controllers\Tenant\Profile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // Profile Index
    public function profileIndex(Request $request)
    {
        // Auth User
        $authUser = Auth::user();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Profile index',
                'data' => [
                    'users' => $authUser,
                ]
            ]);
        }

        return view('tenant.profile.profile', [
            'users' => $authUser,
        ]);
    }
}
