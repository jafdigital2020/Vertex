<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AffiliateAccountController extends Controller
{
    public function showUploadForm()
    {
        return view('affiliate.account.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('csv_file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = array_combine(array_values($header), array_values($row));

            if (empty($data['tenant_code']) || empty($data['username']) || empty($data['email'])) {
                continue;
            }

            $tenant = DB::table('tenants')->where('tenant_code', $data['tenant_code'])->first();
            if (!$tenant) {
                $tenantId = DB::table('tenants')->insertGetId([
                    'tenant_name' => $data['tenant_name'],
                    'tenant_code' => $data['tenant_code'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                $tenantId = $tenant->id;
            }

            $existingUser = DB::table('global_users')
                ->where('email', $data['email'])
                ->orWhere('username', $data['username'])
                ->first();

            if ($existingUser) {
                continue;
            }

            DB::table('global_users')->insert([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'global_role_id' => $data['global_role_id'] ?? 2,
                'tenant_id' => $tenantId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return back()->with('success', 'Affiliate accounts uploaded successfully.');
    }

    public function registerAffiliateAccount(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'username'    => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:global_users', // Ensure email is unique
            'password'    => 'required|string|min:8',
            'tenant_code' => 'required|string|max:255',
            'tenant_name' => 'required|string|max:255',
        ]);

        try {
            // 1) Check if tenant exists
            $tenant = DB::table('tenants')->where('tenant_code', $request->tenant_code)->first();

            if (!$tenant) {
                // Tenant doesn't exist, so create a new tenant
                $tenantId = DB::table('tenants')->insertGetId([
                    'tenant_code' => $request->tenant_code,
                    'tenant_name' => $request->tenant_name,
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]);

                // Trigger external Tenant API
                try {
                    $tenantPayload = [
                        'tenant_code' => $request->tenant_code,
                        'tenant_name' => $request->tenant_name,
                        'tenant_url'  => "https://payroll.timora.ph",
                        'active'      => true
                    ];

                    $tenantApiResponse = Http::post('https://auth.timora.ph/api/tenants', $tenantPayload);

                    Log::info('Tenant API Response from registerAffiliateAccount', [
                        'status' => $tenantApiResponse->status(),
                        'body'   => $tenantApiResponse->body(),
                        'json'   => $tenantApiResponse->json(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to call Tenant API from registerAffiliateAccount: ' . $e->getMessage());
                }
            } else {
                // Optional: Check if tenant_name matches, if not, return conflict error
                if ($tenant->tenant_name !== $request->tenant_name) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Tenant code already exists with a different tenant name.',
                    ], 409);  // Return 409 Conflict if tenant name doesn't match
                }
                $tenantId = $tenant->id;
            }

            // 2) Check if a global user with the same email or username exists
            $existingUser = DB::table('global_users')
                ->where('email', $request->email)
                ->orWhere('username', $request->username)
                ->first();

            if ($existingUser) {
                // Return conflict error if the user already exists
                return response()->json([
                    'status'  => 'error',
                    'message' => 'A user with this email or username already exists.',
                ], 409); // Return 409 Conflict for existing user
            }

            // 3) Start the user creation process in a transaction
            DB::transaction(function () use ($request, $tenantId) {
                // 3.1) Create the affiliate user
                DB::table('global_users')->insert([
                    'username'       => $request->username,
                    'email'          => $request->email,
                    'password'       => Hash::make($request->password),
                    'global_role_id' => 2,  // Default role ID for affiliate user
                    'tenant_id'      => $tenantId,
                    'created_at'     => Carbon::now(),
                    'updated_at'     => Carbon::now(),
                ]);
            });

            // 4) If everything goes well, return success response for user registration
            return response()->json([
                'status'  => 'success',
                'message' => 'Affiliate account registered successfully.',
            ], 201); // Use 201 Created for successful creation

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed
            if ($request->expectsJson() || $request->isJson() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422); // 422 Unprocessable Entity for validation errors
            }
            // Fallback: always return JSON for API
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Catch any other errors
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() ?: 'An unexpected error occurred.',
            ], 500); // 500 for server error
        }
    }
    
}
