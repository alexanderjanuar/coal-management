<?php

namespace App\Models;

use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use SolutionForest\FilamentAccessManagement\Concerns\FilamentUserHelpers;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use League\CommonMark\Node\Block\Document;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\HasAvatar;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, CausesActivity;
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

    public function getAvatarUrl()
    {
        return filament()->getUserAvatarUrl($this);
    }

    public function clientDocuments(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
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

    public function activities()
    {
        return $this->actions();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'password'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                return match ($eventName) {
                    'created' => "Akun pengguna baru dibuat: {$this->name}",
                    'updated' => "Detail akun pengguna diubah: {$this->name}",
                    'deleted' => "Akun pengguna dihapus: {$this->name}",
                    default => "Akun pengguna {$this->name} telah di{$eventName}"
                };
            });
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    /**
     * Get the user's avatar URL or return a default
     */
    public function getAvatarAttribute(): string
    {
        // If there's an uploaded file, use that first
        if ($this->avatar_path && \Storage::disk('public')->exists($this->avatar_path)) {
            return \Storage::disk('public')->url($this->avatar_path);
        }
        
        // Otherwise use the URL if provided
        if ($this->avatar_url) {
            return $this->avatar_url;
        }
        
        // Fall back to generated avatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF&size=300';
    }

    /**
     * Delete the old avatar file when updating
     */
    public function deleteOldAvatar(): void
    {
        if ($this->avatar_path && \Storage::disk('public')->exists($this->avatar_path)) {
            \Storage::disk('public')->delete($this->avatar_path);
        }
    }

    /**
     * Set avatar path and automatically populate avatar_url with storage/ prefix
     */
    public function setAvatarPathAttribute($value): void
    {
        $this->attributes['avatar_path'] = $value;
        
        // If we're setting a file path, also set the avatar_url with storage/ prefix
        if ($value) {
            $this->attributes['avatar_url'] = 'storage/' . $value;
        }
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Or add your custom logic here
    }
}
