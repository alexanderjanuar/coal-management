<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientRequirementGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'created_by',
        'name',
        'description',
        'year',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'year' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ClientDocumentRequirement::class, 'group_id');
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    // Stats

    public function getStatsAttribute(): array
    {
        $reqs = $this->requirements;
        $total = $reqs->count();
        $fulfilled = $reqs->where('status', 'fulfilled')->count();
        $pending = $reqs->where('status', 'pending')->count();
        $waived = $reqs->where('status', 'waived')->count();

        return [
            'total'                 => $total,
            'fulfilled'             => $fulfilled,
            'pending'               => $pending,
            'waived'                => $waived,
            'completion_percentage' => $total > 0 ? round(($fulfilled / $total) * 100, 1) : 0,
        ];
    }

    public function isComplete(): bool
    {
        return $this->requirements()
            ->where('status', 'pending')
            ->where('is_required', true)
            ->doesntExist();
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->due_date
            && $this->due_date->isPast();
    }
}
