<?php

namespace App\Livewire\Client\Components;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Models\UserProject;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\Attributes\On;

class TimTab extends Component implements HasForms
{
    use InteractsWithForms;

    public Client $client;
    public $projects = [];
    public $dataLoaded = false;
    
    // Modal state
    public $selectedProjectId = null;
    public $userId = null;
    public $role = '';
    public $specializations = '';
    public $assignedDate = '';
    public $editingId = null;
    
    // Delete confirmation
    public $memberToDelete = null;

    public function mount(Client $client)
    {
        $this->client = $client;
    }

    // Load data hanya sekali saat tab dikunjungi
    public function loadData()
    {
        if (!$this->dataLoaded) {
            $this->projects = $this->client->projects()
                ->with(['userProjects.user'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            $this->dataLoaded = true;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('selectedProjectId')
                                    ->label('Project')
                                    ->options($this->client->projects()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->dehydrated()
                                    ->disabled($this->editingId !== null)
                                    ->columnSpan(2),
                                
                                Select::make('userId')
                                    ->label('Konsultan')
                                    ->options(User::query()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->dehydrated()
                                    ->disabled($this->editingId !== null)
                                    ->columnSpan(2),
                                
                                Select::make('role')
                                    ->label('Jabatan/Role')
                                    ->options([
                                        'Lead Consultant' => 'Lead Consultant',
                                        'Senior Tax Consultant' => 'Senior Tax Consultant',
                                        'Tax Consultant' => 'Tax Consultant',
                                        'Junior Tax Consultant' => 'Junior Tax Consultant',
                                        'Tax Advisor' => 'Tax Advisor',
                                        'Tax Specialist' => 'Tax Specialist',
                                        'Support Consultant' => 'Support Consultant',
                                        'Accounting Consultant' => 'Accounting Consultant',
                                        'Legal Consultant' => 'Legal Consultant',
                                        'Business Consultant' => 'Business Consultant',
                                        'Project Manager' => 'Project Manager',
                                        'Supervisor' => 'Supervisor',
                                        'Partner' => 'Partner',
                                        'Director' => 'Director',
                                        'Staff' => 'Staff',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->native(false)
                                    ->columnSpan(1),
                                
                                DatePicker::make('assignedDate')
                                    ->label('Tanggal Ditugaskan')
                                    ->default(now())
                                    ->native(false)
                                    ->columnSpan(1),
                                
                                Select::make('specializations')
                                    ->label('Spesialisasi/Area')
                                    ->options([
                                        // Tax Services
                                        'SPT Tahunan Badan' => 'SPT Tahunan Badan',
                                        'SPT Tahunan Pribadi' => 'SPT Tahunan Pribadi',
                                        'SPT Masa PPN' => 'SPT Masa PPN',
                                        'SPT Masa PPh' => 'SPT Masa PPh',
                                        'Tax Planning' => 'Tax Planning',
                                        'Tax Review' => 'Tax Review',
                                        'Tax Audit Assistance' => 'Tax Audit Assistance',
                                        'Tax Compliance' => 'Tax Compliance',
                                        'Transfer Pricing' => 'Transfer Pricing',
                                        'Tax Objection & Appeal' => 'Tax Objection & Appeal',
                                        
                                        // Accounting Services
                                        'Pembukuan' => 'Pembukuan',
                                        'Laporan Keuangan' => 'Laporan Keuangan',
                                        'Audit Laporan Keuangan' => 'Audit Laporan Keuangan',
                                        'Financial Review' => 'Financial Review',
                                        'Management Accounting' => 'Management Accounting',
                                        
                                        // Legal & Corporate
                                        'Pendirian PT' => 'Pendirian PT',
                                        'Pendirian CV' => 'Pendirian CV',
                                        'NIB (Nomor Induk Berusaha)' => 'NIB (Nomor Induk Berusaha)',
                                        'NPWP & PKP' => 'NPWP & PKP',
                                        'Izin Usaha' => 'Izin Usaha',
                                        'Legal Compliance' => 'Legal Compliance',
                                        'Corporate Secretary' => 'Corporate Secretary',
                                        
                                        // Consultation
                                        'Business Consultation' => 'Business Consultation',
                                        'Strategic Planning' => 'Strategic Planning',
                                        'Risk Management' => 'Risk Management',
                                        'Internal Control' => 'Internal Control',
                                        
                                        // Documentation
                                        'Dokumentasi' => 'Dokumentasi',
                                        'Review Dokumen' => 'Review Dokumen',
                                        'Compliance Check' => 'Compliance Check',
                                        
                                        // Support
                                        'Administrative Support' => 'Administrative Support',
                                        'Client Communication' => 'Client Communication',
                                        'Follow Up' => 'Follow Up',
                                    ])
                                    ->multiple()
                                    ->placeholder('Pilih satu atau lebih area spesialisasi')
                                    ->helperText('Pilih area spesialisasi yang ditangani konsultan ini')
                                    ->columnSpan(2),
                            ]),
                    ])
            ]);
    }

    public function openAssignModal($projectId = null)
    {
        $this->selectedProjectId = $projectId;
        $this->resetModalFields();
        $this->editingId = null;
        
        $this->dispatch('open-modal', id: 'assign-member-modal');
    }

    public function openEditModal($membershipId)
    {
        $membership = UserProject::with('user', 'project')->find($membershipId);
        
        if ($membership) {
            $this->editingId = $membershipId;
            $this->selectedProjectId = $membership->project_id;
            $this->userId = $membership->user_id;
            $this->role = $membership->role;
            
            // Handle specializations array
            $this->specializations = is_array($membership->specializations) 
                ? $membership->specializations 
                : ($membership->specializations ? [$membership->specializations] : []);
            
            $this->assignedDate = $membership->assigned_date?->format('Y-m-d');
            
            $this->dispatch('open-modal', id: 'assign-member-modal');
        }
    }

    public function saveMember()
    {
        $data = $this->form->getState();
        
        try {
            // Saat editing, gunakan selectedProjectId dari property, bukan dari form
            $projectId = $this->editingId ? $this->selectedProjectId : ($data['selectedProjectId'] ?? null);
            $userId = $this->editingId ? $this->userId : ($data['userId'] ?? null);
            
            // Validasi
            if (!$projectId || !$userId) {
                Notification::make()
                    ->title('Error!')
                    ->body('Project dan User harus dipilih.')
                    ->danger()
                    ->duration(3000)
                    ->send();
                return;
            }
            
            $memberData = [
                'project_id' => $projectId,
                'user_id' => $userId,
                'role' => $data['role'] ?? null,
                'specializations' => $data['specializations'] ?? null,
                'assigned_date' => $data['assignedDate'] ?? now(),
            ];

            if ($this->editingId) {
                UserProject::find($this->editingId)->update($memberData);
                $message = 'Anggota tim berhasil diperbarui!';
            } else {
                // Check if already exists
                $exists = UserProject::where('project_id', $projectId)
                    ->where('user_id', $userId)
                    ->exists();
                
                if ($exists) {
                    Notification::make()
                        ->title('Peringatan!')
                        ->body('Konsultan ini sudah ditugaskan di project ini.')
                        ->warning()
                        ->duration(3000)
                        ->send();
                    return;
                }
                
                UserProject::create($memberData);
                $message = 'Anggota tim berhasil ditambahkan!';
            }

            $this->closeModal();
            $this->refreshData();
            
            Notification::make()
                ->title('Berhasil!')
                ->body($message)
                ->success()
                ->duration(3000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menyimpan anggota tim: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function deleteConfirm($membershipId)
    {
        $this->memberToDelete = $membershipId;
        $this->dispatch('open-modal', id: 'delete-member-modal');
    }

    public function deleteMember()
    {
        try {
            if (!$this->memberToDelete) {
                return;
            }
            
            $membership = UserProject::find($this->memberToDelete);
            if ($membership) {
                $membership->delete();
                $this->refreshData();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Anggota tim berhasil dihapus!')
                    ->success()
                    ->duration(3000)
                    ->send();
            }
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menghapus anggota tim: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function refreshData()
    {
        $this->projects = $this->client->projects()
            ->with(['userProjects.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function closeModal()
    {
        $this->resetModalFields();
        $this->dispatch('close-modal', id : 'assign-member-modal');
    }

    public function closeDeleteModal()
    {
        $this->memberToDelete = null;
        $this->dispatch('close-modal', id : 'delete-member-modal');
    }

    private function resetModalFields()
    {
        $this->userId = null;
        $this->role = '';
        $this->specializations = '';
        $this->assignedDate = '';
        $this->editingId = null;
        
        if (!$this->selectedProjectId) {
            $this->selectedProjectId = null;
        }
    }

    #[On('refresh-tim')]
    public function refresh()
    {
        $this->refreshData();
    }

    public function render()
    {
        // Auto load data saat render pertama kali
        $this->loadData();
        
        return view('livewire.client.components.tim-tab');
    }
}