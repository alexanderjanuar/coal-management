<?php

namespace App\Observers;

use App\Models\DailyTask;
use App\Models\Comment;

class DailyTaskObserver
{
    /**
     * Handle the DailyTask "updated" event.
     */
    public function updated(DailyTask $dailyTask): void
    {
        // Jangan buat comment jika tidak ada user yang login
        if (!auth()->check()) {
            return;
        }

        $changes = $dailyTask->getDirty();
        $original = $dailyTask->getOriginal();

        foreach ($changes as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            
            // Skip jika nilai sama atau field yang tidak perlu di-track
            if ($oldValue === $newValue || in_array($field, ['updated_at', 'created_at'])) {
                continue;
            }

            $this->createActivityComment($dailyTask, $field, $oldValue, $newValue);
        }
    }

    /**
     * Handle the DailyTask "deleted" event.
     */
    public function deleted(DailyTask $dailyTask): void
    {
        // Untuk deleted, kita tidak bisa menambah comment ke task yang sudah dihapus
        // Tapi bisa log ke sistem atau create notification
        \Log::info("Daily Task '{$dailyTask->title}' telah dihapus oleh " . (auth()->user()->name ?? 'System'));
    }

    /**
     * Handle the DailyTask "deleting" event.
     * Ini dipanggil sebelum task dihapus, jadi masih bisa menambah comment
     */
    public function deleting(DailyTask $dailyTask): void
    {
        if (!auth()->check()) {
            return;
        }

        $dailyTask->comments()->create([
            'user_id' => auth()->id(),
            'content' => "Task '{$dailyTask->title}' akan dihapus",
            'status' => 'approved',
        ]);
    }

    /**
     * Handle assignment changes - dipanggil dari Livewire component
     */
    public function assignmentChanged(DailyTask $dailyTask, string $action, $userId, string $userName = null): void
    {
        if (!auth()->check()) {
            return;
        }

        $message = match($action) {
            'assigned' => "User '{$userName}' ditugaskan ke task ini",
            'unassigned' => "User '{$userName}' dihapus dari task ini",
            default => "Assignment diperbarui"
        };

        $dailyTask->comments()->create([
            'user_id' => auth()->id(),
            'content' => $message,
            'status' => 'approved',
        ]);
    }

    /**
     * Create activity comment for task changes
     */
    private function createActivityComment(DailyTask $task, string $field, $oldValue, $newValue): void
    {
        $message = $this->generateChangeMessage($field, $oldValue, $newValue);
        
        if ($message) {
            $task->comments()->create([
                'user_id' => auth()->id(),
                'content' => $message,
                'status' => 'approved',
            ]);
        }
    }

    /**
     * Generate appropriate message for field changes
     */
    private function generateChangeMessage(string $field, $oldValue, $newValue): ?string
    {
        return match($field) {
            'status' => $this->getStatusChangeMessage($oldValue, $newValue),
            'priority' => $this->getPriorityChangeMessage($oldValue, $newValue),
            'title' => "Judul task diubah dari '{$oldValue}' menjadi '{$newValue}'",
            'description' => $oldValue ? "Deskripsi task diperbarui" : "Deskripsi task ditambahkan",
            'task_date' => "Tanggal deadline diubah dari " . \Carbon\Carbon::parse($oldValue)->format('d M Y') . " menjadi " . \Carbon\Carbon::parse($newValue)->format('d M Y'),
            'start_task_date' => $newValue ? "Task dimulai pada " . \Carbon\Carbon::parse($newValue)->format('d M Y') : "Task belum dimulai",
            'project_id' => $this->getProjectChangeMessage($oldValue, $newValue),
            default => null
        };
    }

    /**
     * Get status change message with proper labels
     */
    private function getStatusChangeMessage($oldValue, $newValue): string
    {
        $statusLabels = [
            'pending' => 'Tertunda',
            'in_progress' => 'Sedang Dikerjakan', 
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        $oldLabel = $statusLabels[$oldValue] ?? $oldValue;
        $newLabel = $statusLabels[$newValue] ?? $newValue;

        return "Status diubah dari '{$oldLabel}' menjadi '{$newLabel}'";
    }

    /**
     * Get priority change message with proper labels
     */
    private function getPriorityChangeMessage($oldValue, $newValue): string
    {
        $priorityLabels = [
            'low' => 'Rendah',
            'normal' => 'Normal', 
            'high' => 'Tinggi',
            'urgent' => 'Mendesak'
        ];

        $oldLabel = $priorityLabels[$oldValue] ?? $oldValue;
        $newLabel = $priorityLabels[$newValue] ?? $newValue;

        return "Prioritas diubah dari '{$oldLabel}' menjadi '{$newLabel}'";
    }

    /**
     * Get project change message
     */
    private function getProjectChangeMessage($oldValue, $newValue): string
    {
        if (!$oldValue && $newValue) {
            $project = \App\Models\Project::find($newValue);
            return "Task ditambahkan ke proyek '" . ($project?->name ?? 'Unknown') . "'";
        }
        
        if ($oldValue && !$newValue) {
            return "Task dihapus dari proyek";
        }
        
        if ($oldValue && $newValue && $oldValue !== $newValue) {
            $oldProject = \App\Models\Project::find($oldValue);
            $newProject = \App\Models\Project::find($newValue);
            return "Proyek diubah dari '" . ($oldProject?->name ?? 'Unknown') . "' menjadi '" . ($newProject?->name ?? 'Unknown') . "'";
        }

        return "Proyek task diperbarui";
    }
}