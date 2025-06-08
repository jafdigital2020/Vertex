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
        [
            'tenant_name' => 'Jollibee', 
            'tenant_code' => 'JOLI',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ], 
        [
            'tenant_name' => 'Mc Donalds', 
            'tenant_code' => 'MCDO',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],   
        [
            'tenant_name' => 'Starbucks', 
            'tenant_code' => 'STAR',  
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],  
    ]);
    }
}
