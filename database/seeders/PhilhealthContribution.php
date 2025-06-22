<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PhilhealthContribution extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        $path = database_path('seeders/data/philhealth_contributions.csv');
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            DB::table('philhealth_contributions')->insert([
                'year' => $record['year'],
                'min_salary' => $record['min_salary'],
                'max_salary' => $record['max_salary'],
                'monthly_premium' => $record['monthly_premium'],
                'employee_share' => $record['employee_share'],
                'employer_share' => $record['employer_share'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
