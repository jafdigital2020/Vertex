<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DefaultAccessSeeder4 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('default_access')->insert([
            [
                'submodule_ids'   => '1,2,8,9,10,11,12,13,14,15,16,17,18,19,20,21,24,25,26,27,30,43,45,46,47,49,50,51,52,53,54,55,56,57',
                'effectivity_date' => Carbon::now()->toDateString(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }
}
