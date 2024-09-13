<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectContractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'contractor_id',
        'quoted_price',
        'quote_pdf',          // Path to the quote PDF
        'quote_suggestion',   // Any suggestions made during negotiations
        'status',             // Track the current status (e.g., pending, submitted, approved)
        'suggested_by',       // Tracks who made the last suggestion (project_manager or contractor)
        'is_final',           // Marks whether the negotiation is final
        'main_contractor',    // Indicates if this contractor is the main contractor
    ];

    // Belongs to Project relationship
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Belongs to Contractor (User)
    public function contractor()
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }
}
