<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'name' => 'JAF Digital Group Inc.',
                'location' => 'Main Office',
                'contact_number' => '+63 123 456 7890',
                'branch_type' => 'main',
                'sss_contribution_type' => 'system',
                'philhealth_contribution_type' => 'system',
                'pagibig_contribution_type' => 'system',
                'withholding_tax_type' => 'system',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
