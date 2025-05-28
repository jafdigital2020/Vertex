<?php

namespace App\Http\Controllers\Tenant;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    // User Index
    public function userIndex(Request $request)
    {
        $users = User::all();
        $roles = Role::all();

        // Check if the request expects JSON (API)
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'users' => $users,
                'roles' => $roles,
            ]);
        }

        // Otherwise, return the view (Web)
        return view('tenant.usermanagement.user', compact('users', 'roles'));
    }

    // Roles Index
    public function roleIndex()
    {
        $roles = Role::all();

        return view('tenant.usermanagement.role', compact('roles'));
    }

    // Roles API
    public function roleStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        try {
            if (!Auth::check()) {
                Log::error('Unauthorized access attempt: User not authenticated');
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // ✅ Automatically determine guard name (for web OR API requests)
            $guardName = $request->expectsJson() ? 'sanctum' : 'web';

            // ✅ Attempt to create role
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $guardName,
            ]);

            // ✅ If request is from an API (Flutter), return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role created successfully!',
                    'role' => $role
                ], 201);
            }

            // ✅ If request is from a Web app, redirect to the roles page with success message
            return redirect()->back()->with('success', 'Role created successfully!');
        } catch (\Exception $e) {
            // ✅ Log error message
            Log::error('Error creating role', ['error' => $e->getMessage()]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create role',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }
}
