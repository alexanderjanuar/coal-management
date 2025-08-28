<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;
    
    use LogsActivity;

    protected $fillable = ['client_id', 'name', 'description', 'status'];

    protected $casts = [
        'due_date' => 'date'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'priority', 'type', 'due_date', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->client->name ?? 'Klien';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 📂 PROYEK BARU: {$this->name} | Prioritas: {$this->priority}",
                    'updated' => match($this->status) {
                        'completed' => "[{$clientName}] ✅ PROYEK SELESAI: {$this->name}",
                        'in_progress' => "[{$clientName}] ⚡ PROYEK AKTIF: {$this->name}",
                        'on_hold' => "[{$clientName}] ⏸️ PROYEK DITUNDA: {$this->name}",
                        'canceled' => "[{$clientName}] ❌ PROYEK DIBATALKAN: {$this->name}",
                        default => "[{$clientName}] Proyek {$this->name} diperbarui"
                    },
                    'deleted' => "[{$clientName}] 🗑️ PROYEK DIHAPUS: {$this->name}",
                    default => "[{$clientName}] Proyek {$this->name} telah di{$eventName}"
                };
            });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function steps()
    {
        return $this->hasMany(ProjectStep::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_projects')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function userProject(): HasMany
    {
        return $this->hasMany(UserProject::class);
    }

    public function sop(): BelongsTo
    {
        return $this->belongsTo(SOP::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

}
