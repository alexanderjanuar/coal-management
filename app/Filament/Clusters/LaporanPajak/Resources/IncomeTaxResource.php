<?php

namespace App\Filament\Clusters\LaporanPajak\Resources;

use App\Filament\Clusters\LaporanPajak;
use App\Filament\Clusters\LaporanPajak\Resources\IncomeTaxResource\Pages;
use App\Filament\Clusters\LaporanPajak\Resources\IncomeTaxResource\RelationManagers;
use App\Models\IncomeTax;
use App\Models\Employee;
use App\Models\TaxReport;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Support\RawJs;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class IncomeTaxResource extends Resource
{
    protected static ?string $model = IncomeTax::class;
    
    protected static ?string $cluster = LaporanPajak::class;
    
    protected static ?string $navigationLabel = 'PPH';
    
    protected static ?string $navigationGroup = 'Jenis Pajak';

    
    protected static ?int $navigationSort = 2;
    
    /**
     * Generate dynamic directory path for file uploads
     */
    private static function generateDirectoryPath($get): string
    {
        $taxReportId = $get('tax_report_id');
        $taxReport = null;
        
        if ($taxReportId) {
            $taxReport = TaxReport::with('client')->find($taxReportId);
        }
        
        // Default values
        $clientName = 'unknown-client';
        $monthName = 'unknown-month';
        
        if ($taxReport && $taxReport->client) {
            // Clean client name for folder structure
            $clientName = Str::slug($taxReport->client->name);
            
            // Convert month from tax report to Indonesian month name
            $monthName = self::convertToIndonesianMonth($taxReport->month);
        }
        
        return "clients/{$clientName}/SPT/{$monthName}/PPH";
    }
    
    /**
     * Generate filename for PPH documents
     */
    private static function generateFileName($get, $originalFileName, $prefix = 'PPH-21'): string
    {
        $employeeId = $get('employee_id');
        $employeeName = 'Unknown-Employee';
        
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
    private static function convertToIndonesianMonth($month): string
    {
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
    
    /**
     * Determine TER category based on employee's TK/K status
     */
    private static function determineTerCategory(Employee $employee): string
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
    
    /**
     * Get formatted tax status string
     */
    private static function getEmployeeTaxStatus(Employee $employee): string
    {
        if ($employee->marital_status === 'single') {
            return "TK/{$employee->tk}";
        } else {
            return "K/{$employee->k}";
        }
    }
    
    /**
     * Calculate TER percentage based on salary and category
     */
    private static function calculateTerPercentage($salary, $category)
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
                    
                Wizard::make([
                    Wizard\Step::make('Laporan Pajak & Karyawan')
                        ->icon('heroicon-o-document-text')
                        ->description('Pilih laporan pajak dan karyawan untuk data PPh 21')
                        ->schema([
                            Section::make('Pilih Laporan Pajak dan Karyawan')
                                ->description('Tentukan laporan pajak dan karyawan yang akan dibuatkan data PPh 21')
                                ->schema([
                                    Forms\Components\Select::make('tax_report_id')
                                        ->label('Laporan Pajak')
                                        ->required()
                                        ->options(function () {
                                            return TaxReport::with('client')
                                                ->get()
                                                ->mapWithKeys(function ($taxReport) {
                                                    $clientName = $taxReport->client->name ?? 'Unknown Client';
                                                    $monthName = self::convertToIndonesianMonth($taxReport->month);
                                                    return [$taxReport->id => "{$clientName} - {$monthName}"];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                            // Reset employee selection when tax report changes
                                            $set('employee_id', null);
                                            $set('ter_category', null);
                                            $set('ter_amount', 5);
                                            $set('pph_21_amount', 0);
                                        })
                                        ->helperText('Pilih laporan pajak untuk periode tertentu'),
                                        
                                    Forms\Components\Select::make('employee_id')
                                        ->label('Karyawan')
                                        ->required()
                                        ->options(function (Forms\Get $get) {
                                            $taxReportId = $get('tax_report_id');
                                            if ($taxReportId) {
                                                $taxReport = TaxReport::find($taxReportId);
                                                if ($taxReport && $taxReport->client_id) {
                                                    return Employee::where('client_id', $taxReport->client_id)
                                                        ->where('status', 'active')
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }
                                            }
                                            return [];
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->disabled(fn (Forms\Get $get) => !$get('tax_report_id'))
                                        ->helperText('Pilih karyawan dari klien terpilih')
                                        ->createOptionForm([
                                            Forms\Components\Hidden::make('client_id')
                                                ->default(function (Forms\Get $get) {
                                                    $taxReportId = $get('tax_report_id');
                                                    if ($taxReportId) {
                                                        $taxReport = TaxReport::find($taxReportId);
                                                        return $taxReport ? $taxReport->client_id : null;
                                                    }
                                                    return null;
                                                }),
                                                
                                            Section::make('Informasi Dasar Karyawan')
                                                ->description('Data utama karyawan')
                                                ->schema([
                                                    Forms\Components\TextInput::make('name')
                                                        ->label('Nama Karyawan')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->placeholder('Masukkan nama lengkap karyawan'),
                                                        
                                                    Forms\Components\TextInput::make('npwp')
                                                        ->label('NPWP')
                                                        ->maxLength(255)
                                                        ->placeholder('00.000.000.0-000.000')
                                                        ->helperText('Format: 00.000.000.0-000.000 (opsional)'),
                                                        
                                                    Forms\Components\Select::make('position')
                                                        ->label('Jabatan')
                                                        ->options([
                                                            'Direktur Utama' => 'Direktur Utama',
                                                            'Direktur' => 'Direktur',
                                                            'Komisaris Utama' => 'Komisaris Utama',
                                                            'Komisaris' => 'Komisaris',
                                                            'Staff' => 'Staff',
                                                            'Manager' => 'Manager',
                                                            'Supervisor' => 'Supervisor',
                                                            'Admin' => 'Admin',
                                                        ])
                                                        ->searchable()
                                                        ->required()
                                                        ->placeholder('Pilih jabatan karyawan'),
                                                        
                                                    Forms\Components\TextInput::make('salary')
                                                        ->label('Gaji Bulanan')
                                                        ->numeric()
                                                        ->prefix('Rp')
                                                        ->mask(RawJs::make('$money($input)'))
                                                        ->stripCharacters(',')
                                                        ->placeholder('0')
                                                        ->helperText('Masukkan gaji bulanan untuk perhitungan TER'),
                                                        
                                                    Forms\Components\Select::make('status')
                                                        ->label('Status Karyawan')
                                                        ->native(false)
                                                        ->options([
                                                            'active' => 'Aktif',
                                                            'inactive' => 'Tidak Aktif',
                                                        ])
                                                        ->default('active')
                                                        ->required(),
                                                        
                                                    Forms\Components\Select::make('type')
                                                        ->label('Tipe Karyawan')
                                                        ->native(false)
                                                        ->options([
                                                            'Harian' => 'Harian',
                                                            'Karyawan Tetap' => 'Karyawan Tetap',
                                                        ])
                                                        ->default('Harian')
                                                        ->required(),
                                                ])
                                                ->columns(2),

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
                                                        ->native(false)
                                                        ->helperText('Status pernikahan menentukan kategori TK atau K'),

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
                                                        ->required(fn (Forms\Get $get) => $get('marital_status') === 'single')
                                                        ->helperText('Jumlah tanggungan untuk status TK (Tidak Kawin)'),

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
                                                        ->required(fn (Forms\Get $get) => $get('marital_status') === 'married')
                                                        ->helperText('Jumlah tanggungan untuk status K (Kawin)'),
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
                                                    $terCategory = self::determineTerCategory($employee);
                                                    $terPercentage = self::calculateTerPercentage($employee->salary ?? 0, $terCategory);
                                                    
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
                                    
                                ])
                                ->columns(1),
                        ]),
                        
                    Wizard\Step::make('Detail Pajak Penghasilan')
                        ->icon('heroicon-o-currency-dollar')
                        ->description('Konfigurasi perhitungan TER dan PPh 21')
                        ->schema([
                            Section::make('Perhitungan Pajak Penghasilan')
                                ->description('Sistem akan menghitung TER berdasarkan status TK/K karyawan')
                                ->schema([
                                    Forms\Components\Grid::make(2)
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
                                                ->helperText('Kategori ditentukan otomatis berdasarkan status TK/K karyawan')
                                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                                    $employeeId = $get('employee_id');
                                                    $employeeSalary = 0;
                                                    
                                                    if ($employeeId) {
                                                        $employee = Employee::find($employeeId);
                                                        if ($employee) {
                                                            $employeeSalary = is_numeric($employee->salary) 
                                                                ? $employee->salary 
                                                                : preg_replace('/[^0-9.]/', '', $employee->salary ?? '0');
                                                        }
                                                    }
                                                    
                                                    if ($state !== 'manual' && $employeeSalary > 0) {
                                                        $terPercentage = self::calculateTerPercentage($employeeSalary, $state);
                                                        $set('ter_amount', $terPercentage);
                                                        
                                                        $terAmount = $employeeSalary * ($terPercentage / 100);
                                                        $pphAmount = $employeeSalary + $terAmount;
                                                        
                                                        $set('pph_21_amount', number_format($pphAmount, 2, '.', ','));
                                                    }
                                                }),

                                            Forms\Components\TextInput::make('ter_amount')
                                                ->label('Tarif TER (%)')
                                                ->required()
                                                ->suffix('%')
                                                ->default(5)
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->step(0.01)
                                                ->disabled(fn (Forms\Get $get) => $get('ter_category') !== 'manual')
                                                ->live(onBlur: true)
                                                ->dehydrated()
                                                ->helperText('Tarif TER dihitung otomatis berdasarkan kategori dan gaji')
                                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                                    if ($get('ter_category') === 'manual') {
                                                        $employeeId = $get('employee_id');
                                                        $employeeSalary = 0;
                                                        
                                                        if ($employeeId) {
                                                            $employee = Employee::find($employeeId);
                                                            if ($employee) {
                                                                $employeeSalary = is_numeric($employee->salary) 
                                                                    ? $employee->salary 
                                                                    : preg_replace('/[^0-9.]/', '', $employee->salary ?? '0');
                                                            }
                                                        }
                                                        
                                                        $cleanedTerPercentage = preg_replace('/[^0-9.]/', '', $state);
                                                        
                                                        $terPercentage = floatval($cleanedTerPercentage) / 100;
                                                        $terAmount = $employeeSalary * $terPercentage;
                                                        $pphAmount = $employeeSalary + $terAmount;
                                                        
                                                        $set('pph_21_amount', number_format($pphAmount, 2, '.', ','));
                                                    }
                                                }),
                                        ]),

                                    Forms\Components\TextInput::make('pph_21_amount')
                                        ->label('Jumlah PPh 21')
                                        ->required()
                                        ->prefix('Rp')
                                        ->placeholder('0.00')
                                        ->mask(RawJs::make('$money($input)'))
                                        ->dehydrateStateUsing(fn ($state) => preg_replace('/[^0-9.]/', '', $state))
                                        ->rules(['required', 'numeric', 'min:0'])
                                        ->helperText('PPh 21 = Gaji Bulanan + (Gaji Bulanan × Tarif TER%)')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                        
                    Wizard\Step::make('Dokumen dan Catatan')
                        ->icon('heroicon-o-document-text')
                        ->description('Upload dokumen bukti potong dan bukti setor')
                        ->schema([
                            Section::make('Upload Dokumen PPh 21')
                                ->description('Upload dokumen bukti potong PPh 21 dan bukti setor (jika ada)')
                                ->schema([
                                    Forms\Components\FileUpload::make('file_path')
                                        ->label('Bukti Potong PPh 21')
                                        ->required()
                                        ->disk('public')
                                        ->openable()
                                        ->downloadable()
                                        ->directory(function (Forms\Get $get) {
                                            return self::generateDirectoryPath($get);
                                        })
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                            return self::generateFileName($get, $file->getClientOriginalName(), 'PPH-21');
                                        })
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->maxSize(5120) // 5MB
                                        ->helperText(function (Forms\Get $get) {
                                            $path = self::generateDirectoryPath($get);
                                            return "File akan disimpan di: storage/{$path}/PPH-21-[Nama Karyawan].[ext] | Maksimal 5MB";
                                        })
                                        ->imageEditor()
                                        ->columnSpanFull(),

                                    Forms\Components\FileUpload::make('bukti_setor')
                                        ->label('Bukti Setor PPh 21 (Opsional)')
                                        ->openable()
                                        ->downloadable()
                                        ->disk('public')
                                        ->directory(function (Forms\Get $get) {
                                            $basePath = self::generateDirectoryPath($get);
                                            return $basePath . '/Bukti-Setor';
                                        })
                                        ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Forms\Get $get): string {
                                            return self::generateFileName($get, $file->getClientOriginalName(), 'Bukti-Setor-PPH-21');
                                        })
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                        ->maxSize(5120) // 5MB
                                        ->helperText(function (Forms\Get $get) {
                                            $path = self::generateDirectoryPath($get);
                                            return "File akan disimpan di: storage/{$path}/Bukti-Setor/ | Maksimal 5MB";
                                        })
                                        ->imageEditor()
                                        ->columnSpanFull(),
                                        
                                    Forms\Components\RichEditor::make('notes')
                                        ->label('Catatan Tambahan')
                                        ->placeholder('Tambahkan catatan relevan tentang pajak penghasilan ini, seperti informasi khusus, perubahan status, atau hal penting lainnya...')
                                        ->maxLength(2000)
                                        ->helperText('Maksimal 2000 karakter')
                                        ->columnSpanFull()
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                            'underline',
                                            'bulletList',
                                            'orderedList',
                                            'link',
                                            'undo',
                                            'redo',
                                        ]),
                                ]),
                                
                            Section::make('Ringkasan Data')
                                ->description('Periksa kembali data yang akan disimpan')
                                ->schema([
                                    Forms\Components\Placeholder::make('data_summary')
                                        ->label('')
                                        ->content(function (Forms\Get $get) {
                                            $taxReportId = $get('tax_report_id');
                                            $employeeId = $get('employee_id');
                                            $terCategory = $get('ter_category');
                                            $terAmount = $get('ter_amount') ?? 0;
                                            $pphAmount = $get('pph_21_amount') ?? 0;
                                            
                                            if (!$taxReportId || !$employeeId) {
                                                return new \Illuminate\Support\HtmlString('
                                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                                        <p class="text-amber-700">Lengkapi data laporan pajak dan karyawan untuk melihat ringkasan</p>
                                                    </div>
                                                ');
                                            }
                                            
                                            $taxReport = TaxReport::with('client')->find($taxReportId);
                                            $employee = Employee::find($employeeId);
                                            
                                            if (!$taxReport || !$employee) {
                                                return new \Illuminate\Support\HtmlString('
                                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                                        <p class="text-red-700">Data tidak valid, silakan periksa kembali pilihan Anda</p>
                                                    </div>
                                                ');
                                            }
                                            
                                            $clientName = $taxReport->client->name ?? 'Unknown Client';
                                            $monthName = self::convertToIndonesianMonth($taxReport->month);
                                            $taxStatus = self::getEmployeeTaxStatus($employee);
                                            $cleanPphAmount = preg_replace('/[^0-9.]/', '', $pphAmount);
                                            
                                            $categoryNames = [
                                                'A' => 'Kategori A (TK/0, TK/1, K/0)',
                                                'B' => 'Kategori B (TK/2, TK/3, K/1, K/2)',
                                                'C' => 'Kategori C (K/3)',
                                                'manual' => 'Input Manual',
                                            ];
                                            
                                            $categoryName = $categoryNames[$terCategory] ?? $terCategory;
                                            
                                            return new \Illuminate\Support\HtmlString("
                                                <div class='bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6'>
                                                    <h3 class='text-lg font-semibold text-blue-900 mb-4'>Ringkasan Data PPh 21</h3>
                                                    <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
                                                        <div class='space-y-3'>
                                                            <div>
                                                                <span class='text-sm font-medium text-blue-700'>Klien & Periode:</span>
                                                                <p class='text-blue-900 font-semibold'>{$clientName} - {$monthName}</p>
                                                            </div>
                                                            <div>
                                                                <span class='text-sm font-medium text-blue-700'>Karyawan:</span>
                                                                <p class='text-blue-900 font-semibold'>{$employee->name}</p>
                                                            </div>
                                                            <div>
                                                                <span class='text-sm font-medium text-blue-700'>Status Pajak:</span>
                                                                <span class='bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-medium'>{$taxStatus}</span>
                                                            </div>
                                                        </div>
                                                        <div class='space-y-3'>
                                                            <div>
                                                                <span class='text-sm font-medium text-blue-700'>Kategori TER:</span>
                                                                <p class='text-blue-900 font-semibold'>{$categoryName}</p>
                                                            </div>
                                                            <div>
                                                                <span class='text-sm font-medium text-blue-700'>Tarif TER:</span>
                                                                <p class='text-blue-900 font-semibold'>{$terAmount}%</p>
                                                            </div>
                                                            <div>
                                                                <span class='text-sm font-medium text-blue-700'>Jumlah PPh 21:</span>
                                                                <p class='text-blue-900 font-bold text-lg'>Rp " . number_format($cleanPphAmount, 0, ',', '.') . "</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ");
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ])
                ->skippable()
                ->persistStepInQueryString('income-tax-wizard-step')
                ->columnSpanFull(),
            ])
            ->statePath('data')
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('user_avatar')
                    ->label('Dibuat Oleh')
                    ->circular()
                    ->state(function ($record) {
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

                Tables\Columns\TextColumn::make('taxReport.client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('taxReport.month')
                    ->label('Periode')
                    ->formatStateUsing(function ($state) {
                        return self::convertToIndonesianMonth($state);
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
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
                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ada')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\BadgeColumn::make('employee.type')
                    ->label('Tipe Karyawan')
                    ->colors([
                        'primary' => 'Karyawan Tetap',
                        'warning' => 'Harian',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('ter_category')
                    ->label('Kategori TER')
                    ->badge()
                    ->colors([
                        'primary' => 'A',
                        'success' => 'B', 
                        'warning' => 'C',
                        'gray' => 'manual',
                    ])
                    ->sortable(),
                    
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
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('taxReport.client_id')
                    ->label('Klien')
                    ->options(function () {
                        return \App\Models\Client::pluck('name', 'id')->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('tax_report_id')
                    ->label('Periode')
                    ->options(function () {
                        return TaxReport::with('client')
                            ->get()
                            ->mapWithKeys(function ($taxReport) {
                                $clientName = $taxReport->client->name ?? 'Unknown Client';
                                $monthName = self::convertToIndonesianMonth($taxReport->month);
                                return [$taxReport->id => "{$clientName} - {$monthName}"];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),
                    
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

                Tables\Filters\SelectFilter::make('employee.marital_status')
                    ->label('Status Pernikahan')
                    ->options([
                        'single' => 'TK (Belum Menikah)',
                        'married' => 'K (Menikah)',
                    ]),

                Tables\Filters\SelectFilter::make('ter_category')
                    ->label('Kategori TER')
                    ->options([
                        'A' => 'Kategori A',
                        'B' => 'Kategori B',
                        'C' => 'Kategori C',
                        'manual' => 'Manual',
                    ]),

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
                                                $taxReport = $record->taxReport;
                                                $clientName = Str::slug($taxReport->client->name);
                                                $monthName = self::convertToIndonesianMonth($taxReport->month);
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
                        ->form(function ($records) {
                            $recordsWithoutBuktiSetor = $records->filter(fn ($record) => empty($record->bukti_setor));
                            
                            $schema = [];
                            
                            foreach ($recordsWithoutBuktiSetor as $record) {
                                $schema[] = Section::make($record->employee->name)
                                    ->description("PPh 21 - {$record->taxReport->client->name}")
                                    ->schema([
                                        Forms\Components\FileUpload::make("records.{$record->id}.bukti_setor")
                                            ->label('Bukti Setor')
                                            ->openable()
                                            ->downloadable()
                                            ->disk('public')
                                            ->directory(function () use ($record) {
                                                $taxReport = $record->taxReport;
                                                $clientName = Str::slug($taxReport->client->name);
                                                $monthName = self::convertToIndonesianMonth($taxReport->month);
                                                return "clients/{$clientName}/SPT/{$monthName}/PPH/Bukti-Setor";
                                            })
                                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file) use ($record): string {
                                                $employeeName = Str::slug($record->employee->name);
                                                $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                                                return "Bukti-Setor-PPH-21-{$employeeName}.{$extension}";
                                            })
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/webp'])
                                            ->columnSpanFull(),
                                            
                                        Forms\Components\Textarea::make("records.{$record->id}.notes")
                                            ->label('Catatan (Opsional)')
                                            ->placeholder('Catatan tambahan untuk bukti setor ini')
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(1)
                                    ->collapsible();
                            }
                            
                            return $schema;
                        })
                        ->action(function ($records, array $data) {
                            $successCount = 0;
                            $errors = [];
                            $processedEmployees = [];
                            
                            foreach ($data['records'] as $recordId => $recordData) {
                                try {
                                    $record = $records->find($recordId);
                                    
                                    if ($record && !empty($recordData['bukti_setor'])) {
                                        $updateData = [
                                            'bukti_setor' => $recordData['bukti_setor']
                                        ];
                                        
                                        if (!empty($recordData['notes'])) {
                                            $existingNotes = $record->notes ?? '';
                                            $newNote = "\n[Bukti Setor] " . $recordData['notes'];
                                            $updateData['notes'] = $existingNotes . $newNote;
                                        }
                                        
                                        $record->update($updateData);
                                        
                                        $successCount++;
                                        $processedEmployees[] = $record->employee->name;
                                    }
                                } catch (\Exception $e) {
                                    $employeeName = $records->find($recordId)->employee->name ?? "ID: {$recordId}";
                                    $errors[] = "Error untuk {$employeeName}: " . $e->getMessage();
                                }
                            }
                            
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
                            
                            if (!empty($errors)) {
                                Notification::make()
                                    ->title('Beberapa Upload Gagal')
                                    ->body(implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '<br>... dan ' . (count($errors) - 5) . ' error lainnya' : ''))
                                    ->warning()
                                    ->duration(8000)
                                    ->send();
                            }
                            
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
            ->emptyStateDescription('Tambahkan data pajak penghasilan (PPh 21) karyawan. Data PPh 21 membantu mencatat kewajiban pajak penghasilan.')
            ->emptyStateIcon('heroicon-o-currency-dollar')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Data PPh 21')
                    ->modalWidth('7xl')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomeTaxes::route('/'),
            'create' => Pages\CreateIncomeTax::route('/create'),
            'edit' => Pages\EditIncomeTax::route('/{record}/edit'),
        ];
    }
}