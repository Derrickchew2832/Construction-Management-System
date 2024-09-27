<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('task_contractor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('contractor_id');
            $table->decimal('quoted_price', 15, 2)->nullable(); // Latest quoted price
            $table->string('quote_pdf')->nullable(); // Path to quote document
            $table->text('quote_suggestion')->nullable(); // Suggested improvements for the quote
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected', 'suggested'])->default('pending');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_contractor');
    }
};
