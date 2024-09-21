<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Project;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Fetch role IDs
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $clientRoleId = DB::table('roles')->where('name', 'client')->value('id');
        $contractorRoleId = DB::table('roles')->where('name', 'contractor')->value('id');
        $supplierRoleId = DB::table('roles')->where('name', 'supplier')->value('id');
        $projectManagerRoleId = DB::table('roles')->where('name', 'project_manager')->value('id');

        // Create an admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('123'),
            'role_id' => $adminRoleId,
            'status' => 'approved',
        ]);

        // Create a project manager
        $projectManager = User::create([
            'name' => 'Project Manager',
            'email' => 'project@example.com',
            'password' => Hash::make('123'),
            'role_id' => $projectManagerRoleId,
            'status' => 'approved',
        ]);

        // Create a contractor
        User::create([
            'name' => 'Contractor',
            'email' => 'contractor@example.com',
            'password' => Hash::make('123'),
            'role_id' => $contractorRoleId,
            'status' => 'approved',
        ]);

        // Create additional users
        User::create([
            'name' => 'Supplier',
            'email' => 'supplier@example.com',
            'password' => Hash::make('123'),
            'role_id' => $supplierRoleId,
            'status' => 'approved',
        ]);

        User::create([
            'name' => 'Client',
            'email' => 'client@example.com',
            'password' => Hash::make('123'),
            'role_id' => $clientRoleId,
            'status' => 'approved',
        ]);

        // Create sample projects (without contractors invited, not yet approved)
        Project::create([
            'project_manager_id' => $projectManager->id,
            'name' => 'Sample Project 1',
            'description' => 'This is the first sample project.',
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'total_budget' => 50000.00,
            'budget_remaining' => 50000.00,
            'location' => 'New York',
            'status' => 'pending',
        ]);

        Project::create([
            'project_manager_id' => $projectManager->id,
            'name' => 'Sample Project 2',
            'description' => 'This is the second sample project.',
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'total_budget' => 100000.00,
            'budget_remaining' => 100000.00,
            'location' => 'Los Angeles',
            'status' => 'pending',
        ]);
    }
}
