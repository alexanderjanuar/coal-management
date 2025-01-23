<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use SolutionForest\FilamentAccessManagement\Concerns\FilamentUserHelpers;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use League\CommonMark\Node\Block\Document;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use FilamentUserHelpers;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function submittedDocuments()
    {
        return $this->hasMany(SubmittedDocument::class);
    }

    public function userClients()
    {
        return $this->hasMany(UserClient::class);
    }

    public function userProjects()
    {
        return $this->hasMany(UserProject::class);
    }


    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Or add your custom logic here
    }
}
