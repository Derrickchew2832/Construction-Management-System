<?php
// database/migrations/2024_07_21_000002_create_projects_table.php

// database/migrations/2024_07_21_000002_create_projects_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_manager_id');
            $table->string('name');
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_budget', 15, 2);
            $table->decimal('budget_remaining', 15, 2);
            $table->string('location');
            $table->string('status')->default('pending');
            $table->boolean('is_favorite')->default(false);
            $table->unsignedBigInteger('main_contractor_id')->nullable();
            $table->integer('user_count')->default(1); // New column to track the number of members in the project
            $table->timestamps();

            // Foreign keys
            $table->foreign('project_manager_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('main_contractor_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('projects');
        Schema::enableForeignKeyConstraints();
    }
};
