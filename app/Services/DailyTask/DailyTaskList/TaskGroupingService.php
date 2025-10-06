<?php
// app/Services/DailyTask/DailyTaskList/TaskGroupingService.php

namespace App\Services\DailyTask\DailyTaskList;

use Illuminate\Support\Collection;
use App\Models\DailyTask;
use Carbon\Carbon;

class TaskGroupingService
{
    public function group(Collection $tasks, string $groupBy): Collection
    {
        if ($groupBy === 'none') {
            return collect(['All Tasks' => $tasks]);
        }

        $grouped = $tasks->groupBy(function ($task) use ($groupBy) {
            return match($groupBy) {
                'status' => $this->getStatusLabel($task->status),
                'priority' => $this->getPriorityLabel($task->priority),
                'project' => $task->project?->name ?? 'No Project',
                'assignee' => $this->getAssigneeLabel($task),
                'date' => $this->getDateCategory($task),
                default => 'All Tasks'
            };
        });

        return $this->sortGroups($grouped, $groupBy);
    }

    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status)
        };
    }

    private function getPriorityLabel(string $priority): string
    {
        return match($priority) {
            'urgent' => 'Urgent',
            'high' => 'High',
            'normal' => 'Normal',
            'low' => 'Low',
            default => ucfirst($priority)
        };
    }

    private function getAssigneeLabel(DailyTask $task): string
    {
        if (!$task->assignedUsers || $task->assignedUsers->count() === 0) {
            return 'Unassigned';
        }
        
        if ($task->assignedUsers->count() === 1) {
            return $task->assignedUsers->first()->name;
        }
        
        return $task->assignedUsers->first()->name . ' (+' . ($task->assignedUsers->count() - 1) . ' more)';
    }

    private function getDateCategory(DailyTask $task): string
    {
        if ($task->status === 'completed') {
            return 'Selesai';
        }
        
        if (!$task->task_date) {
            return 'Tanpa Deadline';
        }
        
        // Ensure Carbon instance
        $taskDate = $task->task_date instanceof Carbon 
            ? $task->task_date 
            : Carbon::parse($task->task_date);
        
        if ($taskDate->isPast()) {
            return 'Terlambat';
        }
        
        return 'Mendatang';
    }

    private function sortGroups(Collection $grouped, string $groupBy): Collection
    {
        return $grouped->sortKeysUsing(function ($a, $b) use ($groupBy) {
            $order = match($groupBy) {
                'status' => ['Pending', 'In Progress', 'Completed', 'Cancelled'],
                'priority' => ['Urgent', 'High', 'Normal', 'Low'],
                'date' => ['Terlambat', 'Mendatang', 'Tanpa Deadline', 'Selesai'],
                default => []
            };

            if (empty($order)) {
                return strcasecmp($a, $b);
            }

            $aPos = array_search($a, $order);
            $bPos = array_search($b, $order);

            if ($aPos !== false && $bPos !== false) {
                return $aPos <=> $bPos;
            }

            return strcasecmp($a, $b);
        });
    }
}