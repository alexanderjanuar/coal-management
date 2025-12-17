<?php

namespace App\Livewire\TaxReport\Components;

use Livewire\Component;
use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;
use Carbon\Carbon;

class YearlySummary extends Component
{
    public $taxReportId;
    public $clientId;
    public $currentMonth;

    public function mount($taxReportId, $clientId)
    {
        $this->taxReportId = $taxReportId;
        $this->clientId = $clientId;
        
        // Get current tax report to determine the month
        $currentReport = TaxReport::find($taxReportId);
        $this->currentMonth = $currentReport->month;
    }

    public function getTimelineDataProperty()
    {
        // Get all tax reports for this client with PPN summaries
        $taxReports = TaxReport::where('client_id', $this->clientId)
            ->with(['ppnSummary'])
            ->get()
            ->sortBy(function ($report) {
                // Parse the month string and convert to timestamp for proper sorting
                // Using sortBy (ascending) instead of sortByDesc for chronological order
                try {
                    return Carbon::parse($report->month)->timestamp;
                } catch (\Exception $e) {
                    // If parsing fails, try different formats
                    try {
                        return Carbon::createFromFormat('Y-m', $report->month)->timestamp;
                    } catch (\Exception $e2) {
                        return 0;
                    }
                }
            })
            ->values(); // Reset collection keys after sorting

        $timelineData = [];
        $totalPpnMasuk = 0;
        $totalPpnKeluar = 0;
        $totalKurangBayar = 0;
        $totalLebihBayar = 0;

        foreach ($taxReports as $report) {
            $ppnSummary = $report->ppnSummary;
            
            if ($ppnSummary) {
                $totalPpnMasuk += $ppnSummary->pajak_masuk;
                $totalPpnKeluar += $ppnSummary->pajak_keluar;
                
                if ($ppnSummary->status_final === 'Kurang Bayar') {
                    $totalKurangBayar += abs($ppnSummary->saldo_final);
                } elseif ($ppnSummary->status_final === 'Lebih Bayar') {
                    $totalLebihBayar += abs($ppnSummary->saldo_final);
                }
            }

            // Parse month for display
            try {
                $monthDate = Carbon::parse($report->month);
            } catch (\Exception $e) {
                try {
                    $monthDate = Carbon::createFromFormat('Y-m', $report->month);
                } catch (\Exception $e2) {
                    $monthDate = Carbon::now();
                }
            }

            $timelineData[] = [
                'id' => $report->id,
                'month' => $report->month,
                'month_name' => $monthDate->format('F Y'),
                'month_short' => $monthDate->format('M Y'),
                'ppn_summary' => $ppnSummary,
                'is_current' => $report->month === $this->currentMonth,
            ];
        }

        return [
            'timeline' => $timelineData,
            'totals' => [
                'ppn_masuk' => $totalPpnMasuk,
                'ppn_keluar' => $totalPpnKeluar,
                'kurang_bayar' => $totalKurangBayar,
                'lebih_bayar' => $totalLebihBayar,
                'net_position' => $totalKurangBayar - $totalLebihBayar,
            ],
            'total_months' => count($timelineData),
        ];
    }

    public function render()
    {
        return view('livewire.tax-report.components.yearly-summary', [
            'timelineData' => $this->timelineData,
        ]);
    }
}