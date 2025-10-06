<?php
// app/Services/DailyTask/DailyTaskList/TaskFilterService.php

namespace App\Services\DailyTask\DailyTaskList;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TaskFilterService
{
    public function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn($q, $search) => 
                $this->applySearch($q, $search)
            )
            ->when($filters['date'] ?? null, fn($q, $date) => 
                $this->applyDateFilter($q, $date)
            )
            ->when($filters['date_start'] ?? null, fn($q, $date) => 
                $this->applyDateStartFilter($q, $date)
            )
            ->when($filters['date_end'] ?? null, fn($q, $date) => 
                $this->applyDateEndFilter($q, $date)
            )
            ->when(!empty($filters['status']), fn($q) => 
                $q->whereIn('status', $filters['status'])
            )
            ->when(!empty($filters['priority']), fn($q) => 
                $q->whereIn('priority', $filters['priority'])
            )
            ->when(!empty($filters['project']), fn($q) => 
                $q->whereIn('project_id', $filters['project'])
            )
            ->when(!empty($filters['assignee']), fn($q) => 
                $this->applyAssigneeFilter($q, $filters['assignee'])
            );
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    private function applyDateFilter(Builder $query, $date): Builder
    {
        try {
            $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
            return $query->whereDate('start_task_date', $carbonDate->format('Y-m-d'));
        } catch (\Exception $e) {
            return $query; // Skip invalid dates
        }
    }

    private function applyDateStartFilter(Builder $query, $date): Builder
    {
        try {
            $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
            return $query->whereDate('start_task_date', '>=', $carbonDate->format('Y-m-d'));
        } catch (\Exception $e) {
            return $query;
        }
    }

    private function applyDateEndFilter(Builder $query, $date): Builder
    {
        try {
            $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
            return $query->whereDate('start_task_date', '<=', $carbonDate->format('Y-m-d'));
        } catch (\Exception $e) {
            return $query;
        }
    }

    private function applyAssigneeFilter(Builder $query, array $assignees): Builder
    {
        return $query->whereHas('assignedUsers', function ($q) use ($assignees) {
            $q->whereIn('users.id', $assignees);
        });
    }

    public function applySorting(Builder $query, string $sortBy, string $direction): Builder
    {
        if ($sortBy === 'priority') {
            return $this->sortByPriority($query, $direction);
        }

        return $query->orderBy($sortBy, $direction);
    }

    private function sortByPriority(Builder $query, string $direction): Builder
    {
        $order = $direction === 'desc' ? 'DESC' : 'ASC';
        
        return $query->orderByRaw("CASE priority 
            WHEN 'urgent' THEN 4 
            WHEN 'high' THEN 3 
            WHEN 'normal' THEN 2 
            WHEN 'low' THEN 1 
            ELSE 0 END {$order}")
            ->orderBy('task_date', 'asc');
    }
}