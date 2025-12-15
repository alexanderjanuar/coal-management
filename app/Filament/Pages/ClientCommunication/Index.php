<?php

namespace App\Filament\Pages\ClientCommunication;

use Filament\Pages\Page;
use App\Models\ClientCommunication;
use App\Models\Client;
use Livewire\WithPagination;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use CodeWithKyrian\FilamentDateRange\Forms\Components\DateRangePicker;
use Illuminate\Support\Facades\Storage;

class Index extends Page implements HasForms
{
    use WithPagination, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'Komunikasi Klien';
    
    protected static ?string $navigationGroup = 'Client Management';
    protected static ?int $navigationSort = 2;

    protected static ?string $title = '';

    protected static ?string $slug = 'client-communication';
    
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->can('client-communication.*');
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()->can('client-communication.*');
    }
    
    protected static string $view = 'filament.pages.client-communication.index';
    
    // Public properties for filters
    public $activeTab = 'all';
    public $event_period = null;
    public $searchTerm = '';
    
    // Modal properties
    public $selectedCommunicationId = null;
    public $completeFormData = [];
    public $viewCommunication = null;
    
    public function mount(): void
    {
        $this->form->fill([
            'event_period' => null,
        ]);
    }
    
    protected function getForms(): array
    {
        return [
            'form',
            'completeForm',
        ];
    }
    
    public function completeForm(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema($this->getCompleteFormSchema())
            ->statePath('completeFormData');
    }
    
    protected function getFormSchema(): array
    {
        return [
            DateRangePicker::make('event_period')
                ->label('Tanggal Komunikasi')
                ->reactive(),
        ];
    }
    
    protected function getCompleteFormSchema(): array
    {
        return [
            \Filament\Forms\Components\RichEditor::make('outcome')
                ->label('Hasil/Kesimpulan')
                ->required()
                ->placeholder('Dokumentasikan hasil komunikasi...')
                ->helperText('Jelaskan apa yang dibahas dan kesimpulan dari komunikasi ini')
                ->toolbarButtons([
                    'bold',
                    'italic',
                    'underline',
                    'strike',
                    'bulletList',
                    'orderedList',
                    'h2',
                    'h3',
                    'blockquote',
                    'codeBlock',
                ])
                ->columnSpanFull(),
            
            \Filament\Forms\Components\FileUpload::make('attachments')
                ->label('Lampiran')
                ->multiple()
                ->directory('client-communications/attachments')
                ->maxSize(5120)
                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->helperText('Upload dokumen pendukung (PDF, gambar, atau Word). Maksimal 5MB per file.')
                ->downloadable()
                ->previewable()
                ->columnSpanFull(),
        ];
    }
    
    public function openCompleteModal($communicationId)
    {
        $this->selectedCommunicationId = $communicationId;
        
        $communication = ClientCommunication::find($communicationId);
        
        $this->completeForm->fill([
            'outcome' => $communication->outcome ?? '',
            'attachments' => $communication->attachments ?? [],
        ]);
        
        $this->dispatch('open-modal', id: 'complete-communication-modal');
    }
    
    public function closeCompleteModal()
    {
        $this->selectedCommunicationId = null;
        $this->completeForm->fill([]);
        $this->dispatch('close-modal', id: 'complete-communication-modal');
    }
    
    public function markAsCompleted()
    {
        $data = $this->completeForm->getState();
        
        $communication = ClientCommunication::findOrFail($this->selectedCommunicationId);
        
        $communication->update([
            'status' => 'completed',
            'outcome' => $data['outcome'],
            'attachments' => $data['attachments'] ?? [],
        ]);
        
        \Filament\Notifications\Notification::make()
            ->success()
            ->title('Komunikasi Ditandai Selesai')
            ->body('Komunikasi telah berhasil ditandai sebagai selesai.')
            ->send();
        
        $this->closeCompleteModal();
        
        // Refresh the list
        $this->dispatch('$refresh');
    }
    
    public function openViewModal($communicationId)
    {
        $this->viewCommunication = ClientCommunication::with(['client', 'user', 'project'])
            ->findOrFail($communicationId);
        
        $this->dispatch('open-modal', id: 'view-communication-modal');
    }
    
    public function closeViewModal()
    {
        $this->viewCommunication = null;
        $this->dispatch('close-modal', id: 'view-communication-modal');
    }
    
    public function clearDateRange()
    {
        $this->event_period = null;
        $this->form->fill([
            'event_period' => null,
        ]);
    }
    
    protected function applyDateRangeFilter($query)
    {
        // Apply date range filter if set
        if ($this->event_period && is_array($this->event_period)) {
            if (isset($this->event_period['start']) && $this->event_period['start']) {
                $query->where('communication_date', '>=', $this->event_period['start']);
            }
            if (isset($this->event_period['end']) && $this->event_period['end']) {
                $query->where('communication_date', '<=', $this->event_period['end']);
            }
        }
        
        return $query;
    }
    
    public function getAllCommunications()
    {
        $query = ClientCommunication::query()
            ->select([
                'id',
                'client_id',
                'user_id',
                'title',
                'description',
                'type',
                'communication_date',
                'communication_time_start',
                'communication_time_end',
                'location',
                'status',
                'internal_participants'
            ])
            ->with([
                'client:id,name',
                'user:id,name'
            ]);
        
        $query = $this->applyDateRangeFilter($query);
        
        return $query->orderBy('communication_date', 'desc')
            ->orderBy('communication_time_start', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->communication_date->format('Y-m-d');
            });
    }
    
    public function getScheduledCommunications()
    {
        $query = ClientCommunication::query()
            ->select([
                'id',
                'client_id',
                'user_id',
                'title',
                'description',
                'type',
                'communication_date',
                'communication_time_start',
                'communication_time_end',
                'location',
                'status',
                'internal_participants'
            ])
            ->with([
                'client:id,name',
                'user:id,name'
            ])
            ->where('status', 'scheduled');
        
        $query = $this->applyDateRangeFilter($query);
        
        return $query->orderBy('communication_date')
            ->orderBy('communication_time_start')
            ->get()
            ->groupBy(function($item) {
                return $item->communication_date->format('Y-m-d');
            });
    }
    
    public function getCompletedCommunications()
    {
        $query = ClientCommunication::query()
            ->select([
                'id',
                'client_id',
                'user_id',
                'title',
                'description',
                'type',
                'communication_date',
                'communication_time_start',
                'communication_time_end',
                'location',
                'status',
                'internal_participants'
            ])
            ->with([
                'client:id,name',
                'user:id,name'
            ])
            ->where('status', 'completed');
        
        $query = $this->applyDateRangeFilter($query);
        
        return $query->orderBy('communication_date', 'desc')
            ->orderBy('communication_time_start', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->communication_date->format('Y-m-d');
            });
    }
    
    public function getCancelledCommunications()
    {
        $query = ClientCommunication::query()
            ->select([
                'id',
                'client_id',
                'user_id',
                'title',
                'description',
                'type',
                'communication_date',
                'communication_time_start',
                'communication_time_end',
                'location',
                'status',
                'internal_participants'
            ])
            ->with([
                'client:id,name',
                'user:id,name'
            ])
            ->where('status', 'cancelled');
        
        $query = $this->applyDateRangeFilter($query);
        
        return $query->orderBy('communication_date', 'desc')
            ->orderBy('communication_time_start', 'desc')
            ->get()
            ->groupBy(function($item) {
                return $item->communication_date->format('Y-m-d');
            });
    }
    
    public function getRescheduledCommunications()
    {
        $query = ClientCommunication::query()
            ->select([
                'id',
                'client_id',
                'user_id',
                'title',
                'description',
                'type',
                'communication_date',
                'communication_time_start',
                'communication_time_end',
                'location',
                'status',
                'internal_participants'
            ])
            ->with([
                'client:id,name',
                'user:id,name'
            ])
            ->where('status', 'rescheduled');
        
        $query = $this->applyDateRangeFilter($query);
        
        return $query->orderBy('communication_date')
            ->orderBy('communication_time_start')
            ->get()
            ->groupBy(function($item) {
                return $item->communication_date->format('Y-m-d');
            });
    }
    
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    // Computed property for the view
    public function getCommunicationsByMonthProperty()
    {
        $communications = match($this->activeTab) {
            'scheduled' => $this->getScheduledCommunications(),
            'completed' => $this->getCompletedCommunications(),
            'cancelled' => $this->getCancelledCommunications(),
            'rescheduled' => $this->getRescheduledCommunications(),
            default => $this->getAllCommunications(),
        };
        
        // Group by month and year
        return $communications->groupBy(function($items, $date) {
            return Carbon::parse($date)->format('F Y');
        })->map(function($monthGroup) {
            return $monthGroup;
        });
    }
}