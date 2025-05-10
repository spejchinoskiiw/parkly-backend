<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'stefan.pejchinoski@iwconnect.com',
            'role' => 'admin',
        ]);

        // Create manager user
        User::create([
            'name' => 'Manager User',
            'email' => 'testi@iwconnect.com',
            'role' => 'manager',
        ]);

        // Create regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'test2@iwconnect.com',
            'role' => 'user',
        ]);
    }
} 