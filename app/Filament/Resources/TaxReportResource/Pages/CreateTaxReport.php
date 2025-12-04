<?php

namespace App\Filament\Resources\TaxReportResource\Pages;

use App\Filament\Resources\TaxReportResource;
use App\Models\TaxReport;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateTaxReport extends CreateRecord
{
    protected static string $resource = TaxReportResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Laporan Pajak')
                    ->description('Buat satu atau beberapa laporan pajak untuk bulan yang berbeda')
                    ->schema([
                        Select::make('client_id')
                            ->label('Klien')
                            ->required()
                            ->relationship(
                                'client',
                                'name',
                                fn ($query) => $query
                                    ->where('status', 'Active')
                                    ->where(function ($q) {
                                        $q->where('ppn_contract', true)
                                          ->orWhere('pph_contract', true)
                                          ->orWhere('bupot_contract', true);
                                    })
                            )
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                // Reset months when client changes
                                $set('months', []);
                                $set('month', null);
                            })
                            ->helperText('Hanya menampilkan klien aktif dengan kontrak PPN, PPH, atau Bupot')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('NPWP')
                                    ->label('NPWP')
                                    ->maxLength(255),
                                TextInput::make('KPP')
                                    ->label('KPP')
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->unique(ignorable: fn($record) => $record)
                                    ->maxLength(255),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'Active' => 'Aktif',
                                        'Inactive' => 'Tidak Aktif',
                                    ])
                                    ->default('Active'),
                                ]),

                        Toggle::make('create_multiple')
                            ->label('Buat Multiple Bulan')
                            ->helperText('Aktifkan untuk membuat laporan pajak untuk beberapa bulan sekaligus')
                            ->reactive()
                            ->default(false)
                            ->afterStateUpdated(function (Set $set, $state) {
                                if (!$state) {
                                    // If switching to single mode, clear months array
                                    $set('months', []);
                                    $set('month', null);
                                }
                            }),

                        // Single month selection (when create_multiple is false)
                        Select::make('month')
                            ->label('Bulan')
                            ->required(fn (Get $get): bool => !$get('create_multiple'))
                            ->visible(fn (Get $get): bool => !$get('create_multiple'))
                            ->native(false)
                            ->options([
                                'January' => 'Januari',
                                'February' => 'Februari',
                                'March' => 'Maret',
                                'April' => 'April',
                                'May' => 'Mei',
                                'June' => 'Juni',
                                'July' => 'Juli',
                                'August' => 'Agustus',
                                'September' => 'September',
                                'October' => 'Oktober',
                                'November' => 'November',
                                'December' => 'Desember',
                            ])
                            ->reactive(),

                        // Multiple months selection (when create_multiple is true)
                        Select::make('months')
                            ->label('Pilih Bulan')
                            ->required(fn (Get $get): bool => $get('create_multiple'))
                            ->visible(fn (Get $get): bool => $get('create_multiple'))
                            ->multiple()
                            ->native(false)
                            ->options([
                                'January' => 'Januari',
                                'February' => 'Februari',
                                'March' => 'Maret',
                                'April' => 'April',
                                'May' => 'Mei',
                                'June' => 'Juni',
                                'July' => 'Juli',
                                'August' => 'Agustus',
                                'September' => 'September',
                                'October' => 'Oktober',
                                'November' => 'November',
                                'December' => 'Desember',
                            ])
                            ->placeholder('Pilih satu atau lebih bulan')
                            ->helperText('Anda bisa memilih beberapa bulan untuk membuat laporan pajak sekaligus')
                            ->reactive(),

                        // Year selection for better organization
                        Select::make('year')
                            ->label('Tahun')
                            ->options(function () {
                                $currentYear = date('Y');
                                $years = [];
                                for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(date('Y'))
                            ->required()
                            ->native(false)
                            ->helperText('Tahun untuk laporan pajak (untuk organisasi yang lebih baik)')
                            ->reactive(),

                        // Show preview using blade template
                        Forms\Components\Placeholder::make('tax_report_preview')
                            ->label('')
                            ->content(function (Get $get) {
                                $clientId = $get('client_id');
                                $createMultiple = $get('create_multiple');
                                $months = $createMultiple ? ($get('months') ?? []) : (array) $get('month');
                                $year = $get('year') ?? date('Y');
                                
                                $filteredMonths = array_filter($months);
                                $client = $clientId ? \App\Models\Client::find($clientId) : null;
                                $clientName = $client ? $client->name : 'Unknown Client';
                                
                                // Check existing reports
                                $existingReports = [];
                                $newMonths = $filteredMonths;
                                
                                if ($clientId && !empty($filteredMonths)) {
                                    $existingReports = TaxReport::where('client_id', $clientId)
                                        ->whereIn('month', $filteredMonths)
                                        ->whereYear('created_at', $year)
                                        ->pluck('month')
                                        ->toArray();
                                    
                                    $newMonths = array_diff($filteredMonths, $existingReports);
                                }
                                
                                return view('components.tax-reports.tax-report-preview', [
                                    'clientId' => $clientId,
                                    'clientName' => $clientName,
                                    'months' => $months,
                                    'filteredMonths' => $filteredMonths,
                                    'existingMonths' => $existingReports,
                                    'newMonths' => $newMonths,
                                    'year' => $year,
                                    'createMultiple' => $createMultiple,
                                ]);
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $createMultiple = $data['create_multiple'] ?? false;
        $clientId = $data['client_id'];
        $year = $data['year'] ?? date('Y');
        
        if ($createMultiple) {
            $months = $data['months'] ?? [];
            return $this->createMultipleTaxReports($clientId, $months, $year);
        } else {
            $month = $data['month'];
            return $this->createSingleTaxReport($clientId, $month, $year);
        }
    }

    protected function createSingleTaxReport(int $clientId, string $month, int $year): Model
    {
        // Check if already exists
        $existing = TaxReport::where('client_id', $clientId)
            ->where('month', $month)
            ->whereYear('created_at', $year)
            ->first();

        if ($existing) {
            Notification::make()
                ->title('Laporan Sudah Ada')
                ->body("Laporan pajak untuk {$month} {$year} sudah ada untuk klien ini.")
                ->warning()
                ->send();
            return $existing;
        }

        $taxReport = TaxReport::create([
            'client_id' => $clientId,
            'month' => $month,
            'created_by' => auth()->id(),
            'created_at' => now()->setYear($year),
            'updated_at' => now(),
        ]);

        Notification::make()
            ->title('Laporan Pajak Berhasil Dibuat')
            ->body("Laporan pajak untuk {$month} {$year} berhasil dibuat.")
            ->success()
            ->send();

        return $taxReport;
    }

    protected function createMultipleTaxReports(int $clientId, array $months, int $year): Model
    {
        $filteredMonths = array_filter($months);
        
        if (empty($filteredMonths)) {
            throw new \Exception('Tidak ada bulan yang valid dipilih');
        }

        // Check existing reports
        $existingReports = TaxReport::where('client_id', $clientId)
            ->whereIn('month', $filteredMonths)
            ->whereYear('created_at', $year)
            ->pluck('month')
            ->toArray();

        $newMonths = array_diff($filteredMonths, $existingReports);
        
        if (empty($newMonths)) {
            Notification::make()
                ->title('Tidak Ada Laporan Baru Dibuat')
                ->body('Semua bulan yang dipilih sudah memiliki laporan pajak untuk klien ini.')
                ->warning()
                ->send();
            
            // Return the first existing report
            return TaxReport::where('client_id', $clientId)
                ->whereIn('month', $existingReports)
                ->whereYear('created_at', $year)
                ->first();
        }

        $createdReports = [];
        $userId = auth()->id();
        $now = now();

        // Use transaction for data integrity
        DB::transaction(function () use ($clientId, $newMonths, $year, $userId, $now, &$createdReports) {
            foreach ($newMonths as $month) {
                $createdReports[] = TaxReport::create([
                    'client_id' => $clientId,
                    'month' => $month,
                    'created_by' => $userId,
                    'created_at' => $now->copy()->setYear($year),
                    'updated_at' => $now,
                ]);
            }
        });

        $createdCount = count($createdReports);
        $skippedCount = count($existingReports);
        
        $message = "Berhasil membuat {$createdCount} laporan pajak";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} sudah ada dan dilewati)";
        }

        Notification::make()
            ->title('Laporan Pajak Multiple Berhasil Dibuat')
            ->body($message)
            ->success()
            ->duration(5000)
            ->send();

        // Return the first created report (required by Filament)
        return $createdReports[0];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        // We're handling notifications in the create methods, so return null here
        return null;
    }
}