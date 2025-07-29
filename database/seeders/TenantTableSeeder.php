<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenantTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       DB::table('tenants')->insert([
        [
            'tenant_name' => 'JAF Digital', 
            'tenant_code' => 'JDGI',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   

    ]);
    }
}
