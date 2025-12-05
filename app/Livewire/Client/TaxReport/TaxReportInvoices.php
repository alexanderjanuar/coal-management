<?php

namespace App\Livewire\Client\TaxReport;

use App\Models\TaxReport;
use App\Models\Invoice;
use App\Models\TaxCalculationSummary;
use App\Models\UserClient;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class TaxReportInvoices extends Component
{
    public $taxReportId;
    public $taxReport;
    public $activeTab = 'daftar-pajak';
    
    // Summary data properties
    public $ppnMasuk = 0;
    public $ppnKeluar = 0;
    public $ppnKurangBayar = 0;
    public $ppnLebihBayar = 0;
    public $fakturMasukCount = 0;
    public $fakturKeluarCount = 0;
    
    // Peredaran Bruto properties
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
    
    // Notes properties
    public $newNote = '';
    public $existingNotes = [];
    public $currentNotes = '';
    public $ppnSummary = null;

    /**
     * Mount component with tax report ID
     */
    public function mount($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        
        // Verify user has access to this tax report's client
        $this->verifyAccess();
        
        $this->loadTaxReport();
        $this->loadSummary();
        $this->loadNotes();
    }

    /**
     * Verify user has access to the client
     */
    protected function verifyAccess()
    {
        $taxReport = TaxReport::findOrFail($this->taxReportId);
        
        $hasAccess = UserClient::where('user_id', auth()->id())
            ->where('client_id', $taxReport->client_id)
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke laporan pajak ini.');
        }
    }

    /**
     * Load tax report with relationships
     */
    protected function loadTaxReport()
    {
        $this->taxReport = TaxReport::with([
            'invoices',
            'client',
            'approvedCompensationsReceived',
            'approvedCompensationsGiven',
            'taxCalculationSummaries'
        ])->findOrFail($this->taxReportId);
    }

    /**
     * Load notes from PPN tax calculation summary
     */
    protected function loadNotes()
    {
        $this->ppnSummary = $this->taxReport->taxCalculationSummaries()
            ->where('tax_type', 'ppn')
            ->first();

        if ($this->ppnSummary) {
            $this->currentNotes = $this->ppnSummary->notes ?? '';
            
            // Parse existing notes if they contain multiple entries
            if (!empty($this->currentNotes)) {
                $this->existingNotes = $this->parseExistingNotes($this->currentNotes);
            }
        }
    }

    /**
     * Parse existing notes into array format
     */
    protected function parseExistingNotes($notes)
    {
        $parsedNotes = [];
        
        // Check if notes contain timestamps (indicating multiple entries)
        if (strpos($notes, '[') !== false && strpos($notes, ']') !== false) {
            // Split by newlines and parse each entry
            $lines = explode("\n", $notes);
            $currentEntry = '';
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Check if line starts with timestamp pattern [YYYY-MM-DD HH:MM]
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2})\](.*)/', $line, $matches)) {
                    // Save previous entry if exists
                    if (!empty($currentEntry)) {
                        $parsedNotes[] = [
                            'content' => trim($currentEntry),
                            'created_at' => $previousTimestamp ?? now()->format('Y-m-d H:i')
                        ];
                    }
                    
                    // Start new entry
                    $previousTimestamp = $matches[1];
                    $currentEntry = trim($matches[2]);
                } else {
                    // Continuation of current entry
                    $currentEntry .= "\n" . $line;
                }
            }
            
            // Add last entry
            if (!empty($currentEntry)) {
                $parsedNotes[] = [
                    'content' => trim($currentEntry),
                    'created_at' => $previousTimestamp ?? now()->format('Y-m-d H:i')
                ];
            }
        } else {
            // Single note entry
            $parsedNotes[] = [
                'content' => $notes,
                'created_at' => $this->ppnSummary->updated_at->format('Y-m-d H:i')
            ];
        }
        
        // Sort by newest first
        return array_reverse($parsedNotes);
    }

    /**
     * Load and calculate all summary data
     */
    public function loadSummary()
    {
        if (!$this->taxReport) {
            $this->loadTaxReport();
        }
        
        // Get filtered invoices
        $filteredFakturKeluar = $this->getFilteredFakturKeluar();
        $filteredFakturMasuk = $this->getFilteredFakturMasuk();
        
        // Calculate PPN
        $this->ppnKeluar = $filteredFakturKeluar->sum('ppn');
        $this->ppnMasuk = $filteredFakturMasuk->sum('ppn');
        
        // Calculate Peredaran Bruto
        $this->totalDpp = $filteredFakturKeluar->sum('dpp');
        $this->totalDppNilaiLainnya = $filteredFakturKeluar->sum('dpp_nilai_lainnya');
        $this->peredaranBruto = $this->totalDpp + $this->totalDppNilaiLainnya;
        
        // Count invoices
        $this->fakturKeluarCount = $filteredFakturKeluar->count();
        $this->fakturMasukCount = $filteredFakturMasuk->count();
        
        // Count excluded invoices
        $this->fakturKeluarExcludedCount = $this->getExcludedFakturKeluarCount();
        $this->fakturMasukExcludedCount = $this->getExcludedFakturMasukCount();
        
        // Calculate compensation and final status
        $this->calculateCompensationAndStatus();
    }
    
    /**
     * Calculate compensation amounts and final status
     */
    protected function calculateCompensationAndStatus()
    {
        // Calculate selisih before compensation
        $selisih = $this->ppnKeluar - $this->ppnMasuk;
        
        // Get compensation data
        $this->kompensasiDiterima = $this->taxReport->approvedCompensationsReceived()
            ->where('tax_type', 'ppn')
            ->sum('amount_compensated');
        
        $this->kompensasiTerpakai = $this->taxReport->approvedCompensationsGiven()
            ->where('tax_type', 'ppn')
            ->sum('amount_compensated');
        
        // Calculate final balance
        $this->saldoFinal = $selisih - $this->kompensasiDiterima;
        
        // Determine final status and amounts
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
    
    /**
     * Get filtered Faktur Keluar
     * Excludes invoice types 02, 03, 07, 08
     * Uses latest revision if exists
     */
    protected function getFilteredFakturKeluar()
    {
        $originalInvoices = $this->taxReport->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->get()
            ->filter(function($invoice) {
                return !$this->isExcludedInvoiceType($invoice->invoice_number);
            });
        
        return $this->getLatestVersions($originalInvoices);
    }
    
    /**
     * Get filtered Faktur Masuk
     * Only includes business-related invoices
     * Uses latest revision if exists
     */
    protected function getFilteredFakturMasuk()
    {
        $originalInvoices = $this->taxReport->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->where('is_business_related', true)
            ->get();
        
        return $this->getLatestVersions($originalInvoices);
    }
    
    /**
     * Get latest version of each invoice (revision or original)
     */
    protected function getLatestVersions($invoices)
    {
        $filteredInvoices = collect();
        
        foreach ($invoices as $invoice) {
            $latestRevision = $invoice->revisions()
                ->orderBy('revision_number', 'desc')
                ->first();
            
            $filteredInvoices->push($latestRevision ?? $invoice);
        }
        
        return $filteredInvoices;
    }
    
    /**
     * Check if invoice type should be excluded
     * Invoice types 02, 03, 07, 08 are excluded
     */
    protected function isExcludedInvoiceType($invoiceNumber)
    {
        $firstTwo = substr($invoiceNumber, 0, 2);
        return in_array($firstTwo, ['02', '03', '07', '08']);
    }
    
    /**
     * Count excluded Faktur Keluar (02, 03, 07, 08)
     */
    protected function getExcludedFakturKeluarCount()
    {
        return $this->taxReport->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->get()
            ->filter(function($invoice) {
                return $this->isExcludedInvoiceType($invoice->invoice_number);
            })
            ->count();
    }
    
    /**
     * Count excluded Faktur Masuk (not business-related)
     */
    protected function getExcludedFakturMasukCount()
    {
        return $this->taxReport->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->where('is_business_related', false)
            ->count();
    }

    /**
     * Get summary statistics for display
     */
    public function getSummaryStats()
    {
        return [
            'total_invoices' => $this->fakturMasukCount + $this->fakturKeluarCount,
            'total_excluded' => $this->fakturMasukExcludedCount + $this->fakturKeluarExcludedCount,
            'selisih_before_compensation' => $this->ppnKeluar - $this->ppnMasuk,
            'compensation_impact' => $this->kompensasiDiterima,
            'final_balance' => $this->saldoFinal,
        ];
    }

    /**
     * Handle month change event from parent component
     */
    public function handleMonthChange($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        $this->verifyAccess();
        $this->refreshData();
    }

    /**
     * Handle tax report change event from parent component
     */
    public function handleTaxReportChange($taxReportId)
    {
        $this->taxReportId = $taxReportId;
        $this->verifyAccess();
        $this->refreshData();
    }

    /**
     * Watch for changes in taxReportId property
     */
    public function updatedTaxReportId($value)
    {
        if ($value) {
            $this->verifyAccess();
            $this->refreshData();
        }
    }

    /**
     * Listen for events from nested components to refresh summary
     */
    protected $listeners = [
        'invoiceCreated' => 'refreshData', 
        'invoiceUpdated' => 'refreshData', 
        'invoiceDeleted' => 'refreshData',
        'compensationUpdated' => 'refreshData',
        'monthChanged' => 'handleMonthChange',
        'taxReportChanged' => 'handleTaxReportChange',
    ];

    /**
     * Refresh all data
     */
    public function refreshData()
    {
        $this->verifyAccess();
        $this->loadTaxReport();
        $this->loadSummary();
        $this->loadNotes();
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.client.tax-report.tax-report-invoices');
    }
}