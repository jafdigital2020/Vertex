<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContractTemplate;

class ContractTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Probationary Employment Contract 2024',
                'contract_type' => 'Probationary',
                'pdf_template_path' => 'templates/contracts/01 Rev. 04 Probationary Employment 2024.pdf',
                'content' => null,
                'is_active' => true,
                'tenant_id' => null, // Global template
            ],
            [
                'name' => 'Regular Employment Contract 2024',
                'contract_type' => 'Regular',
                'pdf_template_path' => 'templates/contracts/02 Rev. 02 Regular Employment 2024.pdf',
                'content' => null,
                'is_active' => true,
                'tenant_id' => null, // Global template
            ],
        ];

        foreach ($templates as $template) {
            ContractTemplate::updateOrCreate(
                ['pdf_template_path' => $template['pdf_template_path']],
                $template
            );
        }

        $this->command->info('Contract templates seeded successfully!');
    }
}
