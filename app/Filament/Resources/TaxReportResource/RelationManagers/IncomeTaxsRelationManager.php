<?php

namespace App\Filament\Resources\TaxReportResource\RelationManagers;

use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\RawJs;

class IncomeTaxsRelationManager extends RelationManager
{
    protected static string $relationship = 'incomeTaxs';

    protected static ?string $title = 'PPh';


    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
                    
                Wizard::make([
                    Wizard\Step::make('Pilih Karyawan')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Section::make('Informasi Karyawan')
                                ->schema([
                                    Forms\Components\Select::make('employee_id')
                                        ->label('Karyawan')
                                        ->required()
                                        ->options(function () {
                                            // Get client_id from the tax report
                                            $taxReport = $this->getOwnerRecord();
                                            if ($taxReport && $taxReport->client_id) {
                                                return Employee::where('client_id', $taxReport->client_id)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            }
                                            return [];
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            Forms\Components\Hidden::make('client_id')
                                                ->default(function () {
                                                    // Get client_id from the tax report
                                                    $taxReport = $this->getOwnerRecord();
                                                    return $taxReport ? $taxReport->client_id : null;
                                                }),
                                                
                                            Forms\Components\TextInput::make('name')
                                                ->label('Nama Karyawan')
                                                ->required()
                                                ->maxLength(255),
                                                
                                            Forms\Components\TextInput::make('npwp')
                                                ->label('NPWP')
                                                ->maxLength(255)
                                                ->placeholder('00.000.000.0-000.000')
                                                ->helperText('Format: 00.000.000.0-000.000'),
                                                
                                            Forms\Components\TextInput::make('position')
                                                ->label('Jabatan')
                                                ->maxLength(255),
                                                
                                            Forms\Components\TextInput::make('salary')
                                                ->label('Gaji')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->mask(RawJs::make('$money($input)'))
                                                ->stripCharacters(','),
                                                
                                            Forms\Components\Select::make('status')
                                                ->label('Status')
                                                ->options([
                                                    'active' => 'Aktif',
                                                    'inactive' => 'Tidak Aktif',
                                                ])
                                                ->default('active'),
                                                
                                            Forms\Components\Select::make('type')
                                                ->label('Tipe Karyawan')
                                                ->options([
                                                    'Harian' => 'Harian',
                                                    'Karyawan Tetap' => 'Karyawan Tetap',
                                                ])
                                                ->default('Harian'),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            return Employee::create($data)->id;
                                        })
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            if ($state) {
                                                $employee = Employee::find($state);
                                                if ($employee) {
                                                    // Don't set TER amount, keep it at default 5%
                                                    
                                                    // Calculate PPH 21 with the formula: Salary + (Salary * TER%)
                                                    $employeeSalary = $employee->salary ?? 0;
                                                    $terPercentage = 5 / 100; // Default 5%
                                                    $terAmount = $employeeSalary * $terPercentage;
                                                    $pphAmount = $employeeSalary + $terAmount;
                                                    
                                                    $set('pph_21_amount', $pphAmount);
                                                }
                                            }
                                        }),
                                        
                                    Forms\Components\Placeholder::make('employee_info')
                                        ->label('Informasi Karyawan')
                                        ->content(function (Forms\Get $get) {
                                            $employeeId = $get('employee_id');
                                            if (!$employeeId) {
                                                return 'Silahkan pilih karyawan terlebih dahulu';
                                            }
                                            
                                            $employee = Employee::find($employeeId);
                                            if (!$employee) {
                                                return 'Karyawan tidak ditemukan';
                                            }
                                            
                                            return view('components.tax-reports.employee-card', [
                                                'employee' => $employee,
                                            ]);
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ]),
                        
                    // In the Detail Pajak Penghasilan section
                    Wizard\Step::make('Detail Pajak Penghasilan')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Section::make('Perhitungan Pajak')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('ter_amount')
                                        ->label('TER (%)')
                                        ->required()
                                        ->numeric()
                                        ->prefix('%')
                                        ->default(5)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            if (is_numeric($state)) {
                                                // Get employee info for calculation
                                                $employeeId = $get('employee_id');
                                                $employeeSalary = 0;
                                                
                                                if ($employeeId) {
                                                    $employee = Employee::find($employeeId);
                                                    if ($employee) {
                                                        $employeeSalary = $employee->salary ?? 0;
                                                    }
                                                }
                                                
                                                // Calculate PPH 21 using the formula: Salary + (Salary * TER%)
                                                $terPercentage = floatval($state) / 100;
                                                $terAmount = $employeeSalary * $terPercentage;
                                                $pphAmount = $employeeSalary + $terAmount;
                                                
                                                $set('pph_21_amount', $pphAmount);
                                            }
                                        }),
                                        
                                    Forms\Components\TextInput::make('pph_21_amount')
                                        ->label('Jumlah PPh 21')
                                        ->required()
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->stripCharacters(',')
                                        ->helperText('PPh 21 = Gaji + (Gaji × Tarif TER%)'),
                                ]),
                    ]),
                        
                    Wizard\Step::make('Dokumen')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Berkas PPh 21')
                                ->schema([
                                    Forms\Components\FileUpload::make('file_path')
                                        ->label('Bukti Potong PPh 21')
                                        ->required()
                                        ->disk('public')
                                        ->directory('income-tax-documents')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->helperText('Unggah dokumen bukti potong PPh 21 (PDF atau gambar)')
                                        ->columnSpanFull(),
                                    
                                    Forms\Components\RichEditor::make('notes')
                                        ->label('Catatan')
                                        ->placeholder('Tambahkan catatan relevan tentang pajak penghasilan ini')
                                        ->maxLength(1000)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->skippable()
                ->persistStepInQueryString('income-tax-wizard-step')
                ->columnSpanFull(),
            ]);
    }

    private function calculatePph21($amount, $employeeType)
    {
        // Simplified PPh 21 calculation - adjust based on actual tax rules
        if ($employeeType === 'Karyawan Tetap') {
            // Example: 5% tax for permanent employees
            return $amount * 0.05;
        } else {
            // Example: 2.5% tax for daily workers
            return $amount * 0.025;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\ImageColumn::make('user_avatar')
                    ->label('Dibuat Oleh')
                    ->circular()
                    ->state(function ($record) {
                        // If we have a created_by value
                        if ($record->created_by) {
                            $user = \App\Models\User::find($record->created_by);
                            if ($user && method_exists($user, 'getAvatarUrl')) {
                                return $user->getAvatarUrl();
                            }
                        }
                        return null;
                    })
                    ->defaultImageUrl(asset('images/default-avatar.png'))
                    ->size(40)
                    ->tooltip(function ($record): string {
                        if ($record->created_by) {
                            $user = \App\Models\User::find($record->created_by);
                            return $user ? $user->name : 'User #' . $record->created_by;
                        }
                        return 'System';
                    })
                    ->defaultImageUrl(asset('images/default-avatar.png'))
                    ->size(40)
                    ->tooltip(function ($record): string {
                        return $record->creator?->name ?? 'System';
                    }),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('employee.npwp')
                    ->label('NPWP')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('employee.position')
                    ->label('Jabatan')
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('employee.type')
                    ->label('Tipe Karyawan')
                    ->colors([
                        'primary' => 'Karyawan Tetap',
                        'warning' => 'Harian',
                    ]),
                    
                Tables\Columns\TextColumn::make('ter_amount')
                    ->label('TER')
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('pph_21_amount')
                    ->label('PPh 21')
                    ->getStateUsing(function ($record) {
                        return $record->pph_21_amount == 0 ? 'Nihil' : $record->pph_21_amount;
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state === 'Nihil') {
                            return $state;
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    })
                    ->colors([
                        'danger' => 'Nihil',
                        'success' => fn ($state) => $state !== 'Nihil',
                    ])
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee.type')
                    ->label('Tipe Karyawan')
                    ->options([
                        'Karyawan Tetap' => 'Karyawan Tetap',
                        'Harian' => 'Harian',
                    ]),
                    
                Tables\Filters\SelectFilter::make('employee.status')
                    ->label('Status Karyawan')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pajak Penghasilan')
                    ->modalHeading('Tambah Data Pajak Penghasilan')
                    ->modalWidth('7xl')
                    ->successNotificationTitle('Data pajak penghasilan berhasil ditambahkan')
                    ->before(function (array $data) {
                        // Get the tax report
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            // Check if there's already an entry for this employee in this tax report
                            $existingEntry = \App\Models\IncomeTax::where('tax_report_id', $taxReport->id)
                                ->where('employee_id', $data['employee_id'])
                                ->first();
                            
                            if ($existingEntry) {
                                // Use notification
                                \Filament\Notifications\Notification::make()
                                    ->title('Data Sudah Ada')
                                    ->body('Data pajak penghasilan untuk karyawan ini sudah ada.')
                                    ->danger()
                                    ->send();
                                
                                // Throw validation exception to stop the process
                                throw new \Illuminate\Validation\ValidationException(
                                    validator: validator([], []),
                                    response: response()->json([
                                        'message' => 'Data pajak penghasilan untuk karyawan ini sudah ada.',
                                    ], 422)
                                );
                            }
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat')
                        ->modalWidth('7xl'),
                        
                    Tables\Actions\EditAction::make()
                        ->label('Edit')
                        ->modalWidth('7xl'),
                        
                    Tables\Actions\Action::make('download')
                        ->label('Unduh Berkas')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null)
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => $record->file_path)
                        ->tooltip('Unduh berkas bukti potong PPh 21'),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Data Pajak Penghasilan')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data pajak penghasilan ini? Tindakan ini tidak dapat dibatalkan.'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus')
                        ->modalHeading('Hapus Data Pajak Penghasilan Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data pajak penghasilan yang terpilih? Tindakan ini tidak dapat dibatalkan.'),
                        
                    Tables\Actions\BulkAction::make('export')
                        ->label('Ekspor ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn () => null) // Implement export functionality here
                        ->requiresConfirmation()
                        ->modalHeading('Ekspor Data Pajak Penghasilan')
                        ->modalDescription('Apakah Anda yakin ingin mengekspor data pajak penghasilan yang terpilih?')
                        ->modalSubmitActionLabel('Ya, Ekspor'),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Data PPh 21')
            ->emptyStateDescription('Tambahkan data pajak penghasilan (PPh 21) karyawan untuk laporan pajak ini. Data PPh 21 membantu mencatat kewajiban pajak penghasilan.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Data PPh 21')
                    ->modalWidth('7xl')
                    ->icon('heroicon-o-plus'),
                    
                // Tables\Actions\Action::make('register_employee')
                //     ->label('Daftarkan Karyawan Baru')
                //     ->url(route('filament.admin.resources.employees.create'))
                //     ->icon('heroicon-o-user-plus')
                //     ->color('gray')
                //     ->openUrlInNewTab(),
            ]);
    }
}