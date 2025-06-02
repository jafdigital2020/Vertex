<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu')->insert([
        [
            'menu_name' => 'MAIN MENU',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'menu_name' => 'HRM',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        [
            'menu_name' => 'FINANCES & ACCOUNTS',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
           [
            'menu_name' => 'ADMINISTRATION',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
        ]);
    }
}
