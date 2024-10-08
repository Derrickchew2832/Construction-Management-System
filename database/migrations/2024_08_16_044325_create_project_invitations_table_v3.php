<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectInvitationsTableV3 extends Migration
{
    public function up()
    {
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('contractor_id')->nullable();  // Nullable contractor ID (for clients, this can be null)
            $table->unsignedBigInteger('invited_by');     // ID of the user who sent the invitation (likely project manager or main contractor)
            $table->string('email');                      // Email for both clients and contractors
            $table->string('token')->nullable();          // Token for verifying the invitation (if applicable)
            $table->string('status')->default('pending'); // Status of the invitation (pending, accepted, rejected, etc.)
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('contractor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_invitations');
    }
}
