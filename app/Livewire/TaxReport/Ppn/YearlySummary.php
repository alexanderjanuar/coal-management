<?php

namespace App\Livewire\TaxReport\Ppn;

use Livewire\Component;
use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;
use Carbon\Carbon;
use App\Exports\TaxReport\PPN\YearlyTaxReportsExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;

class YearlySummary extends Component
{
    public $taxReportId;
    public $clientId;
    public $currentMonth;
    public $currentYear;
    public $clientName;

    public function mount($taxReportId, $clientId)
    {
        $this->taxReportId = $taxReportId;
        $this->clientId = $clientId;
        
        // Get current tax report to determine the month and year
        $currentReport = TaxReport::with('client')->find($taxReportId);
        $this->currentMonth = $currentReport->month;
        $this->currentYear = $currentReport->year ?? $currentReport->created_at->year;
        $this->clientName = $currentReport->client->name;
    }

    public function getTimelineDataProperty()
    {
        // Get all tax reports for this client in the current year with PPN summaries
        $taxReports = TaxReport::where('client_id', $this->clientId)
            ->where('year', $this->currentYear)
            ->with(['taxCalculationSummaries' => function($query) {
                $query->where('tax_type', 'ppn');
            }])
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

        $timelineData = [];
        $totalPpnMasuk = 0;
        $totalPpnKeluar = 0;
        $totalKurangBayar = 0;
        $totalLebihBayar = 0;
        $totalPeredaranBruto = 0;
        $monthsWithReports = 0;
        $sudahLaporCount = 0;
        $belumLaporCount = 0;

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

                // Count report status
                if ($ppnSummary->report_status === 'Sudah Lapor') {
                    $sudahLaporCount++;
                } else {
                    $belumLaporCount++;
                }
            }

            // Format month name with year
            $monthName = $report->month . ' ' . $this->currentYear;

            $timelineData[] = [
                'id' => $report->id,
                'month' => $report->month,
                'month_name' => $monthName,
                'month_short' => substr($report->month, 0, 3) . ' ' . $this->currentYear,
                'ppn_summary' => $ppnSummary,
                'is_current' => $report->month === $this->currentMonth,
            ];
        }

        return [
            'timeline' => $timelineData,
            'year' => $this->currentYear,
            'totals' => [
                'ppn_masuk' => $totalPpnMasuk,
                'ppn_keluar' => $totalPpnKeluar,
                'kurang_bayar' => $totalKurangBayar,
                'lebih_bayar' => $totalLebihBayar,
                'peredaran_bruto' => $totalPeredaranBruto,
                'net_position' => $totalKurangBayar - $totalLebihBayar,
            ],
            'statistics' => [
                'total_months' => count($timelineData),
                'months_with_reports' => $monthsWithReports,
                'sudah_lapor' => $sudahLaporCount,
                'belum_lapor' => $belumLaporCount,
            ],
        ];
    }

    public function exportYearlyReport()
    {
        try {
            // Validate that there are reports to export
            $reportsCount = TaxReport::where('client_id', $this->clientId)
                ->where('year', $this->currentYear)
                ->count();

            if ($reportsCount === 0) {
                Notification::make()
                    ->title('Tidak Ada Data')
                    ->body('Tidak ada laporan pajak untuk tahun ' . $this->currentYear)
                    ->warning()
                    ->send();
                return;
            }

            // Clean client name for filename
            $cleanClientName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->clientName);
            $cleanClientName = preg_replace('/_+/', '_', $cleanClientName);
            $cleanClientName = trim($cleanClientName, '_');

            $filename = 'Rekap_Tahunan_' . $cleanClientName . '_' . $this->currentYear . '.xlsx';

            Notification::make()
                ->title('Export Dimulai')
                ->body('Sedang menyiapkan file Excel...')
                ->info()
                ->send();

            return Excel::download(
                new YearlyTaxReportsExport($this->clientId, $this->currentYear),
                $filename
            );

        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();

            \Log::error('Yearly tax report export failed', [
                'client_id' => $this->clientId,
                'year' => $this->currentYear,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function exportYearlyReportPdf()
    {
        try {
            // Validate that there are reports to export
            $reportsCount = TaxReport::where('client_id', $this->clientId)
                ->where('year', $this->currentYear)
                ->count();

            if ($reportsCount === 0) {
                Notification::make()
                    ->title('Tidak Ada Data')
                    ->body('Tidak ada laporan pajak untuk tahun ' . $this->currentYear)
                    ->warning()
                    ->send();
                return;
            }

            Notification::make()
                ->title('Export Dimulai')
                ->body('Sedang menyiapkan file PDF...')
                ->info()
                ->send();

            $exporter = new \App\Exports\TaxReport\PPN\YearlyTaxReportsPdfExport($this->clientId, $this->currentYear);
            return $exporter->download();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Export Gagal')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();

            \Log::error('Yearly tax report PDF export failed', [
                'client_id' => $this->clientId,
                'year' => $this->currentYear,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tax-report.ppn.yearly-summary', [
            'timelineData' => $this->timelineData,
        ]);
    }
}