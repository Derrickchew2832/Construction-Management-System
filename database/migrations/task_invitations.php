<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('task_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('contractor_id')->nullable(); // Invited contractor
            $table->unsignedBigInteger('invited_by'); // ID of the user who sent the invitation
            $table->string('email'); // Email for the contractor invitation
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); // Status of the invitation
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_invitations');
    }
};
