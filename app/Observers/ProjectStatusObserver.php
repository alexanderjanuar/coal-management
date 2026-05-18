<?php

namespace App\Observers;

use App\Models\ProjectStatus;
use RuntimeException;

class ProjectStatusObserver
{
    /**
     * Block deletion of system statuses (draft / completed / canceled)
     * and statuses that still have projects pointing at them.
     *
     * Phase 4 will add a reassignment flow ("move all projects from
     * X to Y, then delete X"). Until then, the user must manually
     * change all projects off this status before deleting.
     */
    public function deleting(ProjectStatus $status): bool
    {
        if ($status->is_system) {
            throw new RuntimeException(
                "Status '{$status->label}' adalah sistem dan tidak bisa dihapus."
            );
        }

        if ($status->projects()->exists()) {
            $count = $status->projects()->count();
            throw new RuntimeException(
                "Status '{$status->label}' masih digunakan oleh {$count} proyek. " .
                "Ubah status proyek-proyek tersebut terlebih dahulu."
            );
        }

        return true;
    }
}
