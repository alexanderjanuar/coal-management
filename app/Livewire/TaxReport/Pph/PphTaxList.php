<?php

namespace App\Livewire\TaxReport\Pph;

use App\Models\IncomeTax;
use App\Models\Employee;
use App\Models\TaxReport;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Component;
use Filament\Tables\Filters\SelectFilter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PphTaxList extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $taxReportId;
    public $taxReport;
    
    // Statistics
    public $pph21Total = 0;
    public $pph21Count = 0;
    public $pph23Total = 0;
    public $pph23Count = 0;
    public $pph42Total = 0;
    public $pph42Count = 0;
    public $totalPph = 0;
    public $totalCount = 0;

    public function mount($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        $this->taxReport = TaxReport::with('client')->findOrFail($taxReportId);
        $this->calculateStatistics();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                IncomeTax::query()
                    ->where('tax_report_id', $this->taxReportId)
                    ->with(['employee', 'createdBy'])
            )
            ->columns([
                TextColumn::make('masa_pajak')
                    ->label('Masa Pajak')
                    ->formatStateUsing(fn ($state) => $this->formatMasaPajak($state))
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('nomor_pemotongan')
                    ->label('No. Pemotongan')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor disalin!')
                    ->copyMessageDuration(1500),
                
                TextColumn::make('jenis_pajak')
                    ->label('Jenis Pajak')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pasal 21' => 'info',
                        'Pasal 23' => 'warning',
                        'Pasal 4(2)', 'Pasal 4 ayat 2' => 'success',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('nama')
                    ->label('Nama Penerima')
                    ->searchable()
                    ->sortable()
                    ->description(fn (IncomeTax $record): string => 
                        $record->employee 
                            ? "Karyawan: {$record->employee->name}" 
                            : 'Data manual'
                    ),
                
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->formatStateUsing(fn ($state) => $this->formatNpwp($state))
                    ->searchable(),
                
                TextColumn::make('dasar_pengenaan_pajak')
                    ->label('DPP')
                    ->money('IDR')
                    ->sortable(),
                
                TextColumn::make('pajak_penghasilan')
                    ->label('PPh')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtoupper($state)) {
                        'NORMAL' => 'success',
                        'PEMBETULAN' => 'warning',
                        'PEMBATALAN' => 'danger',
                        default => 'gray',
                    }),
                
                TextColumn::make('dilaporkan_dalam_spt')
                    ->label('Lapor SPT')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak')
                    ->color(fn ($state): string => $state ? 'success' : 'gray'),
                
                TextColumn::make('bukti_potong')
                    ->label('Bukti')
                    ->formatStateUsing(fn ($state) => $state ? '✓' : '-')
                    ->alignCenter()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('jenis_pajak')
                    ->label('Jenis Pajak')
                    ->options([
                        'Pasal 21' => 'PPh 21',
                        'Pasal 23' => 'PPh 23',
                        'Pasal 4(2)' => 'PPh 4(2)',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                Action::make('import_excel')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel.sheet.macroEnabled.12',
                            ])
                            ->maxSize(10240) // 10MB
                            ->helperText('Upload file Excel (.xlsx atau .xls) dengan format yang sesuai. Maksimal 10MB.')
                            ->disk('local')
                            ->directory('temp-imports')
                            ->visibility('private'),
                    ])
                    ->modalHeading('Import Data Bukti Potong PPh')
                    ->modalDescription('Upload file Excel yang berisi data bukti potong PPh dari DJP. File harus mengikuti format template yang telah ditentukan.')
                    ->modalSubmitActionLabel('Import Sekarang')
                    ->modalWidth('md')
                    ->action(function (array $data): void {
                        try {
                            $this->importExcelFile($data['file']);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import Gagal')
                                ->body('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('download_template')
                    ->label('Download Template')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function () {
                        $templatePath = storage_path('app/templates/income_tax_template.xlsx');
                        
                        if (file_exists($templatePath)) {
                            return response()->download($templatePath, 'Template_Import_PPh.xlsx');
                        }
                        
                        Notification::make()
                            ->title('Template Tidak Ditemukan')
                            ->body('Silakan jalankan: php artisan generate:income-tax-template')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn () => file_exists(storage_path('app/templates/income_tax_template.xlsx'))),
                
                Action::make('refresh_statistics')
                    ->label('Refresh Statistik')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function () {
                        $this->calculateStatistics();
                        
                        Notification::make()
                            ->title('Statistik Diperbarui')
                            ->body('Data statistik telah di-refresh.')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->form([
                        TextInput::make('nomor_pemotongan')
                            ->label('Nomor Pemotongan')
                            ->required()
                            ->maxLength(50),
                        
                        Select::make('jenis_pajak')
                            ->label('Jenis Pajak')
                            ->required()
                            ->options([
                                'Pasal 21' => 'PPh 21',
                                'Pasal 23' => 'PPh 23',
                                'Pasal 4(2)' => 'PPh 4(2)',
                            ]),
                        
                        TextInput::make('nama')
                            ->label('Nama Penerima')
                            ->maxLength(255),
                        
                        TextInput::make('npwp')
                            ->label('NPWP')
                            ->mask('99.999.999.9-999.999')
                            ->maxLength(20),
                        
                        TextInput::make('dasar_pengenaan_pajak')
                            ->label('DPP')
                            ->numeric()
                            ->prefix('Rp'),
                        
                        TextInput::make('pajak_penghasilan')
                            ->label('Pajak Penghasilan')
                            ->numeric()
                            ->prefix('Rp'),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'NORMAL' => 'Normal',
                                'PEMBETULAN' => 'Pembetulan',
                                'PEMBATALAN' => 'Pembatalan',
                            ]),
                    ])
                    ->fillForm(fn (IncomeTax $record): array => [
                        'nomor_pemotongan' => $record->nomor_pemotongan,
                        'jenis_pajak' => $record->jenis_pajak,
                        'nama' => $record->nama,
                        'npwp' => $record->npwp,
                        'dasar_pengenaan_pajak' => $record->dasar_pengenaan_pajak,
                        'pajak_penghasilan' => $record->pajak_penghasilan,
                        'status' => $record->status,
                    ])
                    ->action(function (IncomeTax $record, array $data): void {
                        $record->update($data);
                        
                        $this->calculateStatistics();
                        
                        Notification::make()
                            ->title('Data Berhasil Diupdate')
                            ->success()
                            ->send();
                    }),
                
                Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (IncomeTax $record) {
                        $record->delete();
                        
                        $this->calculateStatistics();
                        
                        Notification::make()
                            ->title('Data Berhasil Dihapus')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->after(function () {
                        $this->calculateStatistics();
                    }),
                
                BulkAction::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Select::make('status')
                            ->label('Status Baru')
                            ->options([
                                'NORMAL' => 'Normal',
                                'PEMBETULAN' => 'Pembetulan',
                                'PEMBATALAN' => 'Pembatalan',
                            ])
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $records->each->update(['status' => $data['status']]);
                        
                        Notification::make()
                            ->title('Status Berhasil Diupdate')
                            ->body(count($records) . ' data berhasil diupdate.')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading('Belum ada data PPh')
            ->emptyStateDescription('Tambahkan data PPh menggunakan tombol Import Excel')
            ->emptyStateIcon('heroicon-o-document-text')
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    protected function importExcelFile(string $filePath): void
    {
        DB::beginTransaction();
        
        try {
            $fullPath = Storage::disk('local')->path($filePath);
            
            // Load Excel file
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get the highest row number
            $highestRow = $worksheet->getHighestRow();
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    $rowData = [
                        'masa_pajak' => $worksheet->getCell('A' . $row)->getValue(),
                        'nomor_pemotongan' => $worksheet->getCell('B' . $row)->getValue(),
                        'status' => $worksheet->getCell('C' . $row)->getValue() ?: 'NORMAL',
                        'nitku' => $worksheet->getCell('D' . $row)->getValue(),
                        'jenis_pajak' => $worksheet->getCell('E' . $row)->getValue(),
                        'kode_objek_pajak' => $worksheet->getCell('F' . $row)->getValue(),
                        'npwp' => $worksheet->getCell('G' . $row)->getValue(),
                        'nama' => $worksheet->getCell('H' . $row)->getValue(),
                        'dasar_pengenaan_pajak' => $worksheet->getCell('I' . $row)->getValue() ?: 0,
                        'pajak_penghasilan' => $worksheet->getCell('J' . $row)->getValue() ?: 0,
                        'fasilitas_pajak' => $worksheet->getCell('K' . $row)->getValue() ?: 'Tanpa Fasilitas',
                        'dilaporkan_dalam_spt' => $this->convertToBoolean($worksheet->getCell('L' . $row)->getValue()),
                        'spt_sedang_diperiksa' => $this->convertToBoolean($worksheet->getCell('M' . $row)->getValue()),
                        'spt_dalam_penanganan_hukum' => $this->convertToBoolean($worksheet->getCell('N' . $row)->getValue()),
                    ];
                    
                    // Skip empty rows
                    if (empty($rowData['nomor_pemotongan']) || empty($rowData['jenis_pajak'])) {
                        continue;
                    }
                    
                    // Clean and format data
                    $rowData = $this->cleanRowData($rowData);
                    
                    // Try to find or create employee
                    $employeeId = $this->findOrCreateEmployeeId($rowData['npwp'], $rowData['nama']);
                    
                    // Create or update record
                    // Note: employee_id can be null for manual entries without matching employee
                    IncomeTax::updateOrCreate(
                        [
                            'tax_report_id' => $this->taxReportId,
                            'nomor_pemotongan' => $rowData['nomor_pemotongan'],
                        ],
                        [
                            'employee_id' => $employeeId,
                            'masa_pajak' => $rowData['masa_pajak'],
                            'status' => $rowData['status'],
                            'nitku' => $rowData['nitku'],
                            'jenis_pajak' => $rowData['jenis_pajak'],
                            'kode_objek_pajak' => $rowData['kode_objek_pajak'],
                            'npwp' => $rowData['npwp'],
                            'nama' => $rowData['nama'],
                            'dasar_pengenaan_pajak' => $rowData['dasar_pengenaan_pajak'],
                            'pajak_penghasilan' => $rowData['pajak_penghasilan'],
                            'fasilitas_pajak' => $rowData['fasilitas_pajak'],
                            'dilaporkan_dalam_spt' => $rowData['dilaporkan_dalam_spt'],
                            'spt_sedang_diperiksa' => $rowData['spt_sedang_diperiksa'],
                            'spt_dalam_penanganan_hukum' => $rowData['spt_dalam_penanganan_hukum'],
                            'created_by' => auth()->id(),
                        ]
                    );
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Baris {$row}: " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            // Delete temporary file
            Storage::disk('local')->delete($filePath);
            
            // Recalculate statistics
            $this->calculateStatistics();
            
            // Show notification
            if ($errorCount > 0) {
                Notification::make()
                    ->title('Import Selesai dengan Peringatan')
                    ->body("Berhasil: {$successCount} baris | Gagal: {$errorCount} baris")
                    ->warning()
                    ->send();
                    
                if (count($errors) <= 10) {
                    foreach ($errors as $error) {
                        Notification::make()
                            ->title('Error Detail')
                            ->body($error)
                            ->danger()
                            ->send();
                    }
                }
            } else {
                Notification::make()
                    ->title('Import Berhasil!')
                    ->body("{$successCount} data bukti potong berhasil diimpor")
                    ->success()
                    ->send();
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete temporary file
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
            
            throw $e;
        }
    }

    protected function cleanRowData(array $data): array
    {
        // Clean NPWP - remove formatting
        if (!empty($data['npwp'])) {
            $data['npwp'] = preg_replace('/[^0-9]/', '', (string) $data['npwp']);
        }
        
        // Format Masa Pajak to string
        if (!empty($data['masa_pajak'])) {
            $data['masa_pajak'] = (string) $data['masa_pajak'];
        }
        
        // Ensure numbers are numeric
        $data['dasar_pengenaan_pajak'] = (float) ($data['dasar_pengenaan_pajak'] ?? 0);
        $data['pajak_penghasilan'] = (float) ($data['pajak_penghasilan'] ?? 0);
        
        return $data;
    }

    protected function findOrCreateEmployeeId(?string $npwp, ?string $nama): ?int
    {
        if (empty($npwp) && empty($nama)) {
            return null;
        }
        
        // Try to find existing employee
        $employee = null;
        
        if (!empty($npwp)) {
            $employee = Employee::where('npwp', $npwp)->first();
        }
        
        if (!$employee && !empty($nama)) {
            $employee = Employee::where('name', $nama)->first();
        }
        
        // If employee doesn't exist, create a new one
        if (!$employee && !empty($nama)) {
            try {
                $employee = Employee::create([
                    'client_id' => $this->taxReport->client_id,
                    'name' => $nama,
                    'npwp' => $npwp,
                    'status' => 'active',
                    'type' => 'Karyawan Tetap',
                    'marital_status' => 'single',
                    'tk' => 0,
                    'k' => 0,
                ]);
                
                // Log the creation
                \Log::info("Auto-created employee: {$nama} (NPWP: {$npwp})");
                
            } catch (\Exception $e) {
                \Log::error("Failed to create employee: {$nama} - " . $e->getMessage());
                return null;
            }
        }
        
        return $employee ? $employee->id : null;
    }

    protected function convertToBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtoupper(trim((string) $value));
        
        return in_array($value, ['TRUE', '1', 'YES', 'Y']);
    }

    public function calculateStatistics(): void
    {
        $incomeTaxes = IncomeTax::where('tax_report_id', $this->taxReportId)->get();
        
        // PPh 21
        $pph21 = $incomeTaxes->where('jenis_pajak', 'Pasal 21');
        $this->pph21Total = $pph21->sum('pajak_penghasilan');
        $this->pph21Count = $pph21->count();
        
        // PPh 23
        $pph23 = $incomeTaxes->where('jenis_pajak', 'Pasal 23');
        $this->pph23Total = $pph23->sum('pajak_penghasilan');
        $this->pph23Count = $pph23->count();
        
        // PPh 4(2)
        $pph42 = $incomeTaxes->whereIn('jenis_pajak', ['Pasal 4(2)', 'Pasal 4 ayat 2']);
        $this->pph42Total = $pph42->sum('pajak_penghasilan');
        $this->pph42Count = $pph42->count();
        
        // Totals
        $this->totalPph = $incomeTaxes->sum('pajak_penghasilan');
        $this->totalCount = $incomeTaxes->count();
    }

    private function formatNpwp(?string $npwp): string
    {
        if (!$npwp) return '-';
        
        // Remove any existing formatting
        $npwp = preg_replace('/[^0-9]/', '', $npwp);
        
        // Format: XX.XXX.XXX.X-XXX.XXX
        if (strlen($npwp) === 15) {
            return preg_replace(
                '/(\d{2})(\d{3})(\d{3})(\d{1})(\d{3})(\d{3})/',
                '$1.$2.$3.$4-$5.$6',
                $npwp
            );
        }
        
        return $npwp;
    }

    private function formatMasaPajak(?string $masaPajak): string
    {
        if (!$masaPajak) return '-';
        
        try {
            // Format: MMDDYYYY (e.g., 06062025)
            $month = substr($masaPajak, 0, 2);
            $year = substr($masaPajak, 4, 4);
            
            $monthNames = [
                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar',
                '04' => 'Apr', '05' => 'Mei', '06' => 'Jun',
                '07' => 'Jul', '08' => 'Agu', '09' => 'Sep',
                '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
            ];
            
            return ($monthNames[$month] ?? $month) . ' ' . $year;
        } catch (\Exception $e) {
            return $masaPajak;
        }
    }

    public function render()
    {
        return view('livewire.tax-report.pph.pph-tax-list');
    }
}