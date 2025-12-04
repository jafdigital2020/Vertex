<?php

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CandidateAuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('career.auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:candidates,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Generate unique candidate code
        $candidateCode = 'CND-' . strtoupper(Str::random(8));
        while (Candidate::where('candidate_code', $candidateCode)->exists()) {
            $candidateCode = 'CND-' . strtoupper(Str::random(8));
        }

        $candidate = Candidate::create([
            'candidate_code' => $candidateCode,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'status' => 'new',
            'source_type' => 'self_registration',
            'is_active' => true
        ]);

        // Log the candidate in
        Auth::guard('candidate')->login($candidate);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful! You are now logged in.',
                'redirect' => route('career.index')
            ]);
        }

        return redirect()->route('career.index')
            ->with('success', 'Registration successful! You are now logged in.');
    }

    public function showLoginForm()
    {
        return view('career.auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::guard('candidate')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $request->get('redirect', route('career.index'))
                ]);
            }

            return redirect()->intended(route('career.index'))
                ->with('success', 'Welcome back!');
        }

        $error = ['email' => 'The provided credentials do not match our records.'];
        
        if ($request->expectsJson()) {
            return response()->json(['errors' => $error], 422);
        }

        return back()->withErrors($error)->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::guard('candidate')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully!',
                'redirect' => route('career.index')
            ]);
        }

        return redirect()->route('career.index')
            ->with('success', 'You have been logged out successfully!');
    }
}