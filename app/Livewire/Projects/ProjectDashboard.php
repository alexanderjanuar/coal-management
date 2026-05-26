<?php

namespace App\Livewire\Projects;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\UserActivity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProjectDashboard extends Component
{
    /** Cache TTL for all dashboard queries (seconds). */
    protected const CACHE_TTL = 60;

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

    // ─── PIC workload — stacked by status (top 10 across all project statuses) ────
    public function getPicWorkloadProperty(): array
    {
        return Cache::remember('project_dash_pic_workload', self::CACHE_TTL, function () {
            $statusMap  = $this->statusMap();
            // Stable status display order from the status map.
            $statusOrder = array_flip(array_keys($statusMap));

            // Counts per (PIC, status) pair — only active PICs assigned to
            // projects whose client is currently Active. All project statuses
            // included (Draft → Completed → Canceled) so the bar reflects
            // each PIC's total accumulated workload.
            $rows = DB::table('projects')
                ->join('users', 'projects.pic_id', '=', 'users.id')
                ->join('clients', 'projects.client_id', '=', 'clients.id')
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

            // Unassigned projects — appended at the bottom of the bar list
            // so it surfaces as an action item without bumping real PICs down.
            $noPicRows = DB::table('projects')
                ->join('clients', 'projects.client_id', '=', 'clients.id')
                ->whereNull('projects.pic_id')
                ->where('clients.status', 'Active')
                ->select('projects.status', DB::raw('COUNT(*) as count'))
                ->groupBy('projects.status')
                ->get();

            if ($noPicRows->isNotEmpty()) {
                $unassigned = [
                    'id'             => null,
                    'name'           => 'Tanpa PIC',
                    'total'          => 0,
                    'segments'       => [],
                    'is_unassigned'  => true,
                ];
                foreach ($noPicRows as $r) {
                    $unassigned['total'] += $r->count;
                    $unassigned['segments'][] = [
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
                $top[] = $unassigned;
            }

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

    // ─── Recent activities — latest project-related entries, grouped by date bucket ────
    public function getRecentActivitiesProperty(): array
    {
        return Cache::remember('project_dash_recent_activities', self::CACHE_TTL, function () {
            $activities = UserActivity::query()
                ->with([
                    'user:id,name',
                    'project:id,name,client_id',
                    'project.client:id,name',
                ])
                ->whereNotNull('project_id')
                // Skip activities whose project belongs to an inactive client
                ->whereHas('project.client', fn ($q) => $q->where('status', 'Active'))
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            // Action → short verb. Used to bypass the giant filename / details
            // that summarizeDescription drags along for document-type actions.
            $shortVerbs = [
                'document_uploaded'         => 'mengupload dokumen',
                'client_document_uploaded'  => 'mengupload dokumen',
                'legal_document_uploaded'   => 'mengupload dokumen legal',
                'document_submitted'        => 'mengupload dokumen',
                'document_upload'           => 'mengupload dokumen',
                'document_approved'         => 'menyetujui dokumen',
                'client_document_approved'  => 'menyetujui dokumen',
                'legal_document_approved'   => 'menyetujui dokumen legal',
                'document_rejected'         => 'menolak dokumen',
                'client_document_rejected'  => 'menolak dokumen',
                'legal_document_rejected'   => 'menolak dokumen legal',
                'document_status_changed'   => 'mengubah status dokumen',
                'document_pending_review'   => 'mengirim dokumen untuk review',
            ];

            $groups = ['today' => [], 'yesterday' => [], 'older' => []];

            foreach ($activities as $a) {
                $bucket = match (true) {
                    $a->created_at->isToday()     => 'today',
                    $a->created_at->isYesterday() => 'yesterday',
                    default                       => 'older',
                };

                // For known noisy actions, use the short verb. For everything
                // else, run summarizeDescription then trim the "untuk Y" tail
                // (project context is shown below) and lowercase the leading
                // verb so it reads natural after the bold user name.
                if (isset($shortVerbs[$a->action])) {
                    $short = $shortVerbs[$a->action];
                } else {
                    $short = $a->summarizeDescription();
                    $short = preg_replace('/\s+untuk\s+.+$/u', '', $short);
                    if ($short !== '') {
                        $short = mb_strtolower(mb_substr($short, 0, 1)) . mb_substr($short, 1);
                    }
                }

                $groups[$bucket][] = [
                    'id'           => $a->id,
                    'user_name'    => $a->user?->name ?? 'Sistem',
                    'description'  => $short,
                    'client_name'  => $a->project?->client?->name,
                    'project_name' => $a->project?->name,
                    'project_url'  => $a->project ? $this->projectUrl($a->project) : null,
                    // Absolute syntax drops the "lalu" suffix → "5mnt" instead of "5mnt lalu"
                    'time_ago'     => $a->created_at->diffForHumans([
                        'short'  => true,
                        'syntax' => Carbon::DIFF_ABSOLUTE,
                    ]),
                ];
            }

            return $groups;
        });
    }

    // ─── Completion trend — last 12 months, bucketed by month ────────
    public function getCompletionTrendProperty(): array
    {
        return Cache::remember('project_dash_trend', self::CACHE_TTL, function () {
            $doneKeys = $this->statusKeysInCategories(['done']);

            $start = now()->subMonths(11)->startOfMonth();
            $end   = now()->endOfDay();

            // Build empty monthly buckets.
            $buckets = [];
            $cursor = $start->copy();
            while ($cursor <= $end) {
                $key = $cursor->format('Y-m');
                if (! isset($buckets[$key])) {
                    $buckets[$key] = [
                        'label' => $cursor->translatedFormat('M'),
                        'count' => 0,
                    ];
                }
                $cursor->addMonth();
            }

            // Active-client projects completed in this window.
            $rows = DB::table('projects')
                ->join('clients', 'projects.client_id', '=', 'clients.id')
                ->whereIn('projects.status', $doneKeys)
                ->where('clients.status', 'Active')
                ->whereBetween('projects.updated_at', [$start, $end])
                ->select('projects.updated_at')
                ->get();

            foreach ($rows as $r) {
                $key = Carbon::parse($r->updated_at)->format('Y-m');
                if (isset($buckets[$key])) $buckets[$key]['count']++;
            }

            $values = collect($buckets)->pluck('count')->all();

            return [
                'buckets'     => array_values($buckets),
                'max'         => max($values) ?: 1,
                'granularity' => 'month',
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
