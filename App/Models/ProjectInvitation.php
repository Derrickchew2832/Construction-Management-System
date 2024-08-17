<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInvitation extends Model
{
    use HasFactory;

    // Fields that can be mass-assigned
    protected $fillable = [
        'project_id',
        'contractor_id',
        'invited_by',
        'email',
        'token',
        'status',
    ];

    // Data type casting for attributes
    protected $casts = [
        'project_id' => 'integer',
        'contractor_id' => 'integer',
        'invited_by' => 'integer',
        'status' => 'string',
    ];

    // Relationship with the Project model
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relationship with the User model for the contractor
    public function contractor()
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }

    // Relationship with the User model for the user who sent the invitation
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
