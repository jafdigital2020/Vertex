<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
}
