<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_manager_id',   // The ID of the project manager
        'name',                 // The name of the project
        'description',          // Description of the project
        'start_date',           // Start date of the project
        'end_date',             // End date of the project
        'total_budget',         // Total budget allocated for the project
        'budget_remaining',     // Remaining budget for the project
        'location',             // Location of the project
        'main_contractor_id',   // The ID of the main contractor (if assigned)
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

    // Many-to-many relationship to User (contractors) through the project_contractor table
    public function contractors()
    {
        return $this->belongsToMany(User::class, 'project_contractor')
                    ->withPivot('quoted_price', 'quote_pdf', 'status', 'suggested_by', 'is_final', 'main_contractor') // Fields on the pivot table
                    ->withTimestamps(); // Automatically manage created_at and updated_at on the pivot table
    }
}
