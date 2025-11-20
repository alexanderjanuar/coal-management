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
            ->orderByRaw('STR_TO_DATE(month, "%Y-%m") DESC')
            ->get();

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

            $timelineData[] = [
                'id' => $report->id,
                'month' => $report->month,
                'month_name' => Carbon::parse($report->month)->format('F Y'),
                'month_short' => Carbon::parse($report->month)->format('M Y'),
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