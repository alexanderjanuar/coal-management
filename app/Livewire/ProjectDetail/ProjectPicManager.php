<?php

namespace App\Livewire\ProjectDetail;

use App\Models\Project;
use App\Models\User;
use Livewire\Component;
use Filament\Notifications\Notification;

class ProjectPicManager extends Component
{
    public Project $project;
    public $showChangePicModal = false;

    protected $listeners = [
        'open-change-pic-modal' => 'openChangePicModal',
        'close-change-pic-modal' => 'closeChangePicModal',
    ];

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function openChangePicModal()
    {
        $this->showChangePicModal = true;
    }

    public function closeChangePicModal()
    {
        $this->showChangePicModal = false;
    }

    /**
     * Assign a new PIC to the project
     */
    public function assignPic($userId)
    {
        // Check permissions
        if (auth()->user()->hasRole(['staff', 'client'])) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menugaskan PIC.')
                ->danger()
                ->send();
            return;
        }

        try {
            $user = User::findOrFail($userId);
            
            // Verify user is assigned to this client
            if (!$user->userClients()->where('client_id', $this->project->client_id)->exists()) {
                Notification::make()
                    ->title('Pilihan Tidak Valid')
                    ->body('Pengguna yang dipilih tidak ditugaskan ke klien ini.')
                    ->danger()
                    ->send();
                return;
            }

            $previousPic = $this->project->pic;
            
            // Assign new PIC
            $this->project->update(['pic_id' => $userId]);

            // Close modal and refresh project
            $this->closeChangePicModal();
            $this->dispatch('close-modal', id: 'pic-modal');
            $this->project->refresh();

            // Send notifications
            $this->sendPicChangeNotification($user, $previousPic, 'assigned');

            Notification::make()
                ->title('PIC Diperbarui')
                ->body("Berhasil menugaskan {$user->name} sebagai Penanggung Jawab.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Log::error('Failed to assign PIC', [
                'project_id' => $this->project->id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Kesalahan')
                ->body('Gagal menugaskan PIC. Silakan coba lagi.')
                ->danger()
                ->send();
        }
    }

    /**
     * Remove PIC from the project
     */
    public function removePic()
    {
        // Check permissions
        if (auth()->user()->hasRole(['staff', 'client'])) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menghapus PIC.')
                ->danger()
                ->send();
            return;
        }

        try {
            $previousPic = $this->project->pic;
            
            if (!$previousPic) {
                Notification::make()
                    ->title('Tidak Ada PIC yang Ditugaskan')
                    ->body('Proyek ini belum memiliki PIC yang ditugaskan.')
                    ->warning()
                    ->send();
                return;
            }

            // Remove PIC
            $this->project->update(['pic_id' => null]);

            // Close modal and refresh project
            $this->dispatch('close-modal', id: 'pic-modal');
            $this->project->refresh();

            // Send notifications
            $this->sendPicChangeNotification(null, $previousPic, 'removed');

            Notification::make()
                ->title('PIC Dihapus')
                ->body("Berhasil menghapus {$previousPic->name} sebagai Penanggung Jawab.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            \Log::error('Failed to remove PIC', [
                'project_id' => $this->project->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Error')
                ->body('Failed to remove PIC. Please try again.')
                ->danger()
                ->send();
        }
    }

    /**
     * Get available users for PIC assignment
     */
    public function getAvailableUsersProperty()
    {
        return User::whereHas('userClients', function($query) {
            $query->where('client_id', $this->project->client_id);
        })->with('roles')->get();
    }

    /**
     * Send notifications when PIC changes
     */
    private function sendPicChangeNotification($newPic, $previousPic, $action)
    {
        $projectName = $this->project->name;
        $clientName = $this->project->client->name;
        
        switch ($action) {
            case 'assigned':
                $title = 'Penugasan PIC';
                if ($previousPic) {
                    $body = "PIC untuk proyek '{$projectName}' telah diubah dari {$previousPic->name} ke {$newPic->name}.";
                } else {
                    $body = "{$newPic->name} telah ditugaskan sebagai PIC untuk proyek '{$projectName}'.";
                }
                
                // Notify the new PIC
                if ($newPic && $newPic->id !== auth()->id()) {
                    Notification::make()
                        ->title($title)
                        ->body("Anda telah ditugaskan sebagai Penanggung Jawab untuk proyek '{$projectName}' ({$clientName}).")
                        ->success()
                        ->sendToDatabase($newPic)
                        ->broadcast($newPic);
                }
                
                // Notify the previous PIC if there was one
                if ($previousPic && $previousPic->id !== auth()->id()) {
                    Notification::make()
                        ->title($title)
                        ->body("Anda tidak lagi menjadi PIC untuk proyek '{$projectName}'. {$newPic->name} telah ditugaskan sebagai PIC yang baru.")
                        ->warning()
                        ->sendToDatabase($previousPic)
                        ->broadcast($previousPic);
                }
                break;
                
            case 'removed':
                $title = 'PIC Dihapus';
                $body = "PIC untuk proyek '{$projectName}' telah dihapus.";
                
                // Notify the removed PIC
                if ($previousPic && $previousPic->id !== auth()->id()) {
                    Notification::make()
                        ->title($title)
                        ->body("Anda tidak lagi menjadi Penanggung Jawab untuk proyek '{$projectName}' ({$clientName}).")
                        ->warning()
                        ->sendToDatabase($previousPic)
                        ->broadcast($previousPic);
                }
                break;
        }
        
        // Notify all project team members
        $teamMembers = $this->project->users()
            ->where('users.id', '!=', auth()->id())
            ->when($newPic, fn($query) => $query->where('users.id', '!=', $newPic->id))
            ->when($previousPic, fn($query) => $query->where('users.id', '!=', $previousPic->id))
            ->get();
        
        foreach ($teamMembers as $member) {
            Notification::make()
                ->title($title)
                ->body($body)
                ->info()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('View Project')
                        ->url(route('filament.admin.resources.projects.view', ['record' => $this->project->id])),
                ])
                ->sendToDatabase($member)
                ->broadcast($member);
        }
        
        // Log activity
        activity()
            ->performedOn($this->project)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => $action,
                'new_pic' => $newPic ? [
                    'id' => $newPic->id,
                    'name' => $newPic->name,
                    'email' => $newPic->email
                ] : null,
                'previous_pic' => $previousPic ? [
                    'id' => $previousPic->id,
                    'name' => $previousPic->name,
                    'email' => $previousPic->email
                ] : null,
            ])
            ->log($action === 'assigned' 
                ? ($previousPic ? 'PIC changed' : 'PIC assigned')
                : 'PIC removed'
            );
    }

    public function render()
    {
        return view('livewire.project-detail.project-pic-manager');
    }
}