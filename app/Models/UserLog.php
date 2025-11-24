<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserLog extends Model
{
    use HasFactory;

    protected $table = 'user_log';

    protected $fillable = [
        'user_id',
        'url',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Scope for users active within last N minutes
     */
    public function scopeActiveWithinMinutes($query, int $minutes)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * Scope for users active within last N hours
     */
    public function scopeActiveWithinHours($query, int $hours)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for currently active users (within last 5 minutes)
     */
    public function scopeCurrentlyActive($query)
    {
        return $query->where('created_at', '>=', now()->subMinutes(5));
    }

    /**
     * Get the last activity time as human readable
     */
    public function getLastActivityAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if user was active within last N minutes
     */
    public function isActiveWithinMinutes(int $minutes): bool
    {
        return $this->created_at->gte(now()->subMinutes($minutes));
    }

    /**
     * Get activity status badge color
     */
    public function getActivityStatusColorAttribute(): string
    {
        $minutes = $this->created_at->diffInMinutes(now());
        
        return match(true) {
            $minutes <= 5 => 'success',      // Online (green)
            $minutes <= 10 => 'warning',     // Recently active (yellow)
            $minutes <= 30 => 'info',        // Active within 30 min (blue)
            $minutes <= 60 => 'gray',        // Active within 1 hour (gray)
            default => 'danger'              // Inactive (red)
        };
    }

    /**
     * Get activity status text
     */
    public function getActivityStatusTextAttribute(): string
    {
        $minutes = $this->created_at->diffInMinutes(now());
        
        return match(true) {
            $minutes <= 5 => 'Online',
            $minutes <= 10 => 'Aktif ' . $this->created_at->diffForHumans(),
            $minutes <= 30 => 'Terakhir ' . $this->created_at->diffForHumans(),
            $minutes <= 60 => 'Terakhir ' . $this->created_at->diffForHumans(),
            default => 'Tidak aktif sejak ' . $this->created_at->diffForHumans()
        };
    }

    /**
     * Get simplified page name from URL
     */
    public function getPageNameAttribute(): string
    {
        $url = $this->url;
        
        // Extract meaningful part from URL
        if (preg_match('/\/admin\/([^\/\?]+)/', $url, $matches)) {
            return ucfirst(str_replace('-', ' ', $matches[1]));
        }
        
        return 'Dashboard';
    }

    /**
     * Static method to get latest activity per user
     */
    public static function getLatestActivityPerUser()
    {
        return static::select('user_logs.*')
            ->join(
                \DB::raw('(SELECT user_id, MAX(created_at) as max_created_at FROM user_log GROUP BY user_id) as latest'),
                function($join) {
                    $join->on('user_logs.user_id', '=', 'latest.user_id')
                         ->on('user_logs.created_at', '=', 'latest.max_created_at');
                }
            )
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get currently active users count
     */
    public static function getCurrentlyActiveUsersCount(): int
    {
        return static::currentlyActive()
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Get user activity statistics
     */
    public static function getUserActivityStats($userId, $days = 7): array
    {
        $activities = static::forUser($userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'total_activities' => $activities->count(),
            'total_pages_visited' => $activities->pluck('url')->unique()->count(),
            'most_visited_page' => $activities->groupBy('url')->sortByDesc->count()->keys()->first(),
            'first_activity' => $activities->last()?->created_at,
            'last_activity' => $activities->first()?->created_at,
            'average_daily_activities' => round($activities->count() / max($days, 1), 2),
        ];
    }
}