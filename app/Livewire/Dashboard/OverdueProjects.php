<?php

namespace App\Livewire\Dashboard;

use App\Models\Project;
use Livewire\Component;
use Illuminate\Support\Carbon;

class OverdueProjects extends Component
{
public $overdueProjects = [];
    public $overdueCount = 0;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $query = Project::with(['client', 'pic'])
            ->where('due_date', '<', $today)
            ->whereNotIn('status', ['completed', 'completed (Not Payed Yet)', 'canceled'])
            ->whereHas('client', function($q) {
                $q->where('status', 'Active');
            });
        
        // Apply role-based filtering
        if (!$user->hasRole('super-admin')) {
            $clientIds = $user->userClients()->pluck('client_id')->toArray();
            if (!empty($clientIds)) {
                $query->where(function($subQuery) use ($clientIds, $user) {
                    $subQuery->whereIn('client_id', $clientIds)
                             ->where(function($q) use ($user) {
                                 $q->where('pic_id', $user->id)
                                   ->orWhereHas('userProject', function($uq) use ($user) {
                                       $uq->where('user_id', $user->id);
                                   });
                             });
                });
            }
        }
        
        // Get total count
        $this->overdueCount = $query->count();
        
        // Get only top 5 projects
        $this->overdueProjects = $query->orderBy('due_date', 'asc')
                    ->orderByRaw("CASE WHEN priority = 'urgent' THEN 0 WHEN priority = 'normal' THEN 1 ELSE 2 END")
                    ->limit(5)
                    ->get()
                    ->map(function ($project) use ($today) {
                        $daysOverdue = $today->diffInDays(Carbon::parse($project->due_date));
                        return [
                            'id' => $project->id,
                            'name' => $project->name,
                            'client_name' => $project->client->name ?? 'Tidak ada klien',
                            'pic_name' => $project->pic->name ?? 'Belum ditugaskan',
                            'due_date' => Carbon::parse($project->due_date)->format('d M Y'),
                            'days_overdue' => $daysOverdue,
                            'priority' => $project->priority,
                            'url' => route('filament.admin.resources.projects.view', $project),
                        ];
                    })->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.overdue-projects');
    }
}
