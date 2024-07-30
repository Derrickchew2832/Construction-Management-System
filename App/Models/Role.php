<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = ['name'];

    // Define the relationship with the User model
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
