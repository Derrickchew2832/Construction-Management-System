<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_contractor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('contractor_id');
            $table->decimal('quoted_price', 15, 2);  // Holds the latest price in negotiation
            $table->string('quote_pdf');  // Path to the quote PDF file
            $table->text('quote_suggestion')->nullable();  // Added for quote suggestion
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected', 'suggested'])->default('pending');  // Added 'suggested'
            $table->string('suggested_by')->nullable();  // Tracks who made the last suggestion (project_manager, contractor)
            $table->boolean('is_final')->default(false);  // Indicates if the negotiation is final
            $table->boolean('main_contractor')->default(false);  // Indicates if this contractor is the main contractor
            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_contractor');
    }
};
