<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = ['project_step_id', 'name', 'description', 'is_required'];

    public function projectStep()
    {
        return $this->belongsTo(ProjectStep::class);
    }

    public function submittedDocuments()
    {
        return $this->hasMany(SubmittedDocument::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
