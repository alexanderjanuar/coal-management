<?php

namespace App\Livewire\Client\Components;

use App\Models\Client;
use App\Models\ClientCommunication;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\Attributes\On;

class KomunikasiTab extends Component implements HasForms
{
    use InteractsWithForms;

    public Client $client;
    public $communications = [];
    
    // Modal state
    public $title = '';
    public $description = '';
    public $type = 'other';
    public $communication_date = '';
    public $communication_time = '';
    public $notes = '';
    public $editingId = null;
    
    // Delete confirmation
    public $communicationToDelete = null;

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->loadCommunications();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Komunikasi')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Konsultasi SPT Tahunan 2024')
                                    ->columnSpan(2),
                                
                                Select::make('type')
                                    ->label('Jenis Komunikasi')
                                    ->options([
                                        'meeting' => 'Meeting',
                                        'email' => 'Email',
                                        'phone' => 'Telepon',
                                        'video_call' => 'Video Call',
                                        'other' => 'Lainnya',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(2),
                                
                                DatePicker::make('communication_date')
                                    ->label('Tanggal')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),
                                
                                TimePicker::make('communication_time')
                                    ->label('Waktu')
                                    ->seconds(false)
                                    ->columnSpan(1),
                                
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(3)
                                    ->placeholder('Deskripsi singkat tentang komunikasi ini')
                                    ->columnSpan(2),
                                
                                Textarea::make('notes')
                                    ->label('Catatan Tambahan')
                                    ->rows(3)
                                    ->placeholder('Catatan atau hasil dari komunikasi')
                                    ->columnSpan(2),
                            ]),
                    ])
            ]);
    }

    public function loadCommunications()
    {
        $this->communications = $this->client->communications()
            ->with('user')
            ->latest('communication_date')
            ->latest('communication_time')
            ->get();
    }

    public function openCreateModal()
    {
        $this->resetModalFields();
        $this->editingId = null;
        $this->dispatch('open-modal', id: 'communication-modal');
    }

    public function openEditModal($communicationId)
    {
        $communication = ClientCommunication::find($communicationId);
        
        if ($communication) {
            $this->editingId = $communicationId;
            $this->title = $communication->title;
            $this->description = $communication->description;
            $this->type = $communication->type;
            $this->communication_date = $communication->communication_date->format('Y-m-d');
            $this->communication_time = $communication->communication_time;
            $this->notes = $communication->notes;
            
            $this->dispatch('open-modal', id: 'communication-modal');
        }
    }

    public function saveCommunication()
    {
        $data = $this->form->getState();
        
        try {
            $communicationData = [
                'client_id' => $this->client->id,
                'user_id' => auth()->id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'communication_date' => $data['communication_date'],
                'communication_time' => $data['communication_time'] ?? null,
                'notes' => $data['notes'] ?? null,
            ];

            if ($this->editingId) {
                ClientCommunication::find($this->editingId)->update($communicationData);
                $message = 'Catatan komunikasi berhasil diperbarui!';
            } else {
                ClientCommunication::create($communicationData);
                $message = 'Catatan komunikasi berhasil ditambahkan!';
            }

            $this->closeModal();
            $this->loadCommunications();
            
            Notification::make()
                ->title('Berhasil!')
                ->body($message)
                ->success()
                ->duration(3000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menyimpan catatan: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function deleteConfirm($communicationId)
    {
        $this->communicationToDelete = $communicationId;
        $this->dispatch('open-modal', id: 'delete-communication-modal');
    }

    public function deleteCommunication()
    {
        try {
            if (!$this->communicationToDelete) {
                return;
            }
            
            $communication = ClientCommunication::find($this->communicationToDelete);
            if ($communication) {
                $communication->delete();
                $this->loadCommunications();
                
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Catatan komunikasi berhasil dihapus!')
                    ->success()
                    ->duration(3000)
                    ->send();
            }
            
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error!')
                ->body('Gagal menghapus catatan: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    public function closeModal()
    {
        $this->resetModalFields();
        $this->dispatch('close-modal', id : 'communication-modal');
    }

    public function closeDeleteModal()
    {
        $this->communicationToDelete = null;
        $this->dispatch('close-modal', id : 'delete-communication-modal');
    }

    private function resetModalFields()
    {
        $this->title = '';
        $this->description = '';
        $this->type = 'other';
        $this->communication_date = '';
        $this->communication_time = '';
        $this->notes = '';
        $this->editingId = null;
    }

    #[On('refresh-communications')]
    public function refresh()
    {
        $this->loadCommunications();
    }

    public function render()
    {
        return view('livewire.client.components.komunikasi-tab');
    }
}