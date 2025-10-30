<?php

namespace App\Livewire\TaxReport\Components;

use App\Models\TaxReport;
use App\Models\Invoice;
use Livewire\Component;

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

    public function mount($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        $this->loadSummary();
    }

    public function loadSummary()
    {
        $taxReport = TaxReport::with('invoices')->find($this->taxReportId);
        
        if ($taxReport) {
            $this->ppnMasuk = $taxReport->invoices->where('type', 'Faktur Masuk')->sum('ppn');
            $this->ppnKeluar = $taxReport->invoices->where('type', 'Faktur Keluaran')->sum('ppn');
            $this->fakturMasukCount = $taxReport->invoices->where('type', 'Faktur Masuk')->count();
            $this->fakturKeluarCount = $taxReport->invoices->where('type', 'Faktur Keluaran')->count();
            
            $this->ppnKurangBayar = max(0, $this->ppnKeluar - $this->ppnMasuk);
            $this->ppnLebihBayar = max(0, $this->ppnMasuk - $this->ppnKeluar);
        }
    }

    // Listen for events from nested components to refresh summary
    protected $listeners = ['invoiceCreated' => 'loadSummary', 'invoiceUpdated' => 'loadSummary', 'invoiceDeleted' => 'loadSummary'];

    public function render()
    {
        return view('livewire.tax-report.components.tax-report-invoices');
    }
}