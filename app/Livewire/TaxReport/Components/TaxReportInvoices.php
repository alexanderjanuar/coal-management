<?php

namespace App\Livewire\TaxReport\Components;

use App\Models\TaxReport;
use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class TaxReportInvoices extends Component
{
    public $taxReportId;
    public $activeTab = 'daftar-pajak';
    
    // Summary data properties
    public $ppnMasuk = 0;
    public $ppnKeluar = 0;
    public $ppnKurangBayar = 0;
    public $ppnLebihBayar = 0;
    public $fakturMasukCount = 0;
    public $fakturKeluarCount = 0;
    
    // Peredaran Bruto properties (separated)
    public $peredaranBruto = 0;
    public $totalDpp = 0;
    public $totalDppNilaiLainnya = 0;
    
    // Kompensasi properties
    public $kompensasiDiterima = 0;
    public $kompensasiTersedia = 0;
    public $kompensasiTerpakai = 0;
    public $saldoFinal = 0;
    public $statusFinal = 'Nihil';
    
    // Excluded counts
    public $fakturKeluarExcludedCount = 0;
    public $fakturMasukExcludedCount = 0;

    public function mount($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        $this->loadSummary();
    }

    public function loadSummary()
    {
        $taxReport = TaxReport::with(['invoices', 'approvedCompensationsReceived', 'approvedCompensationsGiven'])
            ->find($this->taxReportId);
        
        if ($taxReport) {
            // Get filtered invoices
            $filteredFakturKeluar = $this->getFilteredFakturKeluar($taxReport);
            $filteredFakturMasuk = $this->getFilteredFakturMasuk($taxReport);
            
            // Calculate PPN
            $this->ppnKeluar = $filteredFakturKeluar->sum('ppn');
            $this->ppnMasuk = $filteredFakturMasuk->sum('ppn');
            
            // Calculate Peredaran Bruto (separated DPP and DPP Nilai Lainnya)
            $this->totalDpp = $filteredFakturKeluar->sum('dpp');
            $this->totalDppNilaiLainnya = $filteredFakturKeluar->sum('dpp_nilai_lainnya');
            $this->peredaranBruto = $this->totalDpp + $this->totalDppNilaiLainnya;
            
            // Count invoices
            $this->fakturKeluarCount = $filteredFakturKeluar->count();
            $this->fakturMasukCount = $filteredFakturMasuk->count();
            
            // Count excluded invoices
            $this->fakturKeluarExcludedCount = $this->getExcludedFakturKeluarCount($taxReport);
            $this->fakturMasukExcludedCount = $this->getExcludedFakturMasukCount($taxReport);
            
            // Calculate selisih before compensation
            $selisih = $this->ppnKeluar - $this->ppnMasuk;
            
            // Get compensation data
            $this->kompensasiDiterima = $taxReport->approvedCompensationsReceived()
                ->where('tax_type', 'ppn')
                ->sum('amount_compensated');
            
            $this->kompensasiTerpakai = $taxReport->approvedCompensationsGiven()
                ->where('tax_type', 'ppn')
                ->sum('amount_compensated');
            
            // Calculate final balance
            $this->saldoFinal = $selisih - $this->kompensasiDiterima;
            
            // Determine final status
            if ($this->saldoFinal > 0) {
                $this->statusFinal = 'Kurang Bayar';
                $this->ppnKurangBayar = $this->saldoFinal;
                $this->ppnLebihBayar = 0;
                $this->kompensasiTersedia = 0;
            } elseif ($this->saldoFinal < 0) {
                $this->statusFinal = 'Lebih Bayar';
                $this->ppnLebihBayar = abs($this->saldoFinal);
                $this->ppnKurangBayar = 0;
                $this->kompensasiTersedia = abs($this->saldoFinal) - $this->kompensasiTerpakai;
            } else {
                $this->statusFinal = 'Nihil';
                $this->ppnKurangBayar = 0;
                $this->ppnLebihBayar = 0;
                $this->kompensasiTersedia = 0;
            }
        }
    }
    
    /**
     * Get filtered Faktur Keluar (exclude 02, 03, 07, 08)
     * dan hanya ambil revisi terbaru untuk setiap faktur
     */
    protected function getFilteredFakturKeluar($taxReport)
    {
        // Get all original invoices (not revisions)
        $originalInvoices = $taxReport->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->get()
            ->filter(function($invoice) {
                // Filter by first 2 characters of invoice number
                $firstTwo = substr($invoice->invoice_number, 0, 2);
                return !in_array($firstTwo, ['02', '03', '07', '08']);
            });
        
        $filteredInvoices = collect();
        
        foreach ($originalInvoices as $invoice) {
            // Check if this invoice has revisions
            $latestRevision = $invoice->revisions()
                ->orderBy('revision_number', 'desc')
                ->first();
            
            // If has revision, use the latest revision; otherwise use the original
            $filteredInvoices->push($latestRevision ?? $invoice);
        }
        
        return $filteredInvoices;
    }
    
    /**
     * Get filtered Faktur Masuk (only business-related)
     */
    protected function getFilteredFakturMasuk($taxReport)
    {
        // Get all original invoices that are business-related
        $originalInvoices = $taxReport->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->where('is_business_related', true)
            ->get();
        
        $filteredInvoices = collect();
        
        foreach ($originalInvoices as $invoice) {
            // Check if this invoice has revisions
            $latestRevision = $invoice->revisions()
                ->orderBy('revision_number', 'desc')
                ->first();
            
            // If has revision, use the latest revision; otherwise use the original
            $filteredInvoices->push($latestRevision ?? $invoice);
        }
        
        return $filteredInvoices;
    }
    
    /**
     * Count excluded Faktur Keluar (02, 03, 07, 08)
     */
    protected function getExcludedFakturKeluarCount($taxReport)
    {
        return $taxReport->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->get()
            ->filter(function($invoice) {
                $firstTwo = substr($invoice->invoice_number, 0, 2);
                return in_array($firstTwo, ['02', '03', '07', '08']);
            })
            ->count();
    }
    
    /**
     * Count excluded Faktur Masuk (not business-related)
     */
    protected function getExcludedFakturMasukCount($taxReport)
    {
        return $taxReport->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->where('is_business_related', false)
            ->count();
    }

    // Listen for events from nested components to refresh summary
    protected $listeners = [
        'invoiceCreated' => 'loadSummary', 
        'invoiceUpdated' => 'loadSummary', 
        'invoiceDeleted' => 'loadSummary',
        'compensationUpdated' => 'loadSummary'
    ];

    public function render()
    {
        return view('livewire.tax-report.components.tax-report-invoices');
    }
}