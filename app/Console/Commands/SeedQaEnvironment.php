<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\QaTestingSeeder;
use Illuminate\Console\Command;

class SeedQaEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-qa {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with QA testing data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('force') || $this->confirm('This will delete all existing data and seed QA test data. Are you sure you want to continue?')) {
            $this->info('Starting QA environment seeding...');
            
            // Run the QA testing seeder
            $this->call('db:seed', [
                '--class' => QaTestingSeeder::class,
                '--force' => true,
            ]);
            
            $this->info('QA environment seeded successfully!');
            
            return Command::SUCCESS;
        }
        
        $this->info('Operation cancelled.');
        return Command::FAILURE;
    }
} 