<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id'); // Link to the project
            $table->unsignedBigInteger('task_id')->nullable(); // Link to task (optional)
            $table->unsignedBigInteger('uploaded_by'); // User ID who uploaded the photo
            $table->string('photo_path'); // Path to the photo file
            $table->text('description')->nullable(); // Optional description for the photo
            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null'); // Set task_id to null if task is deleted
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
