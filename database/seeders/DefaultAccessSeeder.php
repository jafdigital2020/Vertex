<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaultAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('default_access')->insert([
            [
                'submodule_ids'   => '1,2,8,9,10,11,14,15,16,17,19,20,24,25,26,27,30,45,54,55,56,57',
                'effectivity_date' => Carbon::now()->toDateString(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ], 
        ]);
    }
}
