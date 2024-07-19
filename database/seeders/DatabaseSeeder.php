<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'), // Replace 'adminpassword' with your desired password
            'role' => 'admin',
            'status' => 'approved', // Assuming admin accounts are auto-approved
        ]);

        // Create other users for testing
        User::factory()->count(5)->create([
            'role' => 'client',
            'status' => 'pending',
        ]);

        User::factory()->count(5)->create([
            'role' => 'contractor',
            'status' => 'pending',
        ]);

        User::factory()->count(5)->create([
            'role' => 'supplier',
            'status' => 'pending',
        ]);

        User::factory()->count(5)->create([
            'role' => 'project manager',
            'status' => 'pending',
        ]);
    }
}
