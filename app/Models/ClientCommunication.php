<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ClientCommunication extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'client_id',
        'user_id',
        'title',
        'description',
        'type',
        'communication_date',
        'communication_time',
        'location',
        'latitude',
        'longitude',
        'client_participants',
        'internal_participants',
        'status',
        'outcome',
        'priority',
        'project_id',
        'meeting_link',
        'meeting_platform',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'communication_date' => 'date',
        'attachments' => 'array',
        'client_participants' => 'array',
        'internal_participants' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get attachments as array, always return array even if null
     */
    public function getAttachmentsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    /**
     * Get client participants as array
     */
    public function getClientParticipantsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    /**
     * Get internal participants as array
     */
    public function getInternalParticipantsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return is_array($value) ? $value : [];
    }

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('communication_date', '>=', now())
                    ->orderBy('communication_date');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('communication_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('communication_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude');
    }

    // Accessors
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'heroicon-o-calendar',
            'email' => 'heroicon-o-envelope',
            'phone' => 'heroicon-o-phone',
            'video_call' => 'heroicon-o-video-camera',
            default => 'heroicon-o-chat-bubble-left-right',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'meeting' => 'Meeting',
            'email' => 'Email',
            'phone' => 'Telepon',
            'video_call' => 'Video Call',
            default => 'Lainnya',
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'rescheduled' => 'warning',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'Terjadwal',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'rescheduled' => 'Dijadwalkan Ulang',
            default => 'Tidak Diketahui',
        };
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'normal' => 'info',
            'low' => 'gray',
            default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'Mendesak',
            'high' => 'Tinggi',
            'normal' => 'Normal',
            'low' => 'Rendah',
            default => 'Normal',
        };
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === 'scheduled' 
            && $this->communication_date 
            && $this->communication_date->isFuture();
    }

    public function getLocationDisplayAttribute(): string
    {
        if (!$this->location) {
            return match($this->type) {
                'video_call' => $this->meeting_platform ?? 'Online',
                'email' => 'Email',
                'phone' => 'Telepon',
                default => '-'
            };
        }

        return $this->location;
    }

    public function getCoordinatesAttribute(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }

        return null;
    }

    public function getHasCoordinatesAttribute(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function getGoogleMapsLinkAttribute(): ?string
    {
        if (!$this->has_coordinates) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function getFormattedLocationAttribute(): string
    {
        $location = $this->location_display;
        
        if ($this->has_coordinates) {
            $location .= " 📍";
        }

        return $location;
    }

    // Helper Methods
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function reschedule($newDate, $newTime = null): void
    {
        $this->update([
            'status' => 'rescheduled',
            'communication_date' => $newDate,
            'communication_time' => $newTime,
        ]);
    }

    public function setCoordinates(float $latitude, float $longitude): void
    {
        $this->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    public function clearCoordinates(): void
    {
        $this->update([
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    /**
     * Calculate distance to another location in kilometers
     */
    public function distanceTo(float $lat, float $lng): ?float
    {
        if (!$this->has_coordinates) {
            return null;
        }

        // Haversine formula
        $earthRadius = 6371; // km

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Add client participant
     */
    public function addClientParticipant(array $participant): void
    {
        $participants = $this->client_participants;
        $participants[] = $participant;
        $this->update(['client_participants' => $participants]);
    }

    /**
     * Add internal participant (user_id or manual input)
     */
    public function addInternalParticipant($participant): void
    {
        $participants = $this->internal_participants;
        $participants[] = $participant;
        $this->update(['internal_participants' => $participants]);
    }

    /**
     * Remove client participant by index
     */
    public function removeClientParticipant(int $index): void
    {
        $participants = $this->client_participants;
        if (isset($participants[$index])) {
            unset($participants[$index]);
            // Re-index array
            $participants = array_values($participants);
            $this->update(['client_participants' => $participants]);
        }
    }

    /**
     * Remove internal participant by index
     */
    public function removeInternalParticipant(int $index): void
    {
        $participants = $this->internal_participants;
        if (isset($participants[$index])) {
            unset($participants[$index]);
            // Re-index array
            $participants = array_values($participants);
            $this->update(['internal_participants' => $participants]);
        }
    }

    /**
     * Get total participants count
     */
    public function getTotalParticipantsAttribute(): int
    {
        return count($this->client_participants) + count($this->internal_participants);
    }

    /**
     * Get client participants count
     */
    public function getClientParticipantsCountAttribute(): int
    {
        return count($this->client_participants);
    }

    /**
     * Get internal participants count
     */
    public function getInternalParticipantsCountAttribute(): int
    {
        return count($this->internal_participants);
    }

    /**
     * Get internal participants as User models (if they are user_ids)
     */
    public function getInternalParticipantUsersAttribute()
    {
        $userIds = collect($this->internal_participants)
            ->filter(fn($p) => is_numeric($p))
            ->toArray();

        if (empty($userIds)) {
            return collect([]);
        }

        return User::whereIn('id', $userIds)->get();
    }

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'type',
                'communication_date',
                'location',
                'latitude',
                'longitude',
                'client_participants',
                'internal_participants',
                'status',
                'priority',
                'outcome'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->client?->name ?? 'Klien';
                $userName = auth()->user()?->name ?? 'System';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 📅 KOMUNIKASI BARU: {$this->type_label} - {$this->title} dijadwalkan pada " . $this->communication_date->format('d M Y') . " oleh {$userName}",
                    'updated' => match($this->status) {
                        'completed' => "[{$clientName}] ✅ SELESAI: {$this->type_label} - {$this->title} telah diselesaikan oleh {$userName}",
                        'cancelled' => "[{$clientName}] ❌ DIBATALKAN: {$this->type_label} - {$this->title} dibatalkan oleh {$userName}",
                        'rescheduled' => "[{$clientName}] 🔄 DIJADWALKAN ULANG: {$this->type_label} - {$this->title} oleh {$userName}",
                        default => "[{$clientName}] 📝 DIPERBARUI: {$this->type_label} - {$this->title} oleh {$userName}"
                    },
                    'deleted' => "[{$clientName}] 🗑️ DIHAPUS: {$this->type_label} - {$this->title} dihapus oleh {$userName}",
                    default => "[{$clientName}] {$this->type_label} - {$this->title} telah {$eventName} oleh {$userName}"
                };
            });
    }

    // Boot method
    protected static function booted()
    {
        static::creating(function ($communication) {
            // Auto-set user_id if not set (person responsible)
            if (!$communication->user_id && auth()->check()) {
                $communication->user_id = auth()->id();
            }
        });
    }
}