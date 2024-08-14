<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_manager_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'total_budget',
        'budget_remaining',
        'location',
        'main_contractor_id',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function mainContractor()
    {
        return $this->belongsTo(User::class, 'main_contractor_id');
    }

    public function contractors()
    {
        return $this->belongsToMany(User::class, 'project_contractor')
                    ->withPivot('quoted_price', 'quote_document_path', 'status')
                    ->withTimestamps();
    }
}
