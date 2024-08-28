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
        'quote_pdf',          // Add this to handle the PDF file path
        'quote_suggestion',   // Add this for any quote suggestion descriptions
        'status',             // Track the status (pending, submitted, approved, rejected)
        'suggested_by',       // Track who made the last suggestion (project_manager or contractor)
        'is_final',           // Indicates if the negotiation is final
        'main_contractor',    // Indicates if this contractor is the main contractor
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function contractor()
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }
}
