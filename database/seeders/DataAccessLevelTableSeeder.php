<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DataAccessLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          DB::table('data_access_level')->insert([
        [
            'access_name' => 'Organization-Wide Access',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'access_name' => 'Branch-Level Access',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'access_name' => 'Department-Level Access',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'access_name' => 'Personal Access Only',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
    ]);
    }
}
