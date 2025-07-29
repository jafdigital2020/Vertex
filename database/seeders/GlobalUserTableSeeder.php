<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GlobalUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('global_users')->insert([
        [
            'username' => 'joli_admin',
            'email' => 'admin@jolibee.co',
            'password' => Hash::make('12345678'),
            'global_role_id' => 2,
            'tenant_id' => 2,
            'remember_token' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]
    ]);
    }
}
