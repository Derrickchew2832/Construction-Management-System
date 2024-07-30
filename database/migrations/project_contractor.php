<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_contractors', function (Blueprint $table) {
            $table->id(); // Creates an unsignedBigInteger primary key
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('contractor_id');
            $table->decimal('quoted_price', 15, 2);
            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_contractors');
    }
};

