<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'total_budget',
        'budget_remaining',
        'location',
        'main_contractor_id',
    ];

    public function documents()
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function contractors()
    {
        return $this->hasMany(ProjectContractor::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    public function mainContractor()
    {
        return $this->belongsTo(User::class, 'main_contractor_id');
    }
}
