<?php

namespace App\Livewire\Client\Management;

use App\Models\Client;
use App\Models\TaxReport;
use Carbon\Carbon;
use Livewire\Component;

class ComplianceTab extends Component
{
    public Client $client;
    public $obligations = [];

    public function mount(Client $client)
    {
        $this->client = $client;
        $this->loadObligations();
    }

    public function loadObligations()
    {
        // Get all tax reports for this client
        $taxReports = TaxReport::where('client_id', $this->client->id)
            ->with(['invoices', 'incomeTaxs'])
            ->get();

        $obligations = [];

        foreach ($taxReports as $taxReport) {
            $periode = Carbon::parse($taxReport->month);

            // Check if has invoices (PPN)
            if ($taxReport->invoices()->count() > 0) {
                $obligations[] = $this->createObligation(
                    'SPT Masa PPN',
                    $periode,
                    'ppn',
                    $taxReport
                );
            }

            // Check if has income taxes (PPh 21)
            if ($taxReport->incomeTaxs()->count() > 0) {
                $obligations[] = $this->createObligation(
                    'SPT Masa PPh 21',
                    $periode,
                    'pph21',
                    $taxReport
                );
            }
        }

        // Sort by deadline (nearest first)
        usort($obligations, function ($a, $b) {
            return strtotime($a['deadline']) - strtotime($b['deadline']);
        });

        $this->obligations = $obligations;
    }

    private function createObligation($jenis, $periode, $type, $taxReport)
    {
        // Calculate deadline
        $deadline = $this->calculateDeadline($periode, $type);

        // Calculate priority based on DPP
        $priority = $this->calculatePriority($taxReport);

        // Determine status
        $status = $this->determineStatus($taxReport, $type);

        return [
            'id' => $taxReport->id,
            'jenis' => $jenis,
            'periode' => $periode->format('F Y'),
            'periode_raw' => $periode->format('Y-m'),
            'deadline' => $deadline->format('Y-m-d'),
            'deadline_formatted' => $deadline->format('Y-m-d'),
            'priority' => $priority,
            'status' => $status,
            'type' => $type,
        ];
    }

    private function calculateDeadline($periode, $type)
    {
        $nextMonth = $periode->copy()->addMonth();

        if ($type === 'ppn') {
            // End of next month
            return $nextMonth->endOfMonth();
        } else {
            // 20th of next month
            return $nextMonth->day(20);
        }
    }

    private function calculatePriority($taxReport)
    {
        // Calculate based on Peredaran Bruto (sum of DPP)
        $peredaranBruto = $taxReport->getPeredaranBruto();

        if ($peredaranBruto >= 10000000000) { // >= 10 Miliar
            return 'Tinggi';
        } elseif ($peredaranBruto >= 1000000000) { // >= 1 Miliar
            return 'Sedang';
        } else {
            return 'Rendah';
        }
    }

    private function determineStatus($taxReport, $type)
    {
        if ($type === 'ppn') {
            // Check ppn_report_status
            if ($taxReport->ppn_report_status === 'Sudah Lapor' || $taxReport->ppn_report_status === 'sudah_lapor') {
                return 'Selesai';
            }
        } else {
            // Check pph_report_status
            if ($taxReport->pph_report_status === 'Sudah Lapor' || $taxReport->pph_report_status === 'sudah_lapor') {
                return 'Selesai';
            }
        }

        return 'Pending';
    }

    public function getPriorityColor($priority)
    {
        return match ($priority) {
            'Tinggi' => 'danger',
            'Sedang' => 'warning',
            'Rendah' => 'success',
            default => 'gray'
        };
    }

    public function getPriorityBadgeClass($priority)
    {
        return match ($priority) {
            'Tinggi' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'Sedang' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'Rendah' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
        };
    }

    public function getStatusBadgeClass($status)
    {
        return match ($status) {
            'Selesai' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'Pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
        };
    }

    public function getStatusIcon($status)
    {
        return match ($status) {
            'Selesai' => 'heroicon-o-check-circle',
            'Pending' => 'heroicon-o-clock',
            default => 'heroicon-o-clock'
        };
    }

    public function render()
    {
        return view('livewire.client.management.compliance-tab');
    }
}