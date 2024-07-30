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
        'role_id', // Change this line from 'role' to 'role_id'
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
        'role_id' => 'integer', // Change this line from 'role' to 'role_id'
        'status' => 'string',
        'phone' => 'string',
        'document_path' => 'string',
    ];

    // Define the relationship with the Role model
    // public function role()
    // {
    //     return $this->belongsTo(Role::class);
    // }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }
}
