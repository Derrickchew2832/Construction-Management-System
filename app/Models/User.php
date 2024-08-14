<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // Updated line from 'role' to 'role_id'
        'status',
        'phone',
        'document_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role_id' => 'integer', // Updated line from 'role' to 'role_id'
        'status' => 'string',
        'phone' => 'string',
        'document_path' => 'string',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    // This is the managedProjects method
    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'project_manager_id');
    }

    public function projectQuotes()
    {
        return $this->belongsToMany(Project::class, 'project_contractor')
                    ->withPivot('quoted_price', 'quote_document_path', 'status')
                    ->withTimestamps();
    }
}
