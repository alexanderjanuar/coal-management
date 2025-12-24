<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GenerateIncomeTaxTemplate extends Command
{
    protected $signature = 'generate:income-tax-template';
    protected $description = 'Generate Excel template for Income Tax import';

    public function handle()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setTitle('Income Tax Import');
        
        // Define headers
        $headers = [
            'A1' => 'Masa Pajak',
            'B1' => 'Nomor Pemotongan',
            'C1' => 'Status',
            'D1' => 'NITKU/Nomor Identitas Sub Unit Organisasi',
            'E1' => 'Jenis Pajak',
            'F1' => 'Kode Objek Pajak',
            'G1' => 'NPWP',
            'H1' => 'Nama',
            'I1' => 'Dasar Pengenaan Pajak (Rp)',
            'J1' => 'Pajak Penghasilan (Rp)',
            'K1' => 'Fasilitas Pajak',
            'L1' => 'Dilaporkan Dalam SPT',
            'M1' => 'SPT Telah/Sedang Diperiksa',
            'N1' => 'SPT Dalam Penanganan Hukum',
        ];
        
        // Add headers
        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }
        
        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];
        
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(40);
        
        // Add sample data
        $sampleData = [
            ['06062025', '2503FY3OF', 'NORMAL', '0020233979726000000000', 'Pasal 21', '21-100-01', '9990000000999000', 'PENERIMA PENGHASILAN', 3850000, 0, 'Tanpa Fasilitas', 'TRUE', 'FALSE', 'FALSE'],
            ['06062025', '2503FY3OM', 'NORMAL', '0020233979726000000000', 'Pasal 23', '23-100-01', '1234567890123000', 'PT CONTOH PERUSAHAAN', 10000000, 200000, 'Tanpa Fasilitas', 'TRUE', 'FALSE', 'FALSE'],
            ['06062025', '2503FY3OT', 'NORMAL', '0020233979726000000000', 'Pasal 4(2)', '4-100-01', '9876543210987000', 'PENERIMA PENGHASILAN LAIN', 5000000, 50000, 'Tanpa Fasilitas', 'TRUE', 'FALSE', 'FALSE'],
        ];
        
        $row = 2;
        foreach ($sampleData as $data) {
            $col = 'A';
            foreach ($data as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(25);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(25);
        $sheet->getColumnDimension('N')->setWidth(30);
        
        // Create Instructions sheet
        $instructionSheet = $spreadsheet->createSheet();
        $instructionSheet->setTitle('Instruksi');
        
        $instructions = [
            ['INSTRUKSI PENGGUNAAN TEMPLATE IMPORT BUKTI POTONG PPh'],
            [''],
            ['1. KETENTUAN UMUM:'],
            ['   - Jangan mengubah nama kolom pada baris pertama'],
            ['   - Hapus data contoh sebelum mengisi data sebenarnya'],
            ['   - Pastikan semua data yang wajib diisi sudah terisi'],
            [''],
            ['2. PENJELASAN KOLOM:'],
            ['   A. Masa Pajak: Format MMDDYYYY, contoh: 06062025 untuk Juni 2025'],
            ['   B. Nomor Pemotongan: Nomor bukti pemotongan dari DJP'],
            ['   C. Status: NORMAL / PEMBETULAN / PEMBATALAN'],
            ['   D. NITKU: Nomor Identitas Sub Unit Organisasi (opsional)'],
            ['   E. Jenis Pajak: Pasal 21 / Pasal 23 / Pasal 4(2)'],
            ['   F. Kode Objek Pajak: Sesuai dengan jenis pajak'],
            ['   G. NPWP: 15 digit angka, tanpa format'],
            ['   H. Nama: Nama penerima penghasilan'],
            ['   I. Dasar Pengenaan Pajak: Angka tanpa pemisah ribuan'],
            ['   J. Pajak Penghasilan: Angka tanpa pemisah ribuan'],
            ['   K. Fasilitas Pajak: Tanpa Fasilitas / DTP / SKB / Lainnya'],
            ['   L. Dilaporkan Dalam SPT: TRUE / FALSE'],
            ['   M. SPT Telah/Sedang Diperiksa: TRUE / FALSE'],
            ['   N. SPT Dalam Penanganan Hukum: TRUE / FALSE'],
            [''],
            ['3. CONTOH DATA:'],
            ['   Lihat sheet "Income Tax Import" untuk contoh data yang benar'],
            [''],
            ['4. CATATAN PENTING:'],
            ['   - Data yang diimpor akan di-link otomatis dengan data karyawan jika NPWP/Nama cocok'],
            ['   - Jika nomor pemotongan sudah ada, data akan di-update'],
            ['   - Pastikan format angka tidak menggunakan pemisah ribuan'],
            ['   - Masa Pajak harus sesuai dengan periode laporan pajak'],
        ];
        
        $row = 1;
        foreach ($instructions as $instruction) {
            $instructionSheet->setCellValue('A' . $row, $instruction[0]);
            $row++;
        }
        
        $instructionSheet->getColumnDimension('A')->setWidth(80);
        
        // Style title on instruction sheet
        $instructionSheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '4472C4'],
            ],
        ]);
        
        // Save file
        $templateDir = storage_path('app/templates');
        if (!file_exists($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filePath = $templateDir . '/income_tax_template.xlsx';
        $writer->save($filePath);
        
        $this->info('Template berhasil dibuat di: ' . $filePath);
        
        return Command::SUCCESS;
    }
}