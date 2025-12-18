<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupTestingEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:testing {--fresh : Fresh migrate before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up a complete testing environment with sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Setting up Testing Environment for Vertex...');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('⚠️  This will DESTROY all existing data and create fresh tables!');
            
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            $this->info('🗄️  Running fresh migrations...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            $this->info('✅ Fresh migrations completed');
            $this->newLine();
        }

        $this->info('📋 Running testing seeders...');
        Artisan::call('db:seed', [
            '--class' => 'DatabaseTestingSeeder',
            '--force' => true
        ]);

        $this->newLine();
        $this->info('🔧 Clearing caches...');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        $this->newLine();
        $this->info('✅ Testing environment setup completed successfully!');
        
        return Command::SUCCESS;
    }
}