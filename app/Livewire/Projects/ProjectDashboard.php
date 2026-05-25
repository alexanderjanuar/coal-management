<?php

namespace App\Livewire\Projects;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class ProjectDashboard extends Component
{
    /** Cache TTL for all dashboard queries (seconds). */
    protected const CACHE_TTL = 60;

    /** Selected time range preset — affects time-bound widgets (completion KPI, trend). */
    #[Url(as: 'range')]
    public string $timeRange = 'this_month';

    public const TIME_RANGES = [
        'today'        => 'Hari Ini',
        'yesterday'    => 'Kemarin',
        'last_7_days'  => '7 Hari Terakhir',
        'last_30_days' => '30 Hari Terakhir',
        'this_month'   => 'Bulan Ini',
        'last_month'   => 'Bulan Lalu',
        'this_quarter' => 'Kuartal Ini',
        'this_year'    => 'Tahun Ini',
        'all_time'     => 'Sepanjang Waktu',
    ];

    public function setTimeRange(string $range): void
    {
        if (\array_key_exists($range, self::TIME_RANGES)) {
            $this->timeRange = $range;
        }
    }

    /** [start, end] Carbon pair for current $timeRange. Null for 'all_time'. */
    protected function dateRange(): ?array
    {
        $now = now();
        return match ($this->timeRange) {
            'today'        => [$now->copy()->startOfDay(),                  $now->copy()->endOfDay()],
            'yesterday'    => [$now->copy()->subDay()->startOfDay(),        $now->copy()->subDay()->endOfDay()],
            'last_7_days'  => [$now->copy()->subDays(6)->startOfDay(),      $now->copy()->endOfDay()],
            'last_30_days' => [$now->copy()->subDays(29)->startOfDay(),     $now->copy()->endOfDay()],
            'this_month'   => [$now->copy()->startOfMonth(),                $now->copy()->endOfMonth()],
            'last_month'   => [$now->copy()->subMonth()->startOfMonth(),    $now->copy()->subMonth()->endOfMonth()],
            'this_quarter' => [$now->copy()->startOfQuarter(),              $now->copy()->endOfQuarter()],
            'this_year'    => [$now->copy()->startOfYear(),                 $now->copy()->endOfYear()],
            'all_time'     => null,
            default        => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    public function getTimeRangeLabelProperty(): string
    {
        return self::TIME_RANGES[$this->timeRange] ?? 'Periode';
    }

    public function getDateRangeSummaryProperty(): string
    {
        $range = $this->dateRange();
        if (!$range) return 'sepanjang waktu';
        [$start, $end] = $range;
        if ($start->isSameDay($end)) return $start->translatedFormat('j M Y');
        if ($start->isSameMonth($end)) return $start->translatedFormat('j') . '–' . $end->translatedFormat('j M Y');
        if ($start->isSameYear($end))  return $start->translatedFormat('j M') . ' – ' . $end->translatedFormat('j M Y');
        return $start->translatedFormat('j M Y') . ' – ' . $end->translatedFormat('j M Y');
    }

    protected function cacheKey(string $bucket): string
    {
        return "project_dash_{$bucket}_{$this->timeRange}";
    }

    // ─── KPI cards ────────────────────────────────────────────────
    public function getKpisProperty(): array
    {
        return Cache::remember($this->cacheKey('kpis'), self::CACHE_TTL, function () {
            $today = today();
            $weekEnd = $today->copy()->addDays(7);

            $activeKeys = $this->statusKeysInCategories(['active']);
            $stillRunningKeys = $this->statusKeysInCategories(['active', 'not_started']);
            $doneKeys = $this->statusKeysInCategories(['done']);

            // "Now" KPIs — always current state, not time-bound. All counts
            // scoped to projects whose client is currently Active.
            $active = $this->activeClientProjects()
                ->whereIn('status', $activeKeys)
                ->count();
            $overdue = $this->activeClientProjects()
                ->where('due_date', '<', $today)
                ->whereIn('status', $stillRunningKeys)
                ->count();
            $dueThisWeek = $this->activeClientProjects()
                ->whereBetween('due_date', [$today, $weekEnd])
                ->whereIn('status', $stillRunningKeys)
                ->count();

            // Time-bound KPI — completed within selected range.
            $range = $this->dateRange();
            $completedQuery = $this->activeClientProjects()->whereIn('status', $doneKeys);
            if ($range) $completedQuery->whereBetween('updated_at', $range);
            $completedInRange = $completedQuery->count();

            return compact('active', 'overdue', 'dueThisWeek', 'completedInRange');
        });
    }

    // ─── Status distribution (current state — never time-filtered) ─
    public function getStatusDistributionProperty(): array
    {
        return Cache::remember('project_dash_status_dist', self::CACHE_TTL, function () {
            $map = $this->statusMap();

            $counts = DB::table('projects')
                ->join('clients', 'projects.client_id', '=', 'clients.id')
                ->where('clients.status', 'Active')
                ->select('projects.status', DB::raw('COUNT(*) as count'))
                ->groupBy('projects.status')
                ->pluck('count', 'status')
                ->all();

            $total = array_sum($counts);
            $rows = [];

            foreach ($map as $key => $meta) {
                $count = $counts[$key] ?? 0;
                if ($count === 0) continue;
                $rows[] = [
                    'key'      => $key,
                    'label'    => $meta['label'],
                    'color'    => $meta['color'],
                    'count'    => $count,
                    'percent'  => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                    'category' => $meta['category'],
                ];
            }

            return ['rows' => $rows, 'total' => $total];
        });
    }

    // ─── PIC workload — stacked by status (top 10 by current active total) ────
    public function getPicWorkloadProperty(): array
    {
        return Cache::remember('project_dash_pic_workload', self::CACHE_TTL, function () {
            $activeKeys = $this->statusKeysInCategories(['active']);
            $statusMap  = $this->statusMap();
            // Stable status display order from the status map.
            $statusOrder = array_flip(array_keys($statusMap));

            // Counts per (PIC, status) pair — only active PICs assigned to
            // projects whose client is currently Active.
            $rows = DB::table('projects')
                ->join('users', 'projects.pic_id', '=', 'users.id')
                ->join('clients', 'projects.client_id', '=', 'clients.id')
                ->whereIn('projects.status', $activeKeys)
                ->whereNotNull('projects.pic_id')
                ->where('users.status', 'active')
                ->where('clients.status', 'Active')
                ->select('users.id as user_id', 'users.name', 'projects.status', DB::raw('COUNT(*) as count'))
                ->groupBy('users.id', 'users.name', 'projects.status')
                ->get();

            // Aggregate by PIC, collect unique statuses for the legend.
            $byPic = [];
            $statusesSeen = [];
            foreach ($rows as $r) {
                if (! isset($byPic[$r->user_id])) {
                    $byPic[$r->user_id] = [
                        'id'       => $r->user_id,
                        'name'     => $r->name,
                        'total'    => 0,
                        'segments' => [],
                    ];
                }
                $byPic[$r->user_id]['total'] += $r->count;
                $byPic[$r->user_id]['segments'][] = [
                    'status' => $r->status,
                    'label'  => $statusMap[$r->status]['label'] ?? ucfirst($r->status),
                    'color'  => $statusMap[$r->status]['color'] ?? '#94a3b8',
                    'count'  => (int) $r->count,
                ];
                $statusesSeen[$r->status] = [
                    'key'   => $r->status,
                    'label' => $statusMap[$r->status]['label'] ?? ucfirst($r->status),
                    'color' => $statusMap[$r->status]['color'] ?? '#94a3b8',
                    'order' => $statusOrder[$r->status] ?? 999,
                ];
            }

            // Top 10 PICs by total active.
            usort($byPic, fn ($a, $b) => $b['total'] <=> $a['total']);
            $top = array_slice(array_values($byPic), 0, 10);

            $max = collect($top)->max('total') ?: 1;

            // Sort each PIC's segments by status order + compute percentages.
            foreach ($top as &$pic) {
                $pic['percent'] = round(($pic['total'] / $max) * 100, 2);
                usort($pic['segments'], fn ($a, $b) => ($statusOrder[$a['status']] ?? 999) <=> ($statusOrder[$b['status']] ?? 999));
                foreach ($pic['segments'] as &$seg) {
                    $seg['segPercent'] = $pic['total'] > 0 ? round(($seg['count'] / $pic['total']) * 100, 2) : 0;
                }
                unset($seg);
            }
            unset($pic);

            // Legend sorted by status order.
            $legend = array_values($statusesSeen);
            usort($legend, fn ($a, $b) => $a['order'] <=> $b['order']);

            return [
                'rows'   => $top,
                'max'    => $max,
                'legend' => $legend,
            ];
        });
    }

    // ─── SOP distribution — stacked by status (top 10 by total projects) ────
    public function getSopDistributionProperty(): array
    {
        return Cache::remember('project_dash_sop_dist', self::CACHE_TTL, function () {
            $statusMap   = $this->statusMap();
            $statusOrder = array_flip(array_keys($statusMap));

            $rows = DB::table('projects')
                ->join('sops', 'projects.sop_id', '=', 'sops.id')
                ->join('clients', 'projects.client_id', '=', 'clients.id')
                ->whereNotNull('projects.sop_id')
                ->where('clients.status', 'Active')
                ->select('sops.id as sop_id', 'sops.name', 'projects.status', DB::raw('COUNT(*) as count'))
                ->groupBy('sops.id', 'sops.name', 'projects.status')
                ->get();

            $bySop = [];
            $statusesSeen = [];
            foreach ($rows as $r) {
                if (! isset($bySop[$r->sop_id])) {
                    $bySop[$r->sop_id] = [
                        'id'       => $r->sop_id,
                        'name'     => $r->name,
                        'total'    => 0,
                        'segments' => [],
                    ];
                }
                $bySop[$r->sop_id]['total'] += $r->count;
                $bySop[$r->sop_id]['segments'][] = [
                    'status' => $r->status,
                    'label'  => $statusMap[$r->status]['label'] ?? ucfirst($r->status),
                    'color'  => $statusMap[$r->status]['color'] ?? '#94a3b8',
                    'count'  => (int) $r->count,
                ];
                $statusesSeen[$r->status] = [
                    'key'   => $r->status,
                    'label' => $statusMap[$r->status]['label'] ?? ucfirst($r->status),
                    'color' => $statusMap[$r->status]['color'] ?? '#94a3b8',
                    'order' => $statusOrder[$r->status] ?? 999,
                ];
            }

            usort($bySop, fn ($a, $b) => $b['total'] <=> $a['total']);
            $top = array_slice(array_values($bySop), 0, 10);

            $max = collect($top)->max('total') ?: 1;

            foreach ($top as &$sop) {
                $sop['percent'] = round(($sop['total'] / $max) * 100, 2);
                usort($sop['segments'], fn ($a, $b) => ($statusOrder[$a['status']] ?? 999) <=> ($statusOrder[$b['status']] ?? 999));
                foreach ($sop['segments'] as &$seg) {
                    $seg['segPercent'] = $sop['total'] > 0 ? round(($seg['count'] / $sop['total']) * 100, 2) : 0;
                }
                unset($seg);
            }
            unset($sop);

            $legend = array_values($statusesSeen);
            usort($legend, fn ($a, $b) => $a['order'] <=> $b['order']);

            return [
                'rows'   => $top,
                'max'    => $max,
                'legend' => $legend,
                'total'  => array_sum(array_column($top, 'total')),
            ];
        });
    }

    // ─── Sample projects per SOP — drives the right card synced to the SOP carousel ────
    public function getSopSamplesProperty(): array
    {
        return Cache::remember('project_dash_sop_samples', self::CACHE_TTL, function () {
            $sopIds = collect($this->sopDistribution['rows'])->pluck('id')->all();
            if (empty($sopIds)) return [];

            $samples = [];
            foreach ($sopIds as $sopId) {
                $samples[$sopId] = $this->activeClientProjects()
                    ->with(['client:id,name,status', 'statusRecord'])
                    ->where('sop_id', $sopId)
                    // Pending / not-yet-completed first, then by upcoming due date
                    ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('due_date')
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get();
            }
            return $samples;
        });
    }

    // ─── Completion trend bucketed within selected range ──────────
    public function getCompletionTrendProperty(): array
    {
        return Cache::remember($this->cacheKey('trend'), self::CACHE_TTL, function () {
            $doneKeys = $this->statusKeysInCategories(['done']);

            // Pick range + granularity based on selection.
            $range = $this->dateRange();
            if (!$range) {
                // All time → last 12 months
                $start = now()->subMonths(11)->startOfMonth();
                $end = now()->endOfDay();
                $granularity = 'month';
            } else {
                [$start, $end] = $range;
                $days = $start->diffInDays($end) + 1;
                // Auto-bucket: ≤14 days → daily, ≤90 days → weekly, else monthly
                $granularity = $days <= 14 ? 'day' : ($days <= 90 ? 'week' : 'month');
            }

            // Build empty buckets
            $buckets = [];
            $cursor = $start->copy();
            while ($cursor <= $end) {
                $key = match ($granularity) {
                    'day'   => $cursor->format('Y-m-d'),
                    'week'  => $cursor->copy()->startOfWeek()->format('Y-m-d'),
                    'month' => $cursor->format('Y-m'),
                };
                if (! isset($buckets[$key])) {
                    $buckets[$key] = [
                        'label' => match ($granularity) {
                            'day'   => $cursor->translatedFormat('j M'),
                            'week'  => 'W' . $cursor->copy()->startOfWeek()->weekOfYear,
                            'month' => $cursor->translatedFormat('M'),
                        },
                        'count' => 0,
                    ];
                }
                $cursor->add($granularity === 'day' ? '1 day' : ($granularity === 'week' ? '1 week' : '1 month'));
                if ($granularity === 'week') $cursor = $cursor->startOfWeek();
            }

            // Query completed projects in range — active clients only.
            $rows = DB::table('projects')
                ->join('clients', 'projects.client_id', '=', 'clients.id')
                ->whereIn('projects.status', $doneKeys)
                ->where('clients.status', 'Active')
                ->whereBetween('projects.updated_at', [$start, $end])
                ->select('projects.updated_at')
                ->get();

            foreach ($rows as $r) {
                $dt = Carbon::parse($r->updated_at);
                $key = match ($granularity) {
                    'day'   => $dt->format('Y-m-d'),
                    'week'  => $dt->copy()->startOfWeek()->format('Y-m-d'),
                    'month' => $dt->format('Y-m'),
                };
                if (isset($buckets[$key])) $buckets[$key]['count']++;
            }

            $values = collect($buckets)->pluck('count')->all();
            $max = max($values) ?: 1;

            return [
                'buckets'     => array_values($buckets),
                'max'         => $max,
                'granularity' => $granularity,
                'total'       => array_sum($values),
            ];
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /** Scope all dashboard project queries to clients with status = Active. */
    protected function activeClientProjects(): \Illuminate\Database\Eloquent\Builder
    {
        return Project::query()
            ->whereHas('client', fn ($q) => $q->where('status', 'Active'));
    }

    protected function statusMap(): array
    {
        return Cache::remember('project_dash_status_map', self::CACHE_TTL, function () {
            return ProjectStatus::ordered()
                ->get()
                ->mapWithKeys(fn ($s) => [$s->key => [
                    'label'    => $s->label,
                    'color'    => $s->color,
                    'category' => $s->category,
                ]])
                ->toArray();
        });
    }

    protected function statusKeysInCategories(array $categories): array
    {
        return collect($this->statusMap())
            ->filter(fn ($m) => in_array($m['category'], $categories, true))
            ->keys()
            ->all();
    }

    public function projectUrl(Project $project): string
    {
        return ProjectResource::getUrl('view', ['record' => $project]);
    }

    public function listUrl(?string $filter = null): string
    {
        return ProjectResource::getUrl() . ($filter ? '?' . $filter : '');
    }

    public function render()
    {
        return view('livewire.projects.project-dashboard');
    }
}
