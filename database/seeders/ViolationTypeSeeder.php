<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ViolationTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('violation_types')->insert([
            ['name' => 'Verbal Reprimand'],
            ['name' => 'Written Reprimand'],
            ['name' => 'Suspension'],
            ['name' => 'Termination'],
        ]);
    }
}
