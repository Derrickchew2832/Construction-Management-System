<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = ['name'];

    /**
     * Define the relationship between the Role and the User model.
     * A role can be assigned to many users (one-to-many relationship).
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id')
                    ->withTimestamps();
    }

    /**
     * Define the relationship between the Role and the ProjectContractor model.
     * A role (main_contractor) can be associated with many project contractors.
     */
    public function projectContractors()
    {
        return $this->hasMany(ProjectContractor::class);
    }
}
