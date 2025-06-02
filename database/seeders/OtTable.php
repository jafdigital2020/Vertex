<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OtTable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/ot_table.csv');
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            DB::table('ot_tables')->insert([
                'type' => $record['type'],
                'normal' => $record['normal'],
                'overtime' => $record['overtime'],
                'night_differential' => $record['night_differential'],
                'night_differential_overtime' => $record['night_differential_overtime'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
