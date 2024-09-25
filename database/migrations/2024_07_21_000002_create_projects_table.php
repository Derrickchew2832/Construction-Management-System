<?php

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

        // Create the project_documents table to store file paths for uploaded project documents
        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('document_path');  // Store the document file path
            $table->string('original_name');  // Store the original file name
            $table->timestamps();
        
            // Foreign key linking to the projects table
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
        
    }

    public function down()
    {
        // Disable foreign key constraints to safely drop tables
        Schema::disableForeignKeyConstraints();

        // Drop both projects and project_documents tables
        Schema::dropIfExists('project_documents');
        Schema::dropIfExists('projects');

        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
};
