<?php

namespace Database\Seeders;

use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WithholdingTaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $file = database_path('seeders/data/withholding_tax.csv');

        $csv = Reader::createFromPath($file, 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv as $record) {
            DB::table('withholding_tax_tables')->insert([
                'frequency' => $record['frequency'],
                'range_from' => $record['range_from'],
                'range_to' => $record['range_to'] ?: null,
                'fix' => $record['fix'],
                'rate' => $record['rate'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
