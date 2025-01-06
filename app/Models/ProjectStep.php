<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectStep extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'order', 'description', 'status'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function requiredDocuments()
    {
        return $this->hasMany(RequiredDocument::class);
    }
}
