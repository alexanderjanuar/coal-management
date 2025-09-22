<?php

namespace App\Traits;

use App\Models\UserActivity;

trait Trackable
{
    /**
     * Log aktivitas untuk model ini
     */
    public function logActivity(string $action, string $description): UserActivity
    {
        // Tambahkan user name ke description
        $userName = auth()->user()?->name ?? 'System';
        $descriptionWithUser = $description . " oleh {$userName}";
        
        $data = [
            'action' => $action,
            'description' => $descriptionWithUser,
            'actionable_type' => get_class($this),
            'actionable_id' => $this->id,
        ];

        // Auto-detect client and project
        if (method_exists($this, 'client') && $this->client) {
            $data['client_id'] = $this->client->id;
        } elseif (method_exists($this, 'getClientAttribute') && $this->client) {
            $data['client_id'] = $this->client->id;
        }

        if (method_exists($this, 'project') && $this->project) {
            $data['project_id'] = $this->project->id;
        }

        return UserActivity::log($data);
    }

    /**
     * Log perubahan dengan data lama dan baru
     */
    public function logChange(string $action, array $oldValues, array $newValues): UserActivity
    {
        $modelName = class_basename($this);
        $displayName = $this->name ?? $this->title ?? "#{$this->id}";
        $userName = auth()->user()?->name ?? 'System';
        
        return UserActivity::log([
            'action' => "{$modelName}_{$action}",
            'description' => "{$modelName} '{$displayName}' telah {$action} oleh {$userName}",
            'actionable_type' => get_class($this),
            'actionable_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'client_id' => $this->getClientId(),
            'project_id' => $this->getProjectId(),
        ]);
    }

    /**
     * Helper untuk mendapatkan client_id
     */
    protected function getClientId(): ?int
    {
        // Special handling untuk SubmittedDocument
        if (method_exists($this, 'getSubmittedDocumentClientId')) {
            return $this->getSubmittedDocumentClientId();
        }
        
        if (method_exists($this, 'client') && $this->client) {
            return $this->client->id;
        }
        
        if (isset($this->client_id)) {
            return $this->client_id;
        }

        return null;
    }

    /**
     * Helper untuk mendapatkan project_id
     */
    protected function getProjectId(): ?int
    {
        // Special handling untuk SubmittedDocument
        if (method_exists($this, 'getSubmittedDocumentProjectId')) {
            return $this->getSubmittedDocumentProjectId();
        }
        
        if (method_exists($this, 'project') && $this->project) {
            return $this->project->id;
        }
        
        if (isset($this->project_id)) {
            return $this->project_id;
        }

        return null;
    }

    /**
     * Get user activities untuk model ini (berbeda dari Spatie's activities)
     */
    public function userActivities()
    {
        return UserActivity::where('actionable_type', get_class($this))
                          ->where('actionable_id', $this->id)
                          ->orderBy('created_at', 'desc');
    }
}