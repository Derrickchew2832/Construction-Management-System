<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectUserFavorite extends Model
{
    use HasFactory;

    protected $table = 'project_user_favorites';

    protected $fillable = [
        'user_id',
        'project_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
