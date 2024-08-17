<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    // Defines the fillable attributes for the User model
    protected $fillable = [
        'name',             // The name of the user
        'email',            // The email address of the user
        'password',         // The password of the user
        'role_id',          // The role ID associated with the user (e.g., project manager, contractor)
        'status',           // The status of the user (e.g., active, inactive)
        'phone',            // The phone number of the user
        'document_path',    // The path to a document uploaded by the user (e.g., ID, contract)
    ];

    // Attributes that should be hidden for arrays and JSON
    protected $hidden = [
        'password',         // Hides the password in arrays and JSON
        'remember_token',   // Hides the remember token in arrays and JSON
    ];

    // Casts attributes to specific types
    protected $casts = [
        'email_verified_at' => 'datetime',  // Casts the email_verified_at attribute to a datetime object
        'password' => 'hashed',             // Ensures the password is always hashed
        'role_id' => 'integer',             // Casts the role_id attribute to an integer
        'status' => 'string',               // Casts the status attribute to a string
        'phone' => 'string',                // Casts the phone attribute to a string
        'document_path' => 'string',        // Casts the document_path attribute to a string
    ];

    // Defines the relationship between the User and Role models
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Checks if the user has a specific role
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    // Defines the relationship where a user manages multiple projects
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    // Defines the relationship between the User and Project models for contractors submitting quotes
    public function projectQuotes()
    {
        return $this->belongsToMany(Project::class, 'project_contractor')
                    ->withPivot('quoted_price', 'quote_document_path', 'status')
                    ->withTimestamps();
    }

    // Defines the relationship for projects that a user has marked as favorites
    public function favoriteProjects()
    {
        return $this->belongsToMany(Project::class, 'project_user_favorites');
    }
}
