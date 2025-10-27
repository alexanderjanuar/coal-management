<?php

namespace App\Observers;

use App\Models\DailyTaskSubTask;
use App\Models\Comment;

class DailyTaskSubTaskObserver
{
    /**
     * Handle the DailyTaskSubTask "created" event.
     */
    public function created(DailyTaskSubTask $subtask): void
    {
        if (!auth()->check()) {
            return;
        }

        $subtask->dailyTask->comments()->create([
            'user_id' => auth()->id(),
            'content' => "Subtask baru ditambahkan: '{$subtask->title}'",
            'status' => 'approved',
        ]);
    }

    /**
     * Handle the DailyTaskSubTask "updated" event.
     */
    public function updated(DailyTaskSubTask $subtask): void
    {
        if (!auth()->check()) {
            return;
        }

        $changes = $subtask->getDirty();
        $original = $subtask->getOriginal();

        foreach ($changes as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            
            // Skip jika nilai sama atau field yang tidak perlu di-track
            if ($oldValue === $newValue || in_array($field, ['updated_at', 'created_at'])) {
                continue;
            }

            $this->createSubtaskActivityComment($subtask, $field, $oldValue, $newValue);
        }
    }

    /**
     * Handle the DailyTaskSubTask "deleting" event.
     */
    public function deleting(DailyTaskSubTask $subtask): void
    {
        if (!auth()->check()) {
            return;
        }

        $subtask->dailyTask->comments()->create([
            'user_id' => auth()->id(),
            'content' => "Subtask dihapus: '{$subtask->title}'",
            'status' => 'approved',
        ]);
    }

    /**
     * Handle the DailyTaskSubTask "deleted" event.
     */
    public function deleted(DailyTaskSubTask $subtask): void
    {
        // Log untuk tracking
        \Log::info("Subtask '{$subtask->title}' dari task '{$subtask->dailyTask->title}' telah dihapus oleh " . (auth()->user()->name ?? 'System'));
    }

    /**
     * Create activity comment for subtask changes
     */
    private function createSubtaskActivityComment(DailyTaskSubTask $subtask, string $field, $oldValue, $newValue): void
    {
        $message = $this->generateSubtaskChangeMessage($subtask, $field, $oldValue, $newValue);
        
        if ($message) {
            $subtask->dailyTask->comments()->create([
                'user_id' => auth()->id(),
                'content' => $message,
                'status' => 'approved',
            ]);
        }
    }

    /**
     * Generate appropriate message for subtask field changes
     */
    private function generateSubtaskChangeMessage(DailyTaskSubTask $subtask, string $field, $oldValue, $newValue): ?string
    {
        return match($field) {
            'status' => $this->getSubtaskStatusChangeMessage($subtask->title, $oldValue, $newValue),
            'title' => "Subtask diubah dari '{$oldValue}' menjadi '{$newValue}'",
            default => null
        };
    }

    /**
     * Get status change message for subtasks
     */
    private function getSubtaskStatusChangeMessage(string $title, $oldValue, $newValue): string
    {
        $statusLabels = [
            'pending' => 'tertunda',
            'in_progress' => 'sedang dikerjakan',
            'completed' => 'selesai',
            'cancelled' => 'dibatalkan'
        ];

        $oldLabel = $statusLabels[$oldValue] ?? $oldValue;
        $newLabel = $statusLabels[$newValue] ?? $newValue;

        // Special message for completion
        if ($newValue === 'completed') {
            return "Subtask selesai: '{$title}'";
        }
        
        // Special message for reopening
        if ($oldValue === 'completed' && $newValue !== 'completed') {
            return "Subtask dibuka kembali: '{$title}' (dari selesai menjadi {$newLabel})";
        }

        return "Status subtask '{$title}' diubah dari {$oldLabel} menjadi {$newLabel}";
    }
}