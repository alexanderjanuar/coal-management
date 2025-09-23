<?php

namespace App\Filament\Resources\SuggestionResource\Widgets;

use App\Models\Suggestion;
use Filament\Forms\Components\RichEditor;
use Filament\Widgets\Widget;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use App\Models\User;
use Livewire\Attributes\On;
use Asmit\FilamentMention\Forms\Components\RichMentionEditor;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;

class CreateSuggestionWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'user_id' => auth()->id(),
            'status' => 'new',
            'priority' => 'low',
            'type' => 'other',
            'context_type' => 'general'
        ]);
    }

    public function create(): void
    {
        $data = $this->form->getState();
        
        try {
            Suggestion::create($data);
            
            // Reset form setelah berhasil
            $this->form->fill([
                'user_id' => auth()->id(),
                'status' => 'new',
                'priority' => 'low',
                'type' => 'other',
                'context_type' => 'general',
                'title' => '',
                'description' => '',
            ]);
            
            Notification::make()
                ->title('Berhasil!')
                ->body('Usulan pengembangan berhasil dikirim dan akan segera ditinjau oleh tim.')
                ->success()
                ->duration(5000)
                ->send();
                
            $this->dispatch('suggestion-created');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal!')
                ->body('Terjadi kesalahan saat menyimpan usulan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetForm(): void
    {
        $this->form->fill([
            'user_id' => auth()->id(),
            'status' => 'new',
            'priority' => 'low',
            'context_type' => 'general',
            'type' => 'other',
            'title' => '',
            'description' => '',
        ]);
        
        Notification::make()
            ->title('Form Direset')
            ->body('Form berhasil dikembalikan ke nilai awal.')
            ->info()
            ->send();
    }

    protected static string $view = 'filament.resources.suggestion-resource.widgets.create-suggestion-widget';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('✨ Usulan Pengembangan Sistem')
                    ->description('Sampaikan ide, saran, atau laporan untuk meningkatkan kualitas sistem')
                    ->icon('heroicon-o-light-bulb')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        TextInput::make('title')
                            ->label('📝 Judul Usulan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Tambahkan fitur notifikasi untuk deadline proyek')
                            ->helperText('Berikan judul yang jelas dan deskriptif')
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                Select::make('user_id')
                                    ->label('👤 Pengguna')
                                    ->options(fn() => User::pluck('name', 'id'))
                                    ->default(auth()->id())
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->helperText('Pengguna yang mengajukan usulan')
                                    ->columnSpan(1),
                                    
                                Select::make('type')
                                    ->label('🏷️ Kategori')
                                    ->required()
                                    ->options([
                                        'bug' => '🐛 Laporan Bug',
                                        'feature' => '✨ Fitur Baru',
                                        'improvement' => '🔧 Perbaikan',
                                        'other' => '💡 Lainnya',
                                    ])
                                    ->default('other')
                                    ->helperText('Pilih kategori yang sesuai')
                                    ->native(false)
                                    ->columnSpan(1),
                                    
                                Select::make('priority')
                                    ->label('🎯 Prioritas')
                                    ->required()
                                    ->options([
                                        'low' => '🟢 Rendah',
                                        'medium' => '🟡 Sedang',
                                        'high' => '🔴 Tinggi',
                                    ])
                                    ->default('low')
                                    ->helperText('Tingkat kepentingan')
                                    ->native(false)
                                    ->columnSpan(1),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                Select::make('priority')
                                    ->label('🎯 Tingkat Prioritas')
                                    ->required()
                                    ->options([
                                        'low' => '🟢 Rendah - Bisa ditunda',
                                        'medium' => '🟡 Sedang - Penting untuk ditangani',
                                        'high' => '🔴 Tinggi - Perlu segera ditangani',
                                    ])
                                    ->default('low')
                                    ->helperText('Seberapa mendesak usulan ini?')
                                    ->native(false)
                                    ->columnSpan(1),
                                    
                                Select::make('status')
                                    ->label('📊 Status')
                                    ->options([
                                        'new' => 'Baru',
                                    ])
                                    ->default('new')
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Status akan diperbarui oleh admin')
                                    ->columnSpan(1),
                            ]),

                        RichMentionEditor::make('description')
                            ->label('📋 Deskripsi Detail')
                            ->required()
                            ->lookupKey('name')
                            ->placeholder('Jelaskan usulan Anda secara detail:
                                • Apa masalah yang ingin diselesaikan?
                                • Bagaimana cara kerjanya?
                                • Apa manfaat yang diharapkan?
                                • Apakah ada contoh atau referensi?

                                Anda dapat mention pengguna lain dengan mengetik @ diikuti nama mereka.')
                            ->helperText('Gunakan @ untuk mention pengguna lain. Semakin detail penjelasan, semakin mudah untuk dipahami.')
                            ->columnSpanFull(),
                    ])
                    ->footerActions([
                        Action::make('submit')
                            ->label('🚀 Kirim Usulan')
                            ->color('primary')
                            ->size('lg')
                            ->action('create')
                            ->keyBindings(['mod+enter'])
                            ->requiresConfirmation()
                            ->modalHeading('Kirim Usulan Pengembangan')
                            ->modalDescription('Apakah Anda yakin ingin mengirim usulan ini? Usulan akan ditinjau oleh tim pengembang.')
                            ->modalSubmitActionLabel('Ya, Kirim Usulan')
                            ->modalCancelActionLabel('Batal')
                            ->modalIcon('heroicon-o-paper-airplane'),
                            
                        Action::make('reset')
                            ->label('🔄 Reset Form')
                            ->color('gray')
                            ->action('resetForm')
                            ->requiresConfirmation()
                            ->modalHeading('Reset Form')
                            ->modalDescription('Apakah Anda yakin ingin mereset form? Semua data yang sudah diisi akan hilang.')
                            ->modalSubmitActionLabel('Ya, Reset')
                            ->modalCancelActionLabel('Batal')
                            ->modalIcon('heroicon-o-arrow-path'),
                            
                        Action::make('draft')
                            ->label('💾 Simpan Draft')
                            ->color('warning')
                            ->outlined()
                            ->action(function () {
                                // Untuk future implementation - simpan sebagai draft
                                Notification::make()
                                    ->title('Info')
                                    ->body('Fitur simpan draft akan segera tersedia.')
                                    ->info()
                                    ->send();
                            })
                            ->visible(false), // Hide for now, enable when draft feature is ready
                        ]),
            ])
            ->statePath('data');
    }

    #[On('suggestion-created')]
    public function refreshWidget(): void
    {
        // Trigger refresh untuk parent component jika diperlukan
        $this->dispatch('$refresh');
    }
}