<?php

namespace App\Livewire\Client\Components;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Models\Sop;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Livewire\Component;
use Livewire\Attributes\On;

class ProjekTab extends Component implements HasForms
{
    use InteractsWithForms;

    public Client $client;
    public $projects = [];
    public $stats = [];
    
    // Modal state
    public $editingId = null;
    public $name = '';
    public $description = '';
    public $priority = 'normal';
    public $type = 'single';
    public $due_date = '';
    public $status = 'draft';
    public $sop_id = null;
    public $pic_id = null;
    
    // Delete confirmation
    public $projectToDelete = null;

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->loadProjects();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Proyek')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Laporan Pajak Tahunan 2025')
                                    ->columnSpan(2),
                                
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(3)
                                    ->placeholder('Deskripsi proyek...')
                                    ->columnSpan(2),
                                
                                Select::make('type')
                                    ->label('Tipe Proyek')
                                    ->options([
                                        'single' => 'Single (Sekali Jalan)',
                                        'monthly' => 'Monthly (Bulanan)',
                                        'yearly' => 'Yearly (Tahunan)',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(1),
                                
                                Select::make('priority')
                                    ->label('Prioritas')
                                    ->options([
                                        'urgent' => 'Urgent',
                                        'normal' => 'Normal',
                                        'low' => 'Low',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('normal')
                                    ->columnSpan(1),
                                
                                DatePicker::make('due_date')
                                    ->label('Deadline')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->after('today')
                                    ->columnSpan(1),
                                
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'analysis' => 'Analysis',
                                        'in_progress' => 'In Progress',
                                        'review' => 'Review',
                                        'completed' => 'Completed',
                                        'completed (Not Payed Yet)' => 'Completed (Not Payed Yet)',
                                        'canceled' => 'Canceled',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->default('draft')
                                    ->columnSpan(1),
                                
                                Select::make('sop_id')
                                    ->label('SOP (Opsional)')
                                    ->options(Sop::pluck('name', 'id'))
                                    ->searchable()
                                    ->native(false)
                                    ->columnSpan(1),
                                
                                Select::make('pic_id')
                                    ->label('PIC (Person In Charge)')
                                    ->options(User::pluck('name', 'id'))
                                    ->searchable()
                                    ->native(false)
                                    ->columnSpan(1),
                            ]),
                    ])
            ]);
    }

    public function loadProjects()
    {
        $this->projects = $this->client->projects()
            ->with(['pic', 'sop', 'teamMembers'])
            ->latest()
            ->get();
        
        $this->calculateStats();
    }

    public function calculateStats()
    {
        $this->stats = [
            'total' => $this->projects->count(),
            'in_progress' => $this->projects->whereIn('status', ['in_progress', 'analysis', 'review'])->count(),
            'completed' => $this->projects->where('status', 'completed')->count(),
            'urgent' => $this->projects->where('priority', 'urgent')->count(),
        ];
    }

    public function openCreateModal()
    {
        $this->resetModalFields();
        $this->editingId = null;
        $this->dispatch('open-modal', id: 'project-modal');
    }

    public function openEditModal($projectId)
    {
        $project = Project::find($projectId);
        
        if ($project) {
            $this->editingId = $projectId;
            $this->name = $project->name;
            $this->description = $project->description;
            $this->priority = $project->priority;
            $this->type = $project->type;
            $this->due_date = $project->due_date->format('Y-m-d');
            $this->status = $project->status;
            $this->sop_id = $project->sop_id;
            $this->pic_id = $project->pic_id;
            
            $this->dispatch('open-modal', id: 'project-modal');
        }
    }

    public function saveProject()
    {
        $data = $this->form->getState();
        
        try {
            $projectData = [
                'client_id' => $this->client->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'],
                'type' => $data['type'],
                'due_date' => $data['due_date'],
                'status' => $data['status'],
                'sop_id' => $data['sop_id'] ?? null,
                'pic_id' => $data['pic_id'] ?? null,
            ];

            if ($this->editingId) {
                Project::find($this->editingId)->update($projectData);
                $message = 'Proyek berhasil diperbarui!';
            } else {
                Project::create($projectData);
                $message = 'Proyek berhasil ditambahkan!';
            }

            $this->closeModal();
            $this->loadProjects();
            
            Notification::make()
                ->title('Berhasil!')
                ->body($message)
                ->success()
                ->duration(3000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menyimpan proyek: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function deleteConfirm($projectId)
    {
        $this->projectToDelete = $projectId;
        $this->dispatch('open-modal', id: 'delete-project-modal');
    }

    public function deleteProject()
    {
        try {
            if (!$this->projectToDelete) {
                return;
            }
            
            $project = Project::find($this->projectToDelete);
            if ($project) {
                $project->delete();
                $this->loadProjects();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Proyek berhasil dihapus!')
                    ->success()
                    ->duration(3000)
                    ->send();
            }
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menghapus proyek: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function closeModal()
    {
        $this->resetModalFields();
        $this->dispatch('close-modal', id: 'project-modal');
    }

    public function closeDeleteModal()
    {
        $this->projectToDelete = null;
        $this->dispatch('close-modal', id: 'delete-project-modal');
    }

    private function resetModalFields()
    {
        $this->name = '';
        $this->description = '';
        $this->priority = 'normal';
        $this->type = 'single';
        $this->due_date = '';
        $this->status = 'draft';
        $this->sop_id = null;
        $this->pic_id = null;
        $this->editingId = null;
    }

    #[On('refresh-projects')]
    public function refresh()
    {
        $this->loadProjects();
    }

    public function render()
    {
        return view('livewire.client.components.projek-tab');
    }
}