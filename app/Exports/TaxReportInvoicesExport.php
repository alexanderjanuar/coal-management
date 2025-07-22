<?php
// app/Exports/TaxReportInvoicesExport.php

namespace App\Exports;

use App\Models\TaxReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class TaxReportInvoicesExport implements FromArray, WithStyles, WithColumnWidths, WithTitle
{
    protected $taxReport;
    protected $selectedInvoiceIds;
    protected $isSelectionMode;
    protected $fakturKeluaranData;
    protected $fakturMasukanData;

    public function __construct(TaxReport $taxReport, ?array $selectedInvoiceIds = null)
    {
        $this->taxReport = $taxReport;
        $this->selectedInvoiceIds = $selectedInvoiceIds;
        $this->isSelectionMode = !empty($selectedInvoiceIds);
    }

    /**
     * Filter invoices to get only latest versions (excluding revised originals)
     */
    private function getLatestVersionInvoices($baseQuery)
    {
        // Get all invoices
        $allInvoices = $baseQuery->get();
        
        // Group by original invoice to get latest versions only
        $latestVersions = $allInvoices->groupBy(function ($invoice) {
            return $invoice->is_revision ? $invoice->original_invoice_id : $invoice->id;
        })->map(function ($group) {
            // Return the latest version (highest revision_number or original if no revisions)
            return $group->sortByDesc('revision_number')->first();
        });
        
        return $latestVersions->values();
    }

    /**
     * Get display values for invoice (0 if revised, actual values if latest)
     */
    private function getDisplayValues($invoice)
    {
        // Check if this invoice has been revised
        $hasRevisions = $invoice->revisions()->exists();
        
        if ($hasRevisions && !$invoice->is_revision) {
            // Original invoice with revisions - show 0 values
            return [
                'dpp_nilai_lainnya' => 0,
                'dpp' => 0,
                'ppn' => 0,
                'is_revised' => true
            ];
        } else {
            // Latest revision or original without revisions - show actual values
            return [
                'dpp_nilai_lainnya' => $invoice->dpp_nilai_lainnya ?? 0,
                'dpp' => $invoice->dpp,
                'ppn' => $invoice->ppn,
                'is_revised' => false
            ];
        }
    }

    public function array(): array
    {
        $data = [];

        // Title row - FAKTUR [CLIENT NAME] [MONTH] [YEAR] - start at column B
        $clientName = strtoupper($this->taxReport->client->name ?? 'UNKNOWN CLIENT');
        $monthYear = strtoupper($this->getIndonesianMonth($this->taxReport->month)) . ' ' . date('Y');
        
        // Add selection indicator to title if in selection mode
        $titlePrefix = $this->isSelectionMode ? 'REKAP FAKTUR TERPILIH ' : 'REKAP FAKTUR ';
        $data[] = ['', '', '', '', '', '', '', '']; // Empty row 1
        $data[] = ['', '', '', '', '', '', '', '']; // Empty row 2
        $data[] = ['', $titlePrefix . $clientName . ' - ' . $monthYear, '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '']; // Empty row

        // Get base query
        $baseQuery = $this->taxReport->invoices();
        
        // Apply selection filter if in selection mode
        if ($this->isSelectionMode) {
            $baseQuery->whereIn('id', $this->selectedInvoiceIds);
        }

        // FAKTUR KELUARAN section - start at column B
        $data[] = ['', 'FAKTUR KELUARAN', '', '', '', '', '', ''];

        // Headers - start at column B (added Nomor Seri Faktur column)
        $data[] = ['', 'No', 'Nama Penjual', 'Nomor Seri Faktur', 'Tanggal', 'DPP Nilai Lainnya', 'DPP', 'PPN'];

        // Get Faktur Keluaran data - use latest versions only for calculations
        $fakturKeluaranQuery = (clone $baseQuery)->where('type', 'Faktur Keluaran');
        $fakturKeluaranLatest = $this->getLatestVersionInvoices($fakturKeluaranQuery);
        
        // But for display, we get ALL invoices (including revised originals) for proper listing
        $fakturKeluaranAll = $fakturKeluaranQuery->orderBy('invoice_date')->get();

        $totalDppNilaiLainnyaKeluaran = 0;
        $totalDppKeluaran = 0;
        $totalPpnKeluaran = 0;

        // Data rows - start at column B - display ALL but calculate only latest versions
        foreach ($fakturKeluaranAll as $index => $invoice) {
            $displayValues = $this->getDisplayValues($invoice);
            
            $data[] = [
                '',
                $index + 1,
                $invoice->company_name,
                $invoice->invoice_number, // Keep original invoice number without any modifications
                date('d/m/Y', strtotime($invoice->invoice_date)),
                $displayValues['dpp_nilai_lainnya'] > 0 ? 'Rp ' . number_format($displayValues['dpp_nilai_lainnya'], 0, ',', ',') : 'Rp 0',
                $displayValues['dpp'] > 0 ? 'Rp ' . number_format($displayValues['dpp'], 0, ',', ',') : 'Rp 0',
                $displayValues['ppn'] > 0 ? 'Rp ' . number_format($displayValues['ppn'], 0, ',', ',') : 'Rp 0',
            ];
        }

        // Calculate totals from latest versions only
        foreach ($fakturKeluaranLatest as $invoice) {
            $totalDppNilaiLainnyaKeluaran += $invoice->dpp_nilai_lainnya ?? 0;
            $totalDppKeluaran += $invoice->dpp;
            $totalPpnKeluaran += $invoice->ppn;
        }

        // JUMLAH row for Faktur Keluaran - start at column B
        $data[] = [
            '', 
            '', 
            'JUMLAH', 
            '',
            '', 
            'Rp ' . number_format($totalDppNilaiLainnyaKeluaran, 0, ',', ','),
            'Rp ' . number_format($totalDppKeluaran, 0, ',', ','), 
            'Rp ' . number_format($totalPpnKeluaran, 0, ',', ',')
        ];

        // Add 2 empty rows for spacing
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];

        // FAKTUR MASUKAN section - start at column B
        $data[] = ['', 'FAKTUR MASUKAN', '', '', '', '', '', ''];

        // Headers - start at column B
        $data[] = ['', 'No', 'Nama Penjual', 'Nomor Seri Faktur', 'Tanggal', 'DPP Nilai Lainnya', 'DPP', 'PPN'];

        // Get Faktur Masukan data - use latest versions only for calculations
        $fakturMasukanQuery = (clone $baseQuery)->where('type', 'Faktur Masuk');
        $fakturMasukanLatest = $this->getLatestVersionInvoices($fakturMasukanQuery);
        
        // But for display, we get ALL invoices (including revised originals) for proper listing
        $fakturMasukanAll = $fakturMasukanQuery->orderBy('invoice_date')->get();

        $totalDppNilaiLainnyaMasukan = 0;
        $totalDppMasukan = 0;
        $totalPpnMasukan = 0;

        // Data rows - start at column B - display ALL but calculate only latest versions
        foreach ($fakturMasukanAll as $index => $invoice) {
            $displayValues = $this->getDisplayValues($invoice);
            
            $data[] = [
                '',
                $index + 1,
                $invoice->company_name,
                $invoice->invoice_number, // Keep original invoice number without any modifications
                date('d/m/Y', strtotime($invoice->invoice_date)),
                $displayValues['dpp_nilai_lainnya'] > 0 ? 'Rp ' . number_format($displayValues['dpp_nilai_lainnya'], 0, ',', ',') : 'Rp 0',
                $displayValues['dpp'] > 0 ? 'Rp ' . number_format($displayValues['dpp'], 0, ',', ',') : 'Rp 0',
                $displayValues['ppn'] > 0 ? 'Rp ' . number_format($displayValues['ppn'], 0, ',', ',') : 'Rp 0',
            ];
        }

        // Calculate totals from latest versions only
        foreach ($fakturMasukanLatest as $invoice) {
            $totalDppNilaiLainnyaMasukan += $invoice->dpp_nilai_lainnya ?? 0;
            $totalDppMasukan += $invoice->dpp;
            $totalPpnMasukan += $invoice->ppn;
        }

        // JUMLAH row for Faktur Masukan - start at column B
        $data[] = [
            '', 
            '', 
            'JUMLAH', 
            '',
            '', 
            'Rp ' . number_format($totalDppNilaiLainnyaMasukan, 0, ',', ','),
            'Rp ' . number_format($totalDppMasukan, 0, ',', ','), 
            'Rp ' . number_format($totalPpnMasukan, 0, ',', ',')
        ];

        // Add 2 empty rows for spacing
        $data[] = ['', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', ''];

        // REKAP KURANG ATAU LEBIH BAYAR PAJAK section
        $data[] = ['', 'REKAP KURANG ATAU LEBIH BAYAR PAJAK', '', '', '', '', '', ''];

        // Summary calculations - use calculated totals from latest versions
        $kurangLebihBayar = $totalPpnKeluaran - $totalPpnMasukan;
        
        // For selection mode, don't include compensation (only for full reports)
        $ppnDikompensasi = $this->isSelectionMode ? 0 : ($this->taxReport->ppn_dikompensasi_dari_masa_sebelumnya ?? 0);
        $finalAmount = $kurangLebihBayar - $ppnDikompensasi;

        $data[] = ['', 'TOTAL PPN FAKTUR KELUARAN', '', '', '', '', '', 'Rp ' . number_format($totalPpnKeluaran, 0, ',', ',')];
        $data[] = ['', 'TOTAL PPN FAKTUR MASUKAN', '', '', '', '', '', 'Rp ' . number_format($totalPpnMasukan, 0, ',', ',')];
        
        // Only show compensation for full reports
        if (!$this->isSelectionMode) {
            $data[] = ['', 'PPN DIKOMPENSASIKAN DARI MASA SEBELUMNYA', '', '', '', '', '', 'Rp ' . number_format($ppnDikompensasi, 0, ',', ',')];
        }
        
        $data[] = ['', 'TOTAL KURANG/ LEBIH BAYAR PAJAK', '', '', '', '', '', 'Rp ' . number_format($finalAmount, 0, ',', ',')];

        // Determine status and add status row
        if ($finalAmount > 0) {
            $status = 'KURANG BAYAR';
        } elseif ($finalAmount < 0) {
            $status = 'LEBIH BAYAR';
        } else {
            $status = 'NIHIL';
        }

        // Add status row
        $data[] = ['', $status, '', '', '', '', '', ''];

        // Add selection info if in selection mode
        if ($this->isSelectionMode) {
            $data[] = ['', '', '', '', '', '', '', ''];
            $totalSelected = count($this->selectedInvoiceIds);
            $totalAvailable = $this->taxReport->invoices()->count();
            $data[] = ['', "DIPILIH: {$totalSelected} dari {$totalAvailable} faktur", '', '', '', '', '', ''];
        }

        // Store data for merging later (use ALL invoices for proper display)
        $this->fakturKeluaranData = $fakturKeluaranAll;
        $this->fakturMasukanData = $fakturMasukanAll;

        return $data;
    }

    /**
     * Apply company name merging for repeated company names
     */
    private function applyCompanyNameMerging(Worksheet $sheet, int $startRow, Collection $invoices, string $column): void
    {
        if ($invoices->isEmpty()) {
            return;
        }

        $currentRow = $startRow;
        $groupedInvoices = $invoices->groupBy('company_name');

        foreach ($groupedInvoices as $companyName => $companyInvoices) {
            $groupSize = $companyInvoices->count();
            
            if ($groupSize > 1) {
                // Merge cells for this company
                $endRow = $currentRow + $groupSize - 1;
                $sheet->mergeCells("{$column}{$currentRow}:{$column}{$endRow}");
                
                // Center the merged cell content vertically
                $sheet->getStyle("{$column}{$currentRow}:{$column}{$endRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
            }
            
            $currentRow += $groupSize;
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Get the filtered data for styling calculations
        $baseQuery = $this->taxReport->invoices();
        
        if ($this->isSelectionMode) {
            $baseQuery->whereIn('id', $this->selectedInvoiceIds);
        }

        $fakturKeluaran = (clone $baseQuery)->where('type', 'Faktur Keluaran')->get();
        $fakturMasukan = (clone $baseQuery)->where('type', 'Faktur Masuk')->get();
        
        $keluaranDataRowCount = $fakturKeluaran->count();
        $masukanDataRowCount = $fakturMasukan->count();

        // Calculate row positions dynamically for FAKTUR KELUARAN
        $titleRow = 3;
        $sectionHeaderRow = 5;
        $headerRow = 6;
        $dataStartRow = 7;
        $dataEndRow = $dataStartRow + $keluaranDataRowCount - 1;
        $jumlahRow = $dataEndRow + 1;

        // Title styling - merge across columns B-H
        $sheet->mergeCells('B3:H3');
        $sheet->getStyle('B3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // FAKTUR KELUARAN section header - MERGE B3:H3
        $sheet->mergeCells("B{$sectionHeaderRow}:H{$sectionHeaderRow}");
        $sheet->getStyle("B{$sectionHeaderRow}:H{$sectionHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Headers row styling
        $sheet->getStyle("B{$headerRow}:H{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8E8E8']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Data rows styling (only actual data rows)
        if ($keluaranDataRowCount > 0) {
            $sheet->getStyle("B{$dataStartRow}:H{$dataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Apply special styling for revised invoices (gray out)
            for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                $invoiceIndex = $row - $dataStartRow;
                if (isset($fakturKeluaran[$invoiceIndex])) {
                    $invoice = $fakturKeluaran[$invoiceIndex];
                    $displayValues = $this->getDisplayValues($invoice);
                    
                    if ($displayValues['is_revised']) {
                        // Light gray background for revised original invoices
                        $sheet->getStyle("B{$row}:H{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'F5F5F5']
                            ],
                            'font' => [
                                'color' => ['rgb' => '999999']
                            ]
                        ]);
                        
                        // Keep company name (column C) with normal background and text
                        $sheet->getStyle("C{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'font' => [
                                'color' => ['rgb' => '000000']
                            ]
                        ]);
                    }
                }
            }

            // Align text columns to left (Nama Penjual, Nomor Seri Faktur, and Tanggal)
            $sheet->getStyle("C{$dataStartRow}:E{$dataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            // Align numbers to the right (DPP Nilai Lainnya, DPP, PPN)
            $sheet->getStyle("F{$dataStartRow}:H{$dataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            // Center the No column
            $sheet->getStyle("B{$dataStartRow}:B{$dataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // JUMLAH row styling for FAKTUR KELUARAN
        $sheet->getStyle("B{$jumlahRow}:H{$jumlahRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Align JUMLAH amounts to the right
        $sheet->getStyle("F{$jumlahRow}:H{$jumlahRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);

        // Calculate FAKTUR MASUKAN section positions
        $masukanSectionHeaderRow = $jumlahRow + 3;
        $masukanHeaderRow = $masukanSectionHeaderRow + 1;
        $masukanDataStartRow = $masukanHeaderRow + 1;
        $masukanDataEndRow = $masukanDataStartRow + $masukanDataRowCount - 1;
        $masukanJumlahRow = $masukanDataEndRow + 1;

        // FAKTUR MASUKAN section header
        $sheet->mergeCells("B{$masukanSectionHeaderRow}:H{$masukanSectionHeaderRow}");
        $sheet->getStyle("B{$masukanSectionHeaderRow}:H{$masukanSectionHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // FAKTUR MASUKAN headers row styling
        $sheet->getStyle("B{$masukanHeaderRow}:H{$masukanHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'E8E8E8']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // FAKTUR MASUKAN data rows styling
        if ($masukanDataRowCount > 0) {
            $sheet->getStyle("B{$masukanDataStartRow}:H{$masukanDataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Apply special styling for revised invoices (gray out)
            for ($row = $masukanDataStartRow; $row <= $masukanDataEndRow; $row++) {
                $invoiceIndex = $row - $masukanDataStartRow;
                if (isset($fakturMasukan[$invoiceIndex])) {
                    $invoice = $fakturMasukan[$invoiceIndex];
                    $displayValues = $this->getDisplayValues($invoice);
                    
                    if ($displayValues['is_revised']) {
                        // Light gray background for revised original invoices
                        $sheet->getStyle("B{$row}:H{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'F5F5F5']
                            ],
                            'font' => [
                                'color' => ['rgb' => '999999']
                            ]
                        ]);
                        
                        // Keep company name (column C) with normal background and text
                        $sheet->getStyle("C{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'font' => [
                                'color' => ['rgb' => '000000']
                            ]
                        ]);
                    }
                }
            }

            // Align text columns to left
            $sheet->getStyle("C{$masukanDataStartRow}:E{$masukanDataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            // Align numbers to the right
            $sheet->getStyle("F{$masukanDataStartRow}:H{$masukanDataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            // Center the No column
            $sheet->getStyle("B{$masukanDataStartRow}:B{$masukanDataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
        }

        // FAKTUR MASUKAN JUMLAH row styling
        $sheet->getStyle("B{$masukanJumlahRow}:H{$masukanJumlahRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Align FAKTUR MASUKAN JUMLAH amounts to the right
        $sheet->getStyle("F{$masukanJumlahRow}:H{$masukanJumlahRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);

        // Calculate REKAP section positions
        $rekapSectionHeaderRow = $masukanJumlahRow + 3;
        $rekapDataStartRow = $rekapSectionHeaderRow + 1;
        $rekapDataRowCount = $this->isSelectionMode ? 3 : 4; // 3 rows for selection (no compensation), 4 for full report
        $rekapDataEndRow = $rekapDataStartRow + $rekapDataRowCount - 1;
        $statusRow = $rekapDataEndRow + 1;

        // REKAP KURANG ATAU LEBIH BAYAR PAJAK section header
        $sheet->mergeCells("B{$rekapSectionHeaderRow}:H{$rekapSectionHeaderRow}");
        $sheet->getStyle("B{$rekapSectionHeaderRow}:H{$rekapSectionHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // REKAP data rows styling with merged cells B to G
        $sheet->getStyle("B{$rekapDataStartRow}:H{$rekapDataEndRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Merge and align REKAP description texts (B to G) and align right
        for ($row = $rekapDataStartRow; $row <= $rekapDataEndRow; $row++) {
            $sheet->mergeCells("B{$row}:G{$row}");
            $sheet->getStyle("B{$row}:G{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER]
            ]);
        }

        // Align amounts to the right - column H only
        $sheet->getStyle("H{$rekapDataStartRow}:H{$rekapDataEndRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
        ]);

        // Status row styling
        $sheet->mergeCells("B{$statusRow}:H{$statusRow}");

        // Calculate final amount for status determination (using latest versions)
        $fakturKeluaranLatest = $this->getLatestVersionInvoices((clone $baseQuery)->where('type', 'Faktur Keluaran'));
        $fakturMasukanLatest = $this->getLatestVersionInvoices((clone $baseQuery)->where('type', 'Faktur Masuk'));
        
        $totalPpnKeluaran = $fakturKeluaranLatest->sum('ppn');
        $totalPpnMasukan = $fakturMasukanLatest->sum('ppn');
        $ppnDikompensasi = $this->isSelectionMode ? 0 : ($this->taxReport->ppn_dikompensasi_dari_masa_sebelumnya ?? 0);
        $finalAmount = ($totalPpnKeluaran - $totalPpnMasukan) - $ppnDikompensasi;

        // Determine text color based on status
        if ($finalAmount > 0) {
            $textColor = 'FF0000'; // Red for KURANG BAYAR
        } elseif ($finalAmount < 0) {
            $textColor = '008000'; // Green for LEBIH BAYAR
        } else {
            $textColor = 'FF8C00'; // Orange for NIHIL
        }

        $sheet->getStyle("B{$statusRow}:H{$statusRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => $textColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ]);

        // Selection info styling (if in selection mode)
        if ($this->isSelectionMode) {
            $selectionInfoRow = $statusRow + 2;
            $sheet->mergeCells("B{$selectionInfoRow}:H{$selectionInfoRow}");
            $sheet->getStyle("B{$selectionInfoRow}:H{$selectionInfoRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Set row heights for better appearance
        $sheet->getRowDimension($sectionHeaderRow)->setRowHeight(25);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);
        $sheet->getRowDimension($jumlahRow)->setRowHeight(18);
        $sheet->getRowDimension($masukanSectionHeaderRow)->setRowHeight(25);
        $sheet->getRowDimension($masukanHeaderRow)->setRowHeight(20);
        $sheet->getRowDimension($masukanJumlahRow)->setRowHeight(18);
        $sheet->getRowDimension($rekapSectionHeaderRow)->setRowHeight(25);
        $sheet->getRowDimension($statusRow)->setRowHeight(30);

        // Apply company name merging for Faktur Keluaran
        if (isset($this->fakturKeluaranData)) {
            $this->applyCompanyNameMerging($sheet, $dataStartRow, $this->fakturKeluaranData, 'C');
        }

        // Apply company name merging for Faktur Masukan
        if (isset($this->fakturMasukanData)) {
            $this->applyCompanyNameMerging($sheet, $masukanDataStartRow, $this->fakturMasukanData, 'C');
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 3,   // Empty column
            'B' => 8,   // No
            'C' => 35,  // Nama Penjual (wider)
            'D' => 25,  // Nomor Seri Faktur (back to original width)
            'E' => 15,  // Tanggal (wider)
            'F' => 20,  // DPP Nilai Lainnya (wider)
            'G' => 20,  // DPP (wider)
            'H' => 20,  // PPN (wider)
        ];
    }

    public function title(): string
    {
        $clientName = $this->taxReport->client->name ?? 'Unknown_Client';
        $monthYear = $this->getIndonesianMonth($this->taxReport->month) . '_' . date('Y');

        // Clean client name for filename
        $cleanClientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $clientName);
        $cleanClientName = preg_replace('/_+/', '_', $cleanClientName);
        $cleanClientName = trim($cleanClientName, '_');

        // Add selection indicator to filename
        $prefix = $this->isSelectionMode ? 'Terpilih_' : '';
        
        return 'Rekap_Faktur_' . $prefix . $cleanClientName . '_' . $monthYear;
    }

    private function getIndonesianMonth($month): string
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

        if (preg_match('/\d{4}-(\d{1,2})/', $month, $matches)) {
            $cleanMonth = $matches[1];
        }

        return $monthNames[$cleanMonth] ?? 'Unknown';
    }
}