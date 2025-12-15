<?php

namespace App\Filament\Pages\ClientCommunication;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use App\Models\ClientCommunication;
use App\Models\Client;
use App\Models\User;
use App\Models\Project;

class Create extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.client-communication.create';
    
    protected static ?string $slug = 'client-communication.create';
    
    protected static ?string $title = 'Buat Komunikasi Baru';
    
    // Hide from navigation since it's accessed via button
    protected static bool $shouldRegisterNavigation = false;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill([
            'user_id' => auth()->id(),
            'status' => 'scheduled',
            'priority' => 'normal',
            'type' => 'meeting',
            'communication_date' => now()->format('Y-m-d'),
        ]);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Klien & Proyek')
                    ->description('Pilih klien dan proyek terkait')
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('client_id')
                                    ->label('Klien')
                                    ->options(fn() => Client::where('status', 'active')->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('project_id', null))
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nama Klien')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return Client::create(array_merge($data, ['status' => 'active']))->id;
                                    }),
                                
                                Select::make('project_id')
                                    ->label('Proyek Terkait')
                                    ->options(function (callable $get) {
                                        $clientId = $get('client_id');
                                        if (!$clientId) {
                                            return [];
                                        }
                                        return Project::where('client_id', $clientId)->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (callable $get) => !$get('client_id'))
                                    ->nullable()
                                    ->helperText('Pilih klien terlebih dahulu untuk melihat proyek'),
                            ]),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Informasi Dasar')
                    ->description('Masukkan detail utama komunikasi')
                    ->aside()
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Komunikasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Rapat Review Bulanan'),
                        
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
                            ->default('meeting')
                            ->native(false)
                            ->live(),
                        
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->placeholder('Berikan detail mengenai komunikasi ini...')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Jadwal & Lokasi')
                    ->description('Atur tanggal, waktu, dan lokasi')
                    ->aside()
                    ->schema([
                        DatePicker::make('communication_date')
                            ->label('Tanggal')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->columnSpanFull(),
                        
                        Grid::make(2)
                            ->schema([
                                TimePicker::make('communication_time_start')
                                    ->label('Waktu Mulai')
                                    ->required()
                                    ->native(false)
                                    ->seconds(false),
                                
                                TimePicker::make('communication_time_end')
                                    ->label('Waktu Selesai')
                                    ->native(false)
                                    ->seconds(false)
                                    ->after('communication_time_start'),
                            ]),
                        
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->placeholder('Kantor, Online, atau alamat spesifik')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('meeting_platform')
                                    ->label('Platform Meeting')
                                    ->placeholder('Zoom, Google Meet, Teams, dll.')
                                    ->maxLength(255)
                                    ->visible(fn ($get) => $get('type') === 'video_call'),
                                
                                TextInput::make('meeting_link')
                                    ->label('Link Meeting')
                                    ->url()
                                    ->placeholder('https://...')
                                    ->maxLength(500)
                                    ->visible(fn ($get) => $get('type') === 'video_call'),
                            ]),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Peserta & Penugasan')
                    ->description('Tambahkan peserta dan tentukan penanggung jawab')
                    ->aside()
                    ->schema([
                        Select::make('user_id')
                            ->label('Penanggung Jawab')
                            ->options(fn() => User::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(auth()->id()),
                        
                        TagsInput::make('client_participants')
                            ->label('Peserta dari Klien')
                            ->placeholder('Tambahkan nama peserta...')
                            ->helperText('Tekan Enter setelah setiap nama'),
                        
                        TagsInput::make('internal_participants')
                            ->label('Tim Internal')
                            ->placeholder('Tambahkan ID atau nama anggota tim...')
                            ->helperText('Tambahkan ID user atau nama peserta internal'),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Status & Prioritas')
                    ->description('Atur status dan tingkat prioritas')
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'scheduled' => 'Terjadwal',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        'rescheduled' => 'Dijadwalkan Ulang',
                                    ])
                                    ->required()
                                    ->default('scheduled')
                                    ->native(false)
                                    ->live(),
                                
                                Select::make('priority')
                                    ->label('Prioritas')
                                    ->options([
                                        'low' => 'Rendah',
                                        'normal' => 'Normal',
                                        'high' => 'Tinggi',
                                        'urgent' => 'Mendesak',
                                    ])
                                    ->required()
                                    ->default('normal')
                                    ->native(false),
                            ]),
                    ])
                    ->columnSpanFull(),
                
                Section::make('Informasi Tambahan')
                    ->description('Catatan dan hasil opsional')
                    ->aside()
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->placeholder('Tambahkan catatan tambahan...')
                            ->columnSpanFull(),
                        
                        Textarea::make('outcome')
                            ->label('Hasil/Kesimpulan')
                            ->rows(3)
                            ->placeholder('Dokumentasikan hasil setelah selesai...')
                            ->columnSpanFull()
                            ->visible(fn ($get) => in_array($get('status'), ['completed', 'cancelled'])),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }
    
    public function create(): void
    {
        $data = $this->form->getState();
        
        $communication = ClientCommunication::create($data);
        
        Notification::make()
            ->success()
            ->title('Komunikasi Berhasil Dibuat')
            ->body('Komunikasi telah berhasil dibuat.')
            ->send();
        
        $this->redirect(route('filament.admin.pages.client-communication'));
    }
    
    public static function canAccess(): bool
    {
        return auth()->user()->can('client-communication.*');
    }
    
    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Buat Komunikasi')
                ->submit('create')
                ->keyBindings(['mod+s']),
            
            Action::make('cancel')
                ->label('Batal')
                ->color('gray')
                ->url(route('filament.admin.pages.client-communication')),
        ];
    }
}