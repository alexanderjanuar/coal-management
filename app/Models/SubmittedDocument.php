<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SubmittedDocument extends Model
{
    use HasFactory;

    use LogsActivity;

    protected $fillable = ['required_document_id', 'user_id', 'file_path', 'status', 'rejection_reason'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['file_path', 'rejection_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $docName = $this->requiredDocument->name ?? 'Document';
                return "Submitted {$docName} was {$eventName}";
            })
            ->logFillable();
    }

    

    public function requiredDocument()
    {
        return $this->belongsTo(RequiredDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
