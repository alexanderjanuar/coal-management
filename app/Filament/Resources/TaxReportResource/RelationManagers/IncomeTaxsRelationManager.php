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
use Swis\Filament\Activitylog\Tables\Actions\ActivitylogAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

class IncomeTaxsRelationManager extends RelationManager
{
    protected static string $relationship = 'incomeTaxs';

    protected static ?string $title = 'PPh';

    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * Generate dynamic directory path for file uploads
     */
    private function generateDirectoryPath($get): string
    {
        // Get tax report to access client information
        $taxReportId = $get('tax_report_id') ?? $this->getOwnerRecord()->id;
        $taxReport = \App\Models\TaxReport::with('client')->find($taxReportId);
        
        // Default values
        $clientName = 'unknown-client';
        $monthName = 'unknown-month';
        
        if ($taxReport && $taxReport->client) {
            // Clean client name for folder structure
            $clientName = Str::slug($taxReport->client->name);
            
            // Convert month from tax report to Indonesian month name
            $monthName = $this->convertToIndonesianMonth($taxReport->month);
        }
        
        return "clients/{$clientName}/SPT/{$monthName}/PPH";
    }

    /**
     * Generate filename for PPH documents
     */
    private function generateFileName($get, $originalFileName, $prefix = 'PPH-21'): string
    {
        $employeeId = $get('employee_id');
        $employeeName = 'Unknown Employee';
        
        if ($employeeId) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                $employeeName = Str::slug($employee->name);
            }
        }
        
        // Get file extension
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        
        return "{$prefix}-{$employeeName}.{$extension}";
    }

    /**
     * Convert month format to Indonesian month names
     */
    private function convertToIndonesianMonth($month): string
    {
        // Handle different month formats
        $monthNames = [
            '01' => 'Januari', '1' => 'Januari', 'january' => 'Januari', 'jan' => 'Januari',
            '02' => 'Februari', '2' => 'Februari', 'february' => 'Februari', 'feb' => 'Februari',
            '03' => 'Maret', '3' => 'Maret', 'march' => 'Maret', 'mar' => 'Maret',
            '04' => 'April', '4' => 'April', 'april' => 'April', 'apr' => 'April',
            '05' => 'Mei', '5' => 'Mei', 'may' => 'Mei',
            '06' => 'Juni', '6' => 'Juni', 'june' => 'Juni', 'jun' => 'Juni',
            '07' => 'Juli', '7' => 'Juli', 'july' => 'Juli', 'jul' => 'Juli',
            '08' => 'Agustus', '8' => 'Agustus', 'august' => 'Agustus', 'aug' => 'Agustus',
            '09' => 'September', '9' => 'September', 'september' => 'September', 'sep' => 'September',
            '10' => 'Oktober', 'october' => 'Oktober', 'oct' => 'Oktober',
            '11' => 'November', 'november' => 'November', 'nov' => 'November',
            '12' => 'Desember', 'december' => 'Desember', 'dec' => 'Desember',
        ];

        $cleanMonth = strtolower(trim($month));
        
        // If it's a date format like "2025-01", extract the month part
        if (preg_match('/\d{4}-(\d{1,2})/', $month, $matches)) {
            $cleanMonth = $matches[1];
        }
        
        return $monthNames[$cleanMonth] ?? Str::title($cleanMonth);
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
                                                
                                            Forms\Components\Select::make('position')
                                                ->label('Jabatan')
                                                ->options([
                                                    'Direktur Utama' => 'Direktur Utama',
                                                    'Direktur' => 'Direktur',
                                                    'Komisaris Utama' => 'Komisaris Utama',
                                                    'Komisaris' => 'Komisaris',
                                                    'Staff' => 'Staff',
                                                ])
                                                ->searchable()
                                                ->required(),                                               
                                            Forms\Components\TextInput::make('salary')
                                                ->label('Gaji')
                                                ->numeric()
                                                ->prefix('Rp')
                                                ->mask(RawJs::make('$money($input)'))
                                                ->stripCharacters(','),
                                                
                                            Forms\Components\Select::make('status')
                                                ->label('Status')
                                                ->native(false)
                                                ->options([
                                                    'active' => 'Aktif',
                                                    'inactive' => 'Tidak Aktif',
                                                ])
                                                ->default('active'),
                                                
                                            Forms\Components\Select::make('type')
                                                ->label('Tipe Karyawan')
                                                ->native(false)
                                                ->options([
                                                    'Harian' => 'Harian',
                                                    'Karyawan Tetap' => 'Karyawan Tetap',
                                                ])
                                                ->default('Harian'),

                                            // NEW: Add TK/K fields to employee creation form
                                            Section::make('Status Pajak (TK/K)')
                                                ->description('Tentukan status perpajakan karyawan untuk perhitungan TER yang akurat')
                                                ->schema([
                                                    Forms\Components\Select::make('marital_status')
                                                        ->label('Status Pernikahan')
                                                        ->options([
                                                            'single' => 'Belum Menikah (TK)',
                                                            'married' => 'Menikah (K)',
                                                        ])
                                                        ->default('single')
                                                        ->required()
                                                        ->live()
                                                        ->native(false),

                                                    Forms\Components\Select::make('tk')
                                                        ->label('Jumlah Tanggungan (TK)')
                                                        ->options([
                                                            0 => 'TK/0 - Tidak ada tanggungan',
                                                            1 => 'TK/1 - 1 tanggungan',
                                                            2 => 'TK/2 - 2 tanggungan',
                                                            3 => 'TK/3 - 3 tanggungan atau lebih',
                                                        ])
                                                        ->default(0)
                                                        ->visible(fn (Forms\Get $get) => $get('marital_status') === 'single')
                                                        ->required(fn (Forms\Get $get) => $get('marital_status') === 'single'),

                                                    Forms\Components\Select::make('k')
                                                        ->label('Jumlah Tanggungan (K)')
                                                        ->options([
                                                            0 => 'K/0 - Tidak ada tanggungan',
                                                            1 => 'K/1 - 1 tanggungan',
                                                            2 => 'K/2 - 2 tanggungan',
                                                            3 => 'K/3 - 3 tanggungan atau lebih',
                                                        ])
                                                        ->default(0)
                                                        ->visible(fn (Forms\Get $get) => $get('marital_status') === 'married')
                                                        ->required(fn (Forms\Get $get) => $get('marital_status') === 'married'),
                                                ])
                                                ->columns(1),
                                        ])
                                        ->createOptionUsing(function (array $data) {
                                            return Employee::create($data)->id;
                                        })
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            if ($state) {
                                                $employee = Employee::find($state);
                                                if ($employee) {
                                                    // Auto-calculate TER category and percentage based on employee's TK/K status
                                                    $terCategory = $this->determineTerCategory($employee);
                                                    $terPercentage = $this->calculateTerPercentage($employee->salary ?? 0, $terCategory);
                                                    
                                                    // Set the TER category and amount
                                                    $set('ter_category', $terCategory);
                                                    $set('ter_amount', $terPercentage);
                                                    
                                                    // Calculate PPH 21 with the formula: Salary + (Salary * TER%)
                                                    $employeeSalary = $employee->salary ?? 0;
                                                    $terAmount = $employeeSalary * ($terPercentage / 100);
                                                    $pphAmount = $employeeSalary + $terAmount;
                                                    
                                                    // Format PPH amount with Indonesian money format
                                                    $set('pph_21_amount', number_format($pphAmount, 2, '.', ','));
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
                                            
                                            // NEW: Display TK/K status in employee info
                                            $taxStatus = $this->getEmployeeTaxStatus($employee);
                                            
                                            return view('components.tax-reports.employee-card', [
                                                'employee' => $employee,
                                                'taxStatus' => $taxStatus,
                                            ]);
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ]),
                        
                    // UPDATED: Improved Detail Pajak Penghasilan section
                    Wizard\Step::make('Detail Pajak Penghasilan')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            Section::make('Perhitungan Pajak')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('ter_category')
                                        ->label('Kategori TER')
                                        ->options([
                                            'A' => 'Kategori A (TK/0, TK/1, K/0)',
                                            'B' => 'Kategori B (TK/2, TK/3, K/1, K/2)',
                                            'C' => 'Kategori C (K/3)',
                                            'manual' => 'Input Manual',
                                        ])
                                        ->native(false)
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            // Get employee info for calculation
                                            $employeeId = $get('employee_id');
                                            $employeeSalary = 0;
                                            
                                            if ($employeeId) {
                                                $employee = Employee::find($employeeId);
                                                if ($employee) {
                                                    // Clean any potential formatting in the salary
                                                    $employeeSalary = is_numeric($employee->salary) 
                                                        ? $employee->salary 
                                                        : preg_replace('/[^0-9.]/', '', $employee->salary ?? '0');
                                                }
                                            }
                                            
                                            if ($state !== 'manual' && $employeeSalary > 0) {
                                                // Calculate TER percentage based on salary and category
                                                $terPercentage = $this->calculateTerPercentage($employeeSalary, $state);
                                                $set('ter_amount', $terPercentage);
                                                
                                                // Calculate PPH 21 using the formula
                                                $terAmount = $employeeSalary * ($terPercentage / 100);
                                                $pphAmount = $employeeSalary + $terAmount;
                                                
                                                // Format PPH amount with Indonesian money format for display
                                                $set('pph_21_amount', number_format($pphAmount, 2, '.', ','));
                                            }
                                        }),

                                    Forms\Components\TextInput::make('ter_amount')
                                        ->label('TER (%)')
                                        ->required()
                                        ->prefix('%')
                                        ->default(5)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->disabled(fn (Forms\Get $get) => $get('ter_category') !== 'manual')
                                        ->live(onBlur: true)
                                        ->dehydrated()
                                        ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                            if ($get('ter_category') === 'manual') {
                                                // Get employee info for calculation
                                                $employeeId = $get('employee_id');
                                                $employeeSalary = 0;
                                                
                                                if ($employeeId) {
                                                    $employee = Employee::find($employeeId);
                                                    if ($employee) {
                                                        // Clean any potential formatting in the salary
                                                        $employeeSalary = is_numeric($employee->salary) 
                                                            ? $employee->salary 
                                                            : preg_replace('/[^0-9.]/', '', $employee->salary ?? '0');
                                                    }
                                                }
                                                
                                                // Clean the TER percentage input to ensure it's numeric
                                                $cleanedTerPercentage = preg_replace('/[^0-9.]/', '', $state);
                                                
                                                // Calculate PPH 21 using the formula: Salary + (Salary * TER%)
                                                $terPercentage = floatval($cleanedTerPercentage) / 100;
                                                $terAmount = $employeeSalary * $terPercentage;
                                                $pphAmount = $employeeSalary + $terAmount;
                                                
                                                // Format PPH amount with Indonesian money format for display
                                                $set('pph_21_amount', number_format($pphAmount, 2, '.', ','));
                                            }
                                        }),

                                    // NEW: Display employee's actual TK/K status
                                    Forms\Components\Placeholder::make('employee_tax_status')
                                        ->label('Status Pajak Karyawan')
                                        ->content(function (Forms\Get $get) {
                                            $employeeId = $get('employee_id');
                                            if (!$employeeId) {
                                                return 'Pilih karyawan terlebih dahulu';
                                            }
                                            
                                            $employee = Employee::find($employeeId);
                                            if (!$employee) {
                                                return 'Karyawan tidak ditemukan';
                                            }
                                            
                                            $taxStatus = $this->getEmployeeTaxStatus($employee);
                                            $recommendedCategory = $this->determineTerCategory($employee);
                                            
                                            return "Status: {$taxStatus} | Kategori TER yang disarankan: {$recommendedCategory}";
                                        })
                                        ->columnSpanFull(),

                                    Forms\Components\Placeholder::make('ter_explanation')
                                        ->label('Penjelasan TER')
                                        ->content(function (Forms\Get $get) {
                                            $category = $get('ter_category');
                                            $terAmount = $get('ter_amount');
                                            
                                            if ($category === 'manual') {
                                                return 'Anda menggunakan input manual untuk TER. Pastikan nilai sesuai dengan ketentuan pajak yang berlaku.';
                                            }
                                            
                                            return "TER kategori {$category} sebesar {$terAmount}% dihitung berdasarkan penghasilan bruto bulanan karyawan sesuai PP No. 58 Tahun 2003.";
                                        })
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('pph_21_amount')
                                        ->label('Jumlah PPh 21')
                                        ->required()
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->dehydrateStateUsing(fn ($state) => preg_replace('/[^0-9.]/', '', $state))
                                        ->rules(['required'])
                                        ->helperText('PPh 21 = Gaji + (Gaji × Tarif TER%)')
                                        ->columnSpanFull(),
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
                                        ->openable()
                                        ->downloadable()
                                        ->directory(function (Forms\Get $get) {
                                            return $this->generateDirectoryPath($get);
                                        })
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                            return $this->generateFileName($get, $file->getClientOriginalName(), 'PPH-21');
                                        })
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->helperText(function (Forms\Get $get) {
                                            $path = $this->generateDirectoryPath($get);
                                            return "Akan disimpan di: storage/{$path}/PPH-21-[Nama Karyawan].[ext]";
                                        })
                                        ->columnSpanFull(),

                                    Forms\Components\FileUpload::make('bukti_setor')
                                        ->label('Bukti Setor (Opsional)')
                                        ->openable()
                                        ->downloadable()
                                        ->disk('public')
                                        ->directory(function (Forms\Get $get) {
                                            $basePath = $this->generateDirectoryPath($get);
                                            return $basePath . '/Bukti-Setor';
                                        })
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                            return $this->generateFileName($get, $file->getClientOriginalName(), 'Bukti-Setor-PPH-21');
                                        })
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->helperText(function (Forms\Get $get) {
                                            $path = $this->generateDirectoryPath($get);
                                            return "Akan disimpan di: storage/{$path}/Bukti-Setor/";
                                        })
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

    // NEW: Helper method to determine TER category based on employee's TK/K status
    private function determineTerCategory(Employee $employee): string
    {
        if ($employee->marital_status === 'single') {
            // TK status
            if (in_array($employee->tk, [0, 1])) {
                return 'A'; // TK/0, TK/1
            } else {
                return 'B'; // TK/2, TK/3
            }
        } else {
            // K status (married)
            if ($employee->k == 0) {
                return 'A'; // K/0
            } elseif (in_array($employee->k, [1, 2])) {
                return 'B'; // K/1, K/2
            } else {
                return 'C'; // K/3
            }
        }
    }

    // NEW: Helper method to get formatted tax status string
    private function getEmployeeTaxStatus(Employee $employee): string
    {
        if ($employee->marital_status === 'single') {
            return "TK/{$employee->tk}";
        } else {
            return "K/{$employee->k}";
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
                    }),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

                // Add TK/K status column
                Tables\Columns\BadgeColumn::make('employee_tax_status')
                    ->label('Status Pajak')
                    ->getStateUsing(function ($record) {
                        $employee = $record->employee;
                        if (!$employee) return 'N/A';
                        
                        if ($employee->marital_status === 'single') {
                            return "TK/{$employee->tk}";
                        } else {
                            return "K/{$employee->k}";
                        }
                    })
                    ->colors([
                        'primary' => fn ($state) => str_starts_with($state, 'TK/'),
                        'success' => fn ($state) => str_starts_with($state, 'K/'),
                        'gray' => 'N/A',
                    ])
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('employee.npwp')
                    ->label('NPWP')
                    ->searchable(),
                    
                Tables\Columns\BadgeColumn::make('employee.position')
                    ->label('Jabatan')
                    ->searchable()
                    ->colors([
                        'primary' => 'Direktur Utama',
                        'danger' => 'Direktur',
                        'warning' => 'Komisaris Utama',
                        'secondary' => 'Komisaris',
                        'success' => 'Staff',
                        'gray' => fn ($state) => !in_array($state, [
                            'Direktur Utama', 'Direktur', 'Komisaris Utama', 'Komisaris', 'Staff'
                        ]),
                    ])
                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ada'),
                    
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
                    ->label('Nilai PPh')
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

                Tables\Columns\IconColumn::make('has_bukti_setor')
                    ->label('Bukti Setor')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return !empty($record->bukti_setor);
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(function ($record) {
                        if (!empty($record->bukti_setor)) {
                            return "Bukti setor tersedia";
                        }
                        
                        return "Bukti setor belum diupload";
                    }),
                    
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

                // Add filter for TK/K status
                Tables\Filters\SelectFilter::make('employee.marital_status')
                    ->label('Status Pernikahan')
                    ->options([
                        'single' => 'TK (Belum Menikah)',
                        'married' => 'K (Menikah)',
                    ]),

                // Filter for bukti setor status
                Tables\Filters\TernaryFilter::make('has_bukti_setor')
                    ->label('Status Bukti Setor')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Upload')
                    ->falseLabel('Belum Upload')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('bukti_setor')->where('bukti_setor', '!=', ''),
                        false: fn (Builder $query) => $query->whereNull('bukti_setor')->orWhere('bukti_setor', ''),
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Pajak Penghasilan')
                    ->modalHeading('Tambah Data Pajak Penghasilan')
                    ->modalWidth('7xl')
                    ->successNotificationTitle('Data pajak penghasilan berhasil ditambahkan')
                    ->disabled(function () {
                        // Get the tax report first
                        $taxReport = $this->getOwnerRecord();
                        
                        // If we have a tax report, check the client's contract status
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            // Disable the button if the client doesn't have an active pph_contract
                            return !($client && $client->pph_contract);
                        }
                        
                        return true; // Disable if no tax report is found
                    })
                    ->tooltip(function () {
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            if (!$client || !$client->pph_contract) {
                                return 'Klien tidak memiliki kontrak PPh aktif. Aktifkan kontrak PPh terlebih dahulu.';
                            }
                        }
                        
                        return 'Tambah Data PPh 21';
                    })
                    ->before(function (array $data) {
                        // Get the tax report
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            // Double-check the client's contract status as a safeguard
                            $client = \App\Models\Client::find($taxReport->client_id);
                            if (!$client || !$client->pph_contract) {
                                // Use notification
                                \Filament\Notifications\Notification::make()
                                    ->title('Kontrak PPh Tidak Aktif')
                                    ->body('Klien tidak memiliki kontrak PPh aktif. Aktifkan kontrak PPh terlebih dahulu.')
                                    ->danger()
                                    ->send();
                                
                                // Throw validation exception to stop the process
                                throw new \Illuminate\Validation\ValidationException(
                                    validator: validator([], []),
                                    response: response()->json([
                                        'message' => 'Klien tidak memiliki kontrak PPh aktif.',
                                    ], 422)
                                );
                            }
                            
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
                    
                    Tables\Actions\Action::make('upload_bukti_setor')
                        ->label('Upload Bukti Setor')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('info')
                        ->visible(fn ($record) => empty($record->bukti_setor))
                        ->form(function ($record) {
                            return [
                                Section::make('Upload Bukti Setor PPh 21')
                                    ->description('Upload dokumen bukti setor untuk PPh 21 ini')
                                    ->schema([
                                        Forms\Components\FileUpload::make('bukti_setor')
                                            ->label('Bukti Setor')
                                            ->required()
                                            ->openable()
                                            ->downloadable()
                                            ->disk('public')
                                            ->directory(function () use ($record) {
                                                // Generate path for existing record

                                                $taxReport = $record->taxReport;
                                                $clientName = Str::slug($taxReport->client->name);
                                                $monthName = $this->convertToIndonesianMonth($taxReport->month);
                                                return "clients/{$clientName}/SPT/{$monthName}/PPH/Bukti-Setor";
                                            })
                                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($record): string {
                                                $employeeName = Str::slug($record->employee->name);
                                                $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                                                return "Bukti-Setor-PPH-21-{$employeeName}.{$extension}";
                                            })
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                            ->helperText('Unggah dokumen bukti setor PPh 21 (PDF atau gambar)')
                                            ->columnSpanFull(),
                                    ])
                            ];
                        })
                        ->action(function ($record, array $data) {
                            $record->update([
                                'bukti_setor' => $data['bukti_setor']
                            ]);
                            
                            Notification::make()
                                ->title('Bukti Setor Berhasil Diupload')
                                ->body('Bukti setor untuk PPh 21 ' . $record->employee->name . ' berhasil diupload.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('view_bukti_setor')
                        ->label('Lihat Bukti Setor')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->visible(fn ($record) => !empty($record->bukti_setor))
                        ->url(fn ($record) => asset('storage/' . $record->bukti_setor))
                        ->openUrlInNewTab()
                        ->tooltip('Lihat bukti setor PPh 21'),

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

                    // Enhanced Bulk Upload Bukti Setor Action
                Tables\Actions\BulkAction::make('bulk_upload_bukti_setor')
                        ->label('Upload Bukti Setor')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->color('info')
                        ->requiresConfirmation(false)
                        ->modalHeading(function ($records) {
                            $count = $records->filter(fn ($record) => empty($record->bukti_setor))->count();
                            return "Upload Bukti Setor ({$count} karyawan)";
                        })
                        ->modalDescription('Upload dokumen bukti setor untuk beberapa karyawan sekaligus. Hanya karyawan yang belum memiliki bukti setor yang akan ditampilkan.')
                        ->modalWidth('5xl')
                        
                        ->action(function ($records, array $data) {
                            $successCount = 0;
                            $errors = [];
                            $processedEmployees = [];
                            
                            // Process each record
                            foreach ($data['records'] as $recordId => $recordData) {
                                try {
                                    $record = $records->find($recordId);
                                    
                                    if ($record && !empty($recordData['bukti_setor'])) {
                                        // Update the record
                                        $updateData = [
                                            'bukti_setor' => $recordData['bukti_setor']
                                        ];
                                        
                                        // Add notes to the main notes field if provided
                                        if (!empty($recordData['notes'])) {
                                            $existingNotes = $record->notes ?? '';
                                            $newNote = "\n[Bukti Setor] " . $recordData['notes'];
                                            $updateData['notes'] = $existingNotes . $newNote;
                                        }
                                        
                                        $record->update($updateData);
                                        
                                        $successCount++;
                                        $processedEmployees[] = $record->employee->name;
                                        
                                        // Log activity if you have activity logging
                                        if (class_exists('\Spatie\Activitylog\Models\Activity')) {
                                            activity()
                                                ->performedOn($record)
                                                ->causedBy(auth()->user())
                                                ->withProperties([
                                                    'action' => 'bulk_upload_bukti_setor',
                                                    'employee_name' => $record->employee->name,
                                                    'file_uploaded' => !empty($recordData['bukti_setor']),
                                                ])
                                                ->log('Bukti setor uploaded via bulk action');
                                        }
                                    }
                                } catch (\Exception $e) {
                                    $employeeName = $records->find($recordId)->employee->name ?? "ID: {$recordId}";
                                    $errors[] = "Error untuk {$employeeName}: " . $e->getMessage();
                                }
                            }
                            
                            // Show comprehensive notification
                            if ($successCount > 0) {
                                $employeeList = count($processedEmployees) > 3 
                                    ? implode(', ', array_slice($processedEmployees, 0, 3)) . " dan " . (count($processedEmployees) - 3) . " lainnya"
                                    : implode(', ', $processedEmployees);
                                    
                                Notification::make()
                                    ->title('Bukti Setor Berhasil Diupload')
                                    ->body("Berhasil mengupload bukti setor untuk {$successCount} karyawan: {$employeeList}")
                                    ->success()
                                    ->duration(5000)
                                    ->send();
                            }
                            
                            // Show error notification if any
                            if (!empty($errors)) {
                                Notification::make()
                                    ->title('Beberapa Upload Gagal')
                                    ->body(implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '<br>... dan ' . (count($errors) - 5) . ' error lainnya' : ''))
                                    ->warning()
                                    ->duration(8000)
                                    ->send();
                            }
                            
                            // If no files were uploaded
                            if ($successCount === 0 && empty($errors)) {
                                Notification::make()
                                    ->title('Tidak Ada File yang Diupload')
                                    ->body('Silakan pilih file bukti setor untuk setiap karyawan.')
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->tooltip('Upload bukti setor untuk beberapa karyawan sekaligus')
                        ->modalSubmitActionLabel('Upload Semua Bukti Setor')
                        ->modalCancelActionLabel('Batal'),
                        
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
                    ->icon('heroicon-o-plus')
                    ->disabled(function () {
                        // Get the tax report
                        $taxReport = $this->getOwnerRecord();
                        
                        // If we have a tax report, check the client's contract status
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            // Disable the button if the client doesn't have an active pph_contract
                            return !($client && $client->pph_contract);
                        }
                        
                        return true; // Disable if no tax report is found
                    })
                    ->tooltip(function () {
                        $taxReport = $this->getOwnerRecord();
                        
                        if ($taxReport) {
                            $client = \App\Models\Client::find($taxReport->client_id);
                            
                            if (!$client || !$client->pph_contract) {
                                return 'Klien tidak memiliki kontrak PPh aktif. Aktifkan kontrak PPh terlebih dahulu.';
                            }
                        }
                        
                        return 'Tambah Data PPh 21';
                    }),
            ]);
    }

    private function calculateTerPercentage($salary, $category)
    {
        // Convert salary to numeric value if it's not already
        $salary = is_numeric($salary) ? $salary : 0;
        
        // TER Category A (TK/0, TK/1, K/0)
        if ($category === 'A') {
            if ($salary <= 5400000) return 0;
            if ($salary <= 5650000) return 0.25;
            if ($salary <= 5950000) return 0.5;
            if ($salary <= 6300000) return 0.75;
            if ($salary <= 6750000) return 1;
            if ($salary <= 7500000) return 1.25;
            if ($salary <= 8550000) return 1.5;
            if ($salary <= 9650000) return 1.75;
            if ($salary <= 10050000) return 2;
            if ($salary <= 10350000) return 2.25;
            if ($salary <= 10700000) return 2.5;
            if ($salary <= 11050000) return 3;
            if ($salary <= 11600000) return 3.5;
            if ($salary <= 12500000) return 4;
            if ($salary <= 13750000) return 5;
            if ($salary <= 15100000) return 6;
            if ($salary <= 16950000) return 7;
            if ($salary <= 19750000) return 8;
            if ($salary <= 24150000) return 9;
            if ($salary <= 26450000) return 10;
            if ($salary <= 28000000) return 11;
            if ($salary <= 30050000) return 12;
            if ($salary <= 32400000) return 13;
            if ($salary <= 35400000) return 14;
            if ($salary <= 39100000) return 15;
            if ($salary <= 43850000) return 16;
            if ($salary <= 47800000) return 17;
            if ($salary <= 51400000) return 18;
            if ($salary <= 56300000) return 19;
            if ($salary <= 62200000) return 20;
            if ($salary <= 68600000) return 21;
            if ($salary <= 77500000) return 22;
            if ($salary <= 89000000) return 23;
            if ($salary <= 103000000) return 24;
            if ($salary <= 125000000) return 25;
            if ($salary <= 157000000) return 26;
            if ($salary <= 206000000) return 27;
            if ($salary <= 337000000) return 28;
            if ($salary <= 454000000) return 29;
            if ($salary <= 550000000) return 30;
            if ($salary <= 695000000) return 31;
            if ($salary <= 910000000) return 32;
            if ($salary <= 1400000000) return 33;
            return 34;
        }
        
        // TER Category B (TK/2, TK/3, K/1, K/2)
        if ($category === 'B') {
            if ($salary <= 6200000) return 0;
            if ($salary <= 6500000) return 0.25;
            if ($salary <= 6850000) return 0.5;
            if ($salary <= 7300000) return 0.75;
            if ($salary <= 9200000) return 1;
            if ($salary <= 10750000) return 1.5;
            if ($salary <= 11250000) return 2;
            if ($salary <= 11600000) return 2.5;
            if ($salary <= 12600000) return 3;
            if ($salary <= 13600000) return 4;
            if ($salary <= 14950000) return 5;
            if ($salary <= 16400000) return 6;
            if ($salary <= 18450000) return 7;
            if ($salary <= 21850000) return 8;
            if ($salary <= 26000000) return 9;
            if ($salary <= 27700000) return 10;
            if ($salary <= 29350000) return 11;
            if ($salary <= 31450000) return 12;
            if ($salary <= 33950000) return 13;
            if ($salary <= 37100000) return 14;
            if ($salary <= 41100000) return 15;
            if ($salary <= 45800000) return 16;
            if ($salary <= 49500000) return 17;
            if ($salary <= 53800000) return 18;
            if ($salary <= 58500000) return 19;
            if ($salary <= 64000000) return 20;
            if ($salary <= 71000000) return 21;
            if ($salary <= 80000000) return 22;
            if ($salary <= 93000000) return 23;
            if ($salary <= 109000000) return 24;
            if ($salary <= 129000000) return 25;
            if ($salary <= 163000000) return 26;
            if ($salary <= 211000000) return 27;
            if ($salary <= 374000000) return 28;
            if ($salary <= 459000000) return 29;
            if ($salary <= 555000000) return 30;
            if ($salary <= 704000000) return 31;
            if ($salary <= 957000000) return 32;
            if ($salary <= 1405000000) return 33;
            return 34;
        }
        
        // TER Category C (K/3)
        if ($category === 'C') {
            if ($salary <= 6600000) return 0;
            if ($salary <= 6950000) return 0.25;
            if ($salary <= 7350000) return 0.5;
            if ($salary <= 7800000) return 0.75;
            if ($salary <= 8850000) return 1;
            if ($salary <= 9800000) return 1.25;
            if ($salary <= 10950000) return 2;
            if ($salary <= 11200000) return 1.75;
            if ($salary <= 12050000) return 2;
            if ($salary <= 12950000) return 3;
            if ($salary <= 14150000) return 4;
            if ($salary <= 15550000) return 5;
            if ($salary <= 17050000) return 6;
            if ($salary <= 19500000) return 7;
            if ($salary <= 22700000) return 8;
            if ($salary <= 26600000) return 9;
            if ($salary <= 28100000) return 10;
            if ($salary <= 30100000) return 11;
            if ($salary <= 32600000) return 12;
            if ($salary <= 35400000) return 13;
            if ($salary <= 38900000) return 14;
            if ($salary <= 43000000) return 15;
            if ($salary <= 47400000) return 16;
            if ($salary <= 51200000) return 17;
            if ($salary <= 55800000) return 18;
            if ($salary <= 60400000) return 19;
            if ($salary <= 66700000) return 20;
            if ($salary <= 74500000) return 21;
            if ($salary <= 83200000) return 22;
            if ($salary <= 95600000) return 23;
            if ($salary <= 110000000) return 24;
            if ($salary <= 134000000) return 25;
            if ($salary <= 169000000) return 26;
            if ($salary <= 221000000) return 27;
            if ($salary <= 390000000) return 28;
            if ($salary <= 463000000) return 29;
            if ($salary <= 561000000) return 30;
            if ($salary <= 709000000) return 31;
            if ($salary <= 965000000) return 32;
            if ($salary <= 1419000000) return 33;
            return 34;
        }
        
        // Default fallback to 5% if no category matches or for manual input
        return 5;
    }
}