<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectInvitationsClientTablev4 extends Migration
{
    public function up()
    {
        Schema::create('project_invitations_client', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('client_id')->nullable();  // Nullable client ID (for unregistered clients)
            $table->unsignedBigInteger('invited_by');             // ID of the user who sent the invitation (likely project manager)
            $table->string('email');                              // Email for the client invitation
            $table->string('token')->nullable();                  // Token for verifying the invitation
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');  // Status of the invitation
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_invitations_client');
    }
}
