<?php

namespace App\Exports\TaxReport\PPN;

use App\Models\TaxReport;
use Barryvdh\DomPDF\Facade\Pdf;

class YearlyTaxReportsPdfExport
{
    protected $clientId;
    protected $year;

    public function __construct($clientId, $year)
    {
        $this->clientId = $clientId;
        $this->year = $year;
    }

    /**
     * Generate the PDF and return Http response
     */
    public function download()
    {
        $data = $this->prepareData();
        
        $pdf = Pdf::loadView('exports.yearly-tax-reports-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'dpi' => 96,
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        $filename = $this->generateFilename();
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $filename);
    }

    /**
     * Generate filename for download
     */
    private function generateFilename(): string
    {
        $taxReport = TaxReport::where('client_id', $this->clientId)
            ->where('year', $this->year)
            ->with('client')
            ->first();
            
        $clientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $taxReport->client->name ?? 'Unknown');
        $clientName = preg_replace('/_+/', '_', $clientName);
        $clientName = trim($clientName, '_');
        
        return 'Rekap_Tahunan_' . $clientName . '_' . $this->year . '.pdf';
    }

    /**
     * Prepare data for the PDF view
     */
    private function prepareData(): array
    {
        // Get all tax reports for this client in the year
        $taxReports = TaxReport::where('client_id', $this->clientId)
            ->where('year', $this->year)
            ->with(['taxCalculationSummaries' => function($query) {
                $query->where('tax_type', 'ppn');
            }, 'client'])
            ->get();

        // Define month order for sorting
        $monthOrder = [
            'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
            'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
            'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12
        ];

        // Sort by month order
        $taxReports = $taxReports->sortBy(function ($report) use ($monthOrder) {
            return $monthOrder[$report->month] ?? 0;
        })->values();

        $monthlyData = [];
        $totalPpnMasuk = 0;
        $totalPpnKeluar = 0;
        $totalKurangBayar = 0;
        $totalLebihBayar = 0;
        $totalPeredaranBruto = 0;
        $monthsWithReports = 0;

        foreach ($taxReports as $report) {
            $ppnSummary = $report->taxCalculationSummaries->where('tax_type', 'ppn')->first();
            
            if ($ppnSummary) {
                $monthsWithReports++;
                $totalPpnMasuk += $ppnSummary->pajak_masuk ?? 0;
                $totalPpnKeluar += $ppnSummary->pajak_keluar ?? 0;
                $totalPeredaranBruto += $ppnSummary->peredaran_bruto ?? 0;
                
                if ($ppnSummary->status_final === 'Kurang Bayar') {
                    $totalKurangBayar += abs($ppnSummary->saldo_final);
                } elseif ($ppnSummary->status_final === 'Lebih Bayar') {
                    $totalLebihBayar += abs($ppnSummary->saldo_final);
                }

                $monthlyData[] = [
                    'month' => $this->getIndonesianMonth($report->month),
                    'ppn_masuk' => $ppnSummary->pajak_masuk ?? 0,
                    'ppn_keluar' => $ppnSummary->pajak_keluar ?? 0,
                    'peredaran_bruto' => $ppnSummary->peredaran_bruto ?? 0,
                    'saldo_final' => $ppnSummary->saldo_final ?? 0,
                    'status_final' => $ppnSummary->status_final ?? 'Nihil',
                    'kompensasi_diterima' => $ppnSummary->kompensasi_diterima ?? 0,
                    'kompensasi_terpakai' => $ppnSummary->kompensasi_terpakai ?? 0,
                    'report_status' => $ppnSummary->report_status ?? 'Belum Lapor',
                ];
            }
        }

        $clientName = $taxReports->first()->client->name ?? 'UNKNOWN CLIENT';

        return [
            'clientName' => strtoupper($clientName),
            'year' => $this->year,
            'monthlyData' => $monthlyData,
            'totals' => [
                'ppn_masuk' => $totalPpnMasuk,
                'ppn_keluar' => $totalPpnKeluar,
                'kurang_bayar' => $totalKurangBayar,
                'lebih_bayar' => $totalLebihBayar,
                'peredaran_bruto' => $totalPeredaranBruto,
                'net_position' => $totalKurangBayar - $totalLebihBayar,
            ],
            'statistics' => [
                'months_with_reports' => $monthsWithReports,
                'total_months' => 12,
            ],
        ];
    }

    /**
     * Get Indonesian month name
     */
    private function getIndonesianMonth($month): string
    {
        $monthNames = [
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
        ];

        return $monthNames[$month] ?? $month;
    }
}
