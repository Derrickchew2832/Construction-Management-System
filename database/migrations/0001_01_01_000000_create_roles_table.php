<?php

// database/migrations/2024_07_21_000000_create_roles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id(); // This will create an unsigned big integer primary key
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insert default roles directly into the roles table
        DB::table('roles')->insert([
            ['name' => 'admin'],
            ['name' => 'client'],
            ['name' => 'project manager'],
            ['name' => 'contractor'],
            ['name' => 'supplier'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
}
