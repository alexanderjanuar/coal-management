<?php

namespace App\Livewire\TaxReport\Pph;

use App\Models\IncomeTax;
use App\Models\TaxReport;
use Livewire\Component;

class TaxReportPph extends Component
{
    // Properties for tax report data
    public $taxReportId;
    public $taxReport;
    
    // PPh calculations
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
    
    // Kompensasi data
    public $kompensasiDiterima = 0;
    public $kompensasiTersedia = 0;
    public $kompensasiTerpakai = 0;
    
    // Notes functionality
    public $newNote = '';
    public $existingNotes = [];
    
    // Listeners for real-time updates
    protected $listeners = ['refreshPphCalculations' => 'calculatePph'];
    
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
        
        // Load notes if needed
        // $this->loadNotes();
    }
    
    /**
     * Load tax report data
     */
    protected function loadTaxReportData()
    {
        if ($this->taxReportId) {
            $this->taxReport = TaxReport::with('client')->find($this->taxReportId);
        }
    }
    
    /**
     * Calculate PPh totals from income_taxes table
     */
    public function calculatePph()
    {
        if (!$this->taxReportId) {
            return;
        }
        
        // Get all income taxes for this tax report
        $incomeTaxes = IncomeTax::where('tax_report_id', $this->taxReportId)->get();
        
        // PPh 21 (Pasal 21)
        $pph21Records = $incomeTaxes->where('jenis_pajak', 'Pasal 21');
        $this->pph21Total = $pph21Records->sum('pajak_penghasilan');
        $this->pph21Count = $pph21Records->count();
        $this->pph21Bruto = $pph21Records->sum('dasar_pengenaan_pajak');
        
        // PPh 23 (Pasal 23)
        $pph23Records = $incomeTaxes->where('jenis_pajak', 'Pasal 23');
        $this->pph23Total = $pph23Records->sum('pajak_penghasilan');
        $this->pph23Count = $pph23Records->count();
        $this->pph23Bruto = $pph23Records->sum('dasar_pengenaan_pajak');
        
        // PPh 4(2) (Pasal 4(2) or Pasal 4 ayat 2)
        $pph42Records = $incomeTaxes->whereIn('jenis_pajak', ['Pasal 4(2)', 'Pasal 4 ayat 2']);
        $this->pph42Total = $pph42Records->sum('pajak_penghasilan');
        $this->pph42Count = $pph42Records->count();
        $this->pph42Bruto = $pph42Records->sum('dasar_pengenaan_pajak');
        
        // Totals
        $this->totalPph = $incomeTaxes->sum('pajak_penghasilan');
        $this->totalBuktiPotong = $incomeTaxes->count();
        
        // Load kompensasi data from tax_calculation_summaries
        $this->loadKompensasiData();
    }
    
    /**
     * Load kompensasi data from tax_calculation_summaries
     */
    protected function loadKompensasiData()
    {
        if (!$this->taxReport) {
            return;
        }
        
        // Get PPh summary from tax_calculation_summaries
        $pphSummary = $this->taxReport->taxCalculationSummaries()
            ->where('tax_type', 'pph')
            ->first();
        
        if ($pphSummary) {
            $this->kompensasiDiterima = $pphSummary->kompensasi_diterima ?? 0;
            $this->kompensasiTersedia = $pphSummary->kompensasi_tersedia ?? 0;
            $this->kompensasiTerpakai = $pphSummary->kompensasi_terpakai ?? 0;
        }
    }
    
    /**
     * Load notes from tax report
     */
    protected function loadNotes()
    {
        if ($this->taxReport && $this->taxReport->notes) {
            $this->existingNotes = json_decode($this->taxReport->notes, true) ?? [];
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
                'created_by' => auth()->user()->name,
            ];
            
            $this->taxReport->update([
                'notes' => json_encode($notes)
            ]);
            
            $this->existingNotes = $notes;
            $this->newNote = '';
            
            $this->dispatch('notify', [
                'message' => 'Catatan berhasil disimpan',
                'type' => 'success'
            ]);
        }
    }
    
    /**
     * Delete a note
     */
    public function deleteNote($index)
    {
        if ($this->taxReport) {
            $notes = json_decode($this->taxReport->notes, true) ?? [];
            
            if (isset($notes[$index])) {
                unset($notes[$index]);
                $notes = array_values($notes); // Re-index array
                
                $this->taxReport->update([
                    'notes' => json_encode($notes)
                ]);
                
                $this->existingNotes = $notes;
                
                $this->dispatch('notify', [
                    'message' => 'Catatan berhasil dihapus',
                    'type' => 'success'
                ]);
            }
        }
    }
    
    /**
     * Refresh all calculations
     */
    public function refreshCalculations()
    {
        $this->calculatePph();
        
        $this->dispatch('notify', [
            'message' => 'Kalkulasi berhasil diperbarui',
            'type' => 'success'
        ]);
    }


    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.tax-report.pph.tax-report-pph');
    }
}