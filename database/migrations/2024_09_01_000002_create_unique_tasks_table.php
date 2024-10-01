<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('assigned_contractor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->date('start_date');
            $table->date('due_date');
            $table->enum('category', [
                'under_negotiation',
                'due_date',
                'priority_1',
                'priority_2',
                'completed',
                'verified'
            ])->default('under_negotiation'); // Change 'status' to 'category'
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending'); // Add a separate 'status' field for task acceptance status
            $table->string('task_pdf')->nullable(); // Optional task document
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
