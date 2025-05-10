<?php

use App\Console\Commands\SeedQaEnvironment;
use Database\Seeders\QaTestingSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register QA seeding command as a shortcut
Artisan::command('qa:seed {--force}', function () {
    $force = $this->option('force');
    
    if ($force || $this->confirm('This will delete all existing data and seed QA test data. Are you sure you want to continue?')) {
        $this->info('Starting QA environment seeding...');
        
        // Run the QA testing seeder
        $this->call('db:seed', [
            '--class' => QaTestingSeeder::class,
            '--force' => true,
        ]);
        
        $this->info('QA environment seeded successfully!');
    } else {
        $this->info('Operation cancelled.');
    }
})->purpose('Seed the database with QA testing data');
