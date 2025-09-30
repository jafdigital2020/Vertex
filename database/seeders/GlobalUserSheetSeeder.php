<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GlobalUserSheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
   // Load Excel file
    $path = database_path('seeders/data/affiliate.csv');
    $spreadsheet = IOFactory::load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    // First row is header
    $header = array_shift($rows);

    foreach ($rows as $row) {
        // Match row values to header keys
        $data = array_combine(array_values($header), array_values($row));

        // Skip if row is empty
        if (empty($data['tenant_code']) || empty($data['username']) || empty($data['email'])) {
            continue;
        }

        // Insert tenant if not exists
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

        // Skip if global user already exists (check by email OR username)
        $existingUser = DB::table('global_users')
            ->where('email', $data['email'])
            ->orWhere('username', $data['username'])
            ->first();

        if ($existingUser) {
            continue; // Skip this row
        }

        // Insert global user
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
}
}