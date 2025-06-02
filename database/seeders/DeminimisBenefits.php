<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DeminimisBenefits extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/deminimis_benefits.csv');
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            DB::table('deminimis_benefits')->insert([
                'name' => $record['name'],
                'maximum_amount' => $record['maximum_amount'],
                'frequency' => $record['frequency'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
