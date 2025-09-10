<?php

namespace App\Livewire\TaxReport;

use App\Models\TaxReport;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TopUnreportedClients extends Component
{
    public $topClients = [];
    public $loading = true;

    public function mount()
    {
        $this->loadTopUnreportedClients();
    }

    public function loadTopUnreportedClients()
    {
        $this->loading = true;
        
        // Get clients with highest peredaran bruto from unreported tax reports
        $this->topClients = TaxReport::select([
            'tax_reports.id',
            'tax_reports.month', 
            'tax_reports.ppn_report_status',
            'tax_reports.pph_report_status',
            'tax_reports.bupot_report_status',
            'clients.name as client_name',
            'clients.id as client_id',
            DB::raw('SUM(CASE WHEN invoices.type = "Faktur Keluaran" THEN invoices.dpp ELSE 0 END) as total_peredaran_bruto'),
            DB::raw('COUNT(invoices.id) as total_invoices')
        ])
        ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
        ->join('invoices', 'tax_reports.id', '=', 'invoices.tax_report_id')
        ->where('invoices.is_revision', false) // Exclude revisions
        ->where(function($query) {
            $query->where('tax_reports.ppn_report_status', 'Belum Lapor')
                  ->orWhere('tax_reports.pph_report_status', 'Belum Lapor')
                  ->orWhere('tax_reports.bupot_report_status', 'Belum Lapor');
        })
        ->groupBy([
            'tax_reports.id',
            'tax_reports.month',
            'tax_reports.ppn_report_status', 
            'tax_reports.pph_report_status',
            'tax_reports.bupot_report_status',
            'clients.name',
            'clients.id'
        ])
        ->orderByDesc('total_peredaran_bruto')
        ->limit(5)
        ->get();

        $this->loading = false;
    }

    public function getUnreportedCount($client)
    {
        $count = 0;
        if ($client->ppn_report_status === 'Belum Lapor') $count++;
        if ($client->pph_report_status === 'Belum Lapor') $count++;
        if ($client->bupot_report_status === 'Belum Lapor') $count++;
        return $count;
    }

    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatCurrencyText($amount)
    {
        // Convert number to Indonesian text
        $terbilang = $this->terbilang($amount);
        return ucfirst($terbilang) . ' rupiah';
    }

    public function formatCurrencyTextShort($amount)
    {
        // Convert number to Indonesian text and limit to 30 characters
        $terbilang = $this->terbilang($amount);
        $fullText = ucfirst($terbilang) . ' rupiah';
        
        if (strlen($fullText) > 30) {
            return substr($fullText, 0, 27) . '...';
        }
        
        return $fullText;
    }

    private function terbilang($angka)
    {
        $angka = abs($angka);
        $angka = floor($angka);
        
        $huruf = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
            'sepuluh', 'sebelas'
        ];
        
        if ($angka < 12) {
            return $huruf[$angka];
        } elseif ($angka < 20) {
            return $huruf[$angka - 10] . ' belas';
        } elseif ($angka < 100) {
            return $huruf[floor($angka / 10)] . ' puluh' . ($angka % 10 != 0 ? ' ' . $huruf[$angka % 10] : '');
        } elseif ($angka < 200) {
            return 'seratus' . ($angka % 100 != 0 ? ' ' . $this->terbilang($angka % 100) : '');
        } elseif ($angka < 1000) {
            return $huruf[floor($angka / 100)] . ' ratus' . ($angka % 100 != 0 ? ' ' . $this->terbilang($angka % 100) : '');
        } elseif ($angka < 2000) {
            return 'seribu' . ($angka % 1000 != 0 ? ' ' . $this->terbilang($angka % 1000) : '');
        } elseif ($angka < 1000000) {
            return $this->terbilang(floor($angka / 1000)) . ' ribu' . ($angka % 1000 != 0 ? ' ' . $this->terbilang($angka % 1000) : '');
        } elseif ($angka < 1000000000) {
            return $this->terbilang(floor($angka / 1000000)) . ' juta' . ($angka % 1000000 != 0 ? ' ' . $this->terbilang($angka % 1000000) : '');
        } elseif ($angka < 1000000000000) {
            return $this->terbilang(floor($angka / 1000000000)) . ' miliar' . ($angka % 1000000000 != 0 ? ' ' . $this->terbilang($angka % 1000000000) : '');
        } elseif ($angka < 1000000000000000) {
            return $this->terbilang(floor($angka / 1000000000000)) . ' triliun' . ($angka % 1000000000000 != 0 ? ' ' . $this->terbilang($angka % 1000000000000) : '');
        }
        
        return 'angka terlalu besar';
    }

    public function getTaxReportUrl($taxReportId)
    {
        return route('filament.admin.laporan-pajak.resources.tax-reports.edit', ['record' => $taxReportId]);
    }

    public function getClientUrl($clientId)
    {
        return route('filament.admin.resources.clients.index', ['record' => $clientId]);
    }

    public function render()
    {
        return view('livewire.tax-report.top-unreported-clients');
    }
}