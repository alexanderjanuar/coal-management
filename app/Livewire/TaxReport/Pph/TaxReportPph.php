<?php

namespace App\Livewire\TaxReport\Pph;

use App\Models\IncomeTax;
use App\Models\TaxReport;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TaxReportPph extends Component
{
    // Properties for tax report data
    public $taxReportId;
    public $taxReport;
    
    // PPh calculations - cached for performance
    public $pph21Total = 0;
    public $pph21Count = 0;
    public $pph21Bruto = 0;
    
    public $pph23Total = 0;
    public $pph23Count = 0;
    public $pph23Bruto = 0;
    
    public $pph42Total = 0;
    public $pph42Count = 0;
    public $pph42Bruto = 0;
    
    public $totalPph = 0;
    public $totalBuktiPotong = 0;
    
    // Notes functionality
    public $newNote = '';
    public $existingNotes = [];
    
    // Listeners for real-time updates
    protected $listeners = [
        'refreshPphCalculations' => 'calculatePph',
        'pphDataUpdated' => 'calculatePph',
    ];
    
    /**
     * Mount the component with tax report ID
     */
    public function mount($taxReportId = null)
    {
        $this->taxReportId = $taxReportId;
        
        // Load tax report data
        $this->loadTaxReportData();
        
        // Calculate PPh totals
        $this->calculatePph();
    }
    
    /**
     * Load tax report data with minimal queries
     */
    protected function loadTaxReportData()
    {
        if ($this->taxReportId) {
            $this->taxReport = TaxReport::with('client:id,name')
                ->select('id', 'client_id', 'month')
                ->find($this->taxReportId);
        }
    }
    
    /**
     * Calculate PPh totals from income_taxes table
     * Optimized with single query using CASE statements
     */
    public function calculatePph()
    {
        if (!$this->taxReportId) {
            return;
        }
        
        // Use cache to avoid repeated calculations (5 minutes cache)
        $cacheKey = "pph_calculations_{$this->taxReportId}";
        
        $calculations = Cache::remember($cacheKey, 300, function () {
            // Single optimized query using aggregations and CASE statements
            return DB::table('income_taxes')
                ->where('tax_report_id', $this->taxReportId)
                ->select([
                    // PPh 21
                    DB::raw('SUM(CASE WHEN jenis_pajak = "Pasal 21" THEN pajak_penghasilan ELSE 0 END) as pph21_total'),
                    DB::raw('COUNT(CASE WHEN jenis_pajak = "Pasal 21" THEN 1 END) as pph21_count'),
                    DB::raw('SUM(CASE WHEN jenis_pajak = "Pasal 21" THEN dasar_pengenaan_pajak ELSE 0 END) as pph21_bruto'),
                    
                    // PPh 23
                    DB::raw('SUM(CASE WHEN jenis_pajak = "Pasal 23" THEN pajak_penghasilan ELSE 0 END) as pph23_total'),
                    DB::raw('COUNT(CASE WHEN jenis_pajak = "Pasal 23" THEN 1 END) as pph23_count'),
                    DB::raw('SUM(CASE WHEN jenis_pajak = "Pasal 23" THEN dasar_pengenaan_pajak ELSE 0 END) as pph23_bruto'),
                    
                    // PPh 4(2)
                    DB::raw('SUM(CASE WHEN jenis_pajak IN ("Pasal 4(2)", "Pasal 4 ayat 2") THEN pajak_penghasilan ELSE 0 END) as pph42_total'),
                    DB::raw('COUNT(CASE WHEN jenis_pajak IN ("Pasal 4(2)", "Pasal 4 ayat 2") THEN 1 END) as pph42_count'),
                    DB::raw('SUM(CASE WHEN jenis_pajak IN ("Pasal 4(2)", "Pasal 4 ayat 2") THEN dasar_pengenaan_pajak ELSE 0 END) as pph42_bruto'),
                    
                    // Totals
                    DB::raw('SUM(pajak_penghasilan) as total_pph'),
                    DB::raw('COUNT(*) as total_bukti_potong')
                ])
                ->first();
        });
        
        // Assign to component properties
        $this->pph21Total = $calculations->pph21_total ?? 0;
        $this->pph21Count = $calculations->pph21_count ?? 0;
        $this->pph21Bruto = $calculations->pph21_bruto ?? 0;
        
        $this->pph23Total = $calculations->pph23_total ?? 0;
        $this->pph23Count = $calculations->pph23_count ?? 0;
        $this->pph23Bruto = $calculations->pph23_bruto ?? 0;
        
        $this->pph42Total = $calculations->pph42_total ?? 0;
        $this->pph42Count = $calculations->pph42_count ?? 0;
        $this->pph42Bruto = $calculations->pph42_bruto ?? 0;
        
        $this->totalPph = $calculations->total_pph ?? 0;
        $this->totalBuktiPotong = $calculations->total_bukti_potong ?? 0;
    }
    
    /**
     * Load notes from tax report
     */
    public function loadNotes()
    {
        if ($this->taxReport && $this->taxReport->notes) {
            $this->existingNotes = json_decode($this->taxReport->notes, true) ?? [];
        } else {
            $this->existingNotes = [];
        }
    }
    
    /**
     * Save a new note
     */
    public function saveNote()
    {
        $this->validate([
            'newNote' => 'required|min:3|max:1000',
        ], [
            'newNote.required' => 'Catatan tidak boleh kosong',
            'newNote.min' => 'Catatan minimal 3 karakter',
            'newNote.max' => 'Catatan maksimal 1000 karakter',
        ]);
        
        if ($this->taxReport) {
            $notes = json_decode($this->taxReport->notes, true) ?? [];
            
            $notes[] = [
                'content' => $this->newNote,
                'created_at' => now()->toDateTimeString(),
                'created_by' => auth()->id(),
                'created_by_name' => auth()->user()->name,
            ];
            
            $this->taxReport->update([
                'notes' => json_encode($notes)
            ]);
            
            $this->existingNotes = $notes;
            $this->newNote = '';
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Catatan berhasil disimpan'
            ]);
        }
    }
    
    /**
     * Delete a note
     */
    public function deleteNote($index)
    {
        if ($this->taxReport && isset($this->existingNotes[$index])) {
            $notes = $this->existingNotes;
            unset($notes[$index]);
            $notes = array_values($notes); // Re-index array
            
            $this->taxReport->update([
                'notes' => json_encode($notes)
            ]);
            
            $this->existingNotes = $notes;
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Catatan berhasil dihapus'
            ]);
        }
    }
    
    /**
     * Refresh all calculations and clear cache
     */
    public function refreshCalculations()
    {
        // Clear cache
        Cache::forget("pph_calculations_{$this->taxReportId}");
        
        // Recalculate
        $this->calculatePph();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Kalkulasi berhasil diperbarui'
        ]);
    }
    
    /**
     * Get formatted period name
     */
    public function getPeriodNameProperty()
    {
        if (!$this->taxReport) {
            return '-';
        }
        
        return \Carbon\Carbon::parse($this->taxReport->month . '-01')->format('F Y');
    }
    
    /**
     * Get client name
     */
    public function getClientNameProperty()
    {
        return $this->taxReport->client->name ?? 'Unknown Client';
    }

    /**
     * Render the component
     */
    public function render()
    {
        // Load notes when rendering catatan tab
        $this->loadNotes();
        
        return view('livewire.tax-report.pph.tax-report-pph');
    }
}