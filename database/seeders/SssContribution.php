<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SssContribution extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('seeders/data/sss_contributions.csv');
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            DB::table('sss_contribution_tables')->insert([
                'range_from' => $record['range_from'],
                'range_to' => $record['range_to'],
                'monthly_salary_credit' => $record['monthly_salary_credit'],
                'employer_regular_ss' => $record['employer_regular_ss'],
                'employer_mpf' => $record['employer_mpf'],
                'employer_ec' => $record['employer_ec'],
                'employer_total' => $record['employer_total'],
                'employee_regular_ss' => $record['employee_regular_ss'],
                'employee_mpf' => $record['employee_mpf'],
                'employee_total' => $record['employee_total'],
                'total_contribution' => $record['total_contribution'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
