<?php

namespace App\Filament\Imports;

use App\Models\IncomeTax;
use App\Models\Employee;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

class IncomeTaxImporter extends Importer
{
    protected static ?string $model = IncomeTax::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('masa_pajak')
                ->label('Masa Pajak')
                ->requiredMapping()
                ->rules(['required'])
                ->example('06062025'),

            ImportColumn::make('nomor_pemotongan')
                ->label('Nomor Pemotongan')
                ->requiredMapping()
                ->rules(['required', 'max:50'])
                ->example('2503FY3OF'),

            ImportColumn::make('status')
                ->label('Status')
                ->rules(['max:50'])
                ->example('NORMAL'),

            ImportColumn::make('nitku')
                ->label('NITKU/Nomor Identitas Sub Unit Organisasi')
                ->rules(['max:50'])
                ->example('0020233979726000000000'),

            ImportColumn::make('jenis_pajak')
                ->label('Jenis Pajak')
                ->requiredMapping()
                ->rules(['required', 'max:50'])
                ->example('Pasal 21'),

            ImportColumn::make('kode_objek_pajak')
                ->label('Kode Objek Pajak')
                ->rules(['max:50'])
                ->example('21-100-01'),

            ImportColumn::make('npwp')
                ->label('NPWP')
                ->rules(['max:20'])
                ->example('9990000000999000'),

            ImportColumn::make('nama')
                ->label('Nama')
                ->rules(['max:255'])
                ->example('PENERIMA PENGHASILAN'),

            ImportColumn::make('dasar_pengenaan_pajak')
                ->label('Dasar Pengenaan Pajak (Rp)')
                ->numeric()
                ->rules(['numeric', 'min:0'])
                ->example('3850000'),

            ImportColumn::make('pajak_penghasilan')
                ->label('Pajak Penghasilan (Rp)')
                ->numeric()
                ->rules(['numeric', 'min:0'])
                ->example('0'),

            ImportColumn::make('fasilitas_pajak')
                ->label('Fasilitas Pajak')
                ->rules(['max:100'])
                ->example('Tanpa Fasilitas'),

            ImportColumn::make('dilaporkan_dalam_spt')
                ->label('Dilaporkan Dalam SPT')
                ->boolean()
                ->rules(['boolean'])
                ->example('TRUE'),

            ImportColumn::make('spt_sedang_diperiksa')
                ->label('SPT Telah/Sedang Diperiksa')
                ->boolean()
                ->rules(['boolean'])
                ->example('FALSE'),

            ImportColumn::make('spt_dalam_penanganan_hukum')
                ->label('SPT Dalam Penanganan Hukum')
                ->boolean()
                ->rules(['boolean'])
                ->example('FALSE'),
        ];
    }

    public function resolveRecord(): ?IncomeTax
    {
        // Get tax_report_id from options
        $taxReportId = $this->options['tax_report_id'] ?? null;
        
        if (!$taxReportId) {
            throw new \Exception('Tax Report ID is required');
        }

        // Format NPWP - remove dots and dashes, keep only numbers
        $npwp = $this->data['npwp'] ?? null;
        if ($npwp) {
            $npwp = preg_replace('/[^0-9]/', '', (string) $npwp);
        }

        // Format Masa Pajak to string if it's numeric
        $masaPajak = $this->data['masa_pajak'] ?? null;
        if ($masaPajak) {
            $masaPajak = (string) $masaPajak;
        }

        // Try to find matching employee by name or NPWP
        $employeeId = null;
        if (!empty($this->data['nama']) || !empty($npwp)) {
            $employee = Employee::query()
                ->when($npwp, function ($query) use ($npwp) {
                    $query->where('npwp', $npwp);
                })
                ->when(empty($npwp) && !empty($this->data['nama']), function ($query) {
                    $query->where('name', 'LIKE', '%' . $this->data['nama'] . '%');
                })
                ->first();
            
            if ($employee) {
                $employeeId = $employee->id;
            }
        }

        // Find existing record or create new
        return IncomeTax::firstOrNew([
            'tax_report_id' => $taxReportId,
            'nomor_pemotongan' => $this->data['nomor_pemotongan'],
        ], [
            'employee_id' => $employeeId,
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import bukti potong PPh selesai. ' . number_format($import->successful_rows) . ' baris berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diimpor.';
        }

        return $body;
    }

    protected function beforeFill(): void
    {
        // Clean and format NPWP
        if (isset($this->data['npwp'])) {
            $this->data['npwp'] = preg_replace('/[^0-9]/', '', (string) $this->data['npwp']);
        }

        // Format Masa Pajak
        if (isset($this->data['masa_pajak'])) {
            $this->data['masa_pajak'] = (string) $this->data['masa_pajak'];
        }

        // Set default status
        if (empty($this->data['status'])) {
            $this->data['status'] = 'NORMAL';
        }

        // Set default fasilitas pajak
        if (empty($this->data['fasilitas_pajak'])) {
            $this->data['fasilitas_pajak'] = 'Tanpa Fasilitas';
        }

        // Set created_by from auth user
        if (auth()->check()) {
            $this->data['created_by'] = auth()->id();
        }
    }
}