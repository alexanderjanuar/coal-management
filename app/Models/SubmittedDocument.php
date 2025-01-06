<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmittedDocument extends Model
{
    use HasFactory;

    protected $fillable = ['required_document_id', 'user_id', 'file_path', 'status', 'rejection_reason'];

    public function requiredDocument()
    {
        return $this->belongsTo(RequiredDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
