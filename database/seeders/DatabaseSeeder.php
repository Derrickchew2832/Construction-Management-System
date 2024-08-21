<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
            'password' => Hash::make('adminpassword'), // Replace 'adminpassword' with your desired password
            'role_id' => $adminRoleId,
            'status' => 'approved', // Assuming admin accounts are auto-approved
        ]);

        User::create([
            'name' => 'Project',
            'email' => 'project@example.com',
            'password' => Hash::make('123456'), // Replace 'adminpassword' with your desired password
            'role_id' => $projectManagerRoleId,
            'status' => 'approved', // Assuming admin accounts are auto-approved
        ]);

        User::create([
            'name' => 'Contractor',
            'email' => 'contractor@example.com',
            'password' => Hash::make('123456'), // Replace 'adminpassword' with your desired password
            'role_id' => $contractorRoleId,
            'status' => 'approved', // Assuming admin accounts are auto-approved
        ]);


        // Create other users for testing
        /*User::factory()->count(5)->create([
            'role_id' => $clientRoleId,
            'status' => 'pending',
        ]);
        */

        /*User::factory()->count(5)->create([
            'role_id' => $contractorRoleId,
            'status' => 'pending',
        ]);
        */

        /*User::factory()->count(5)->create([
            'role_id' => $supplierRoleId,
            'status' => 'pending',
        ]);
        */

        /*User::factory()->count(5)->create([
            'role_id' => $projectManagerRoleId,
            'status' => 'pending',
        ]);
        */
    }
}
