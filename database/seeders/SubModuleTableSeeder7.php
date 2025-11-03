<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubModuleTableSeeder7 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void

    {
        DB::table('sub_modules')->insert([
            [
                'id' => 61,
                'sub_module_name' => 'Suspension Employee',
                'module_id' => 4,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
