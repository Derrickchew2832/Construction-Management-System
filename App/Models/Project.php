<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_manager_id',   // The ID of the project manager who created the project
        'name',                 // The name of the project
        'description',          // A description of the project
        'start_date',           // The start date of the project
        'end_date',             // The end date of the project
        'total_budget',         // The total budget allocated for the project
        'budget_remaining',     // The remaining budget for the project
        'location',             // The physical location of the project
        'main_contractor_id',   // The ID of the contractor selected as the main contractor
    ];

    // Relationship to the User model, representing the project manager
    public function manager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    // Relationship to the User model, representing the main contractor
    public function mainContractor()
    {
        return $this->belongsTo(User::class, 'main_contractor_id');
    }

    // Many-to-many relationship to the User model, representing contractors invited to the project
    public function contractors()
    {
        return $this->belongsToMany(User::class, 'project_contractor')
                    ->withPivot('quoted_price', 'quote_document_path', 'status') // Additional fields on the pivot table
                    ->withTimestamps(); // Automatically manage created_at and updated_at on the pivot table
    }
}
