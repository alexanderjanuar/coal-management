<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TaxReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'month',
        'created_by'
    ];

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function invoices(){
        return $this->hasMany(Invoice::class);
    }

    public function incomeTaxs(){
        return $this->hasMany(IncomeTax::class);
    }

    public function bupots(){
        return $this->hasMany(Bupot::class);
    }

    // New compensation relationships
    public function compensationsGiven()
    {
        return $this->hasMany(TaxCompensation::class, 'source_tax_report_id');
    }

    public function compensationsReceived()
    {
        return $this->hasMany(TaxCompensation::class, 'target_tax_report_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get invoices relationship excluding revisions
     */
    public function originalInvoices()
    {
        return $this->invoices()->where('is_revision', false);
    }

    /**
     * Get only revision invoices
     */
    public function revisionInvoices()
    {
        return $this->invoices()->where('is_revision', true);
    }

    /**
     * Manually recalculate tax report status (updated to exclude revisions)
     * Useful for data fixes or manual updates
     */
    public function recalculateStatus(): void
    {
        // Get total PPN from Faktur Masuk (excluding revisions)
        $totalPpnMasuk = $this->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
        
        // Get total PPN from Faktur Keluar (excluding revisions)
        $totalPpnKeluar = $this->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->sum('ppn');
        
        // Calculate difference
        $selisihPpn = $totalPpnKeluar - $totalPpnMasuk;
        
        // Determine status
        $status = 'Nihil';
        if ($selisihPpn > 0) {
            $status = 'Kurang Bayar';
        } elseif ($selisihPpn < 0) {
            $status = 'Lebih Bayar';
        }
        
        $this->update(['invoice_tax_status' => $status]);
    }

    /**
     * Get selisih PPN (difference) - excludes revisions
     */
    public function getSelisihPpn(): float
    {
        return $this->getTotalPpnKeluar() - $this->getTotalPpnMasuk();
    }

    /**
     * Get formatted selisih PPN with currency
     */
    public function getFormattedSelisihPpn(): string
    {
        $selisih = $this->getSelisihPpn();
        return 'Rp ' . number_format(abs($selisih), 0, ',', '.');
    }

    /**
     * Get status with amount for display
     */
    public function getStatusWithAmount(): string
    {
        if (!$this->invoice_tax_status || $this->invoice_tax_status === 'Nihil') {
            return 'Nihil';
        }
        
        $amount = $this->getFormattedSelisihPpn();
        return $this->invoice_tax_status;
    }

    /**
     * Get status color for display
     */
    public function getStatusColor(): string
    {
        return match($this->invoice_tax_status) {
            'Lebih Bayar' => 'success',  // Green
            'Kurang Bayar' => 'warning', // Orange/Yellow
            'Nihil' => 'gray',           // Gray
            default => 'gray'
        };
    }

    /**
     * Check if tax report has overpayment
     */
    public function isLebihBayar(): bool
    {
        return $this->invoice_tax_status === 'Lebih Bayar';
    }

    /**
     * Check if tax report has underpayment
     */
    public function isKurangBayar(): bool
    {
        return $this->invoice_tax_status === 'Kurang Bayar';
    }

    /**
     * Check if tax report is balanced (nihil)
     */
    public function isNihil(): bool
    {
        return $this->invoice_tax_status === 'Nihil' || is_null($this->invoice_tax_status);
    }

    /**
     * Calculate complete tax status with compensation (updated to exclude revisions)
     */
    public function calculateFinalTaxStatus()
    {
        $totalPpnKeluaran = $this->originalInvoices()->where('type', 'Faktur Keluaran')->sum('ppn');
        $totalPpnMasukan = $this->originalInvoices()->where('type', 'Faktur Masuk')->sum('ppn');
        
        // Basic PPN calculation
        $ppnTerutang = $totalPpnKeluaran - $totalPpnMasukan;
        
        // Apply compensation from previous months
        $finalAmount = $ppnTerutang - $this->ppn_dikompensasi_dari_masa_sebelumnya;
        
        // Determine status using your enum values
        $status = 'Nihil';
        if ($finalAmount > 0) {
            $status = 'Kurang Bayar';
        } elseif ($finalAmount < 0) {
            $status = 'Lebih Bayar';
        }

        return [
            'ppn_keluaran' => $totalPpnKeluaran,
            'ppn_masukan' => $totalPpnMasukan,
            'ppn_terutang' => $ppnTerutang,
            'ppn_dikompensasi' => $this->ppn_dikompensasi_dari_masa_sebelumnya,
            'final_amount' => $finalAmount,
            'status' => $status,
            'available_for_compensation' => $status === 'Lebih Bayar' ? abs($finalAmount) : 0
        ];
    }

    /**
     * Get available compensation from previous months
     */
    public function getAvailableCompensations()
    {
        return self::where('client_id', $this->client_id)
                ->where('created_at', '<', $this->created_at ?? now())
                ->where('invoice_tax_status', 'Lebih Bayar') // Updated enum value
                ->whereRaw('ppn_lebih_bayar_dibawa_ke_masa_depan > ppn_sudah_dikompensasi')
                ->get()
                ->map(function ($report) {
                    $available = $report->ppn_lebih_bayar_dibawa_ke_masa_depan - $report->ppn_sudah_dikompensasi;
                    return [
                        'id' => $report->id,
                        'month' => $report->month,
                        'total_lebih_bayar' => $report->ppn_lebih_bayar_dibawa_ke_masa_depan,
                        'already_used' => $report->ppn_sudah_dikompensasi,
                        'available_amount' => $available,
                        'label' => "Bulan {$report->month} - Tersedia: Rp " . number_format($available, 0, ',', '.')
                    ];
                });
    }

    /**
     * Get total PPN Keluar with filtering (excludes certain invoice numbers and revisions)
     */
    public function getTotalPpnKeluarFiltered(): float
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->whereNotIn(DB::raw('LEFT(invoice_number, 2)'), ['02', '03', '07', '08'])
            ->sum('ppn');
    }

    /**
     * Get total PPN Masuk (excludes revisions but no number filtering)
     */
    public function getTotalPpnMasukFiltered(): float
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
    }

    /**
     * Get total PPN Keluar (without filter, excludes revisions) - for backward compatibility
     */
    public function getTotalPpnKeluar(): float
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->sum('ppn');
    }

    /**
     * Get total PPN Masuk (without filter, excludes revisions) - for backward compatibility
     */
    public function getTotalPpnMasuk(): float
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
    }

    /**
     * Get Peredaran Bruto (total DPP from Faktur Keluaran, excludes revisions)
     */
    public function getPeredaranBruto(): float
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->sum('dpp');
    }

    /**
     * Get total DPP for Faktur Keluar with filtering (excludes certain numbers and revisions)
     */
    public function getTotalDppKeluarFiltered(): float
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->whereNotIn(DB::raw('LEFT(invoice_number, 2)'), ['02', '03', '07', '08'])
            ->sum('dpp');
    }

    /**
     * Get total DPP for Faktur Masuk (excludes revisions only)
     */
    public function getTotalDppMasuk(): float
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->sum('dpp');
    }

    /**
     * Get count of invoices by type (excludes revisions)
     */
    public function getInvoiceCount($type = null): int
    {
        $query = $this->originalInvoices();
        
        if ($type) {
            $query->where('type', $type);
        }
        
        return $query->count();
    }

    /**
     * Get count of filtered Faktur Keluaran (excludes certain numbers and revisions)
     */
    public function getFilteredFakturKeluarCount(): int
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->whereNotIn(DB::raw('LEFT(invoice_number, 2)'), ['02', '03', '07', '08'])
            ->count();
    }

    /**
     * Calculate effective PPN payment considering compensation and excluding revisions
     */
    public function getEffectivePpnPayment(): float
    {
        $ppnKeluar = $this->getTotalPpnKeluarFiltered();
        $ppnMasuk = $this->getTotalPpnMasukFiltered();
        $compensation = $this->ppn_dikompensasi_dari_masa_sebelumnya ?? 0;
        
        return ($ppnKeluar - $ppnMasuk) - $compensation;
    }

    /**
     * Auto-calculate and update invoice tax status based on filtered amounts
     */
    public function updateInvoiceTaxStatus(): string
    {
        $effectivePayment = $this->getEffectivePpnPayment();
        
        if ($effectivePayment > 0) {
            $this->invoice_tax_status = 'Kurang Bayar';
        } elseif ($effectivePayment < 0) {
            $this->invoice_tax_status = 'Lebih Bayar';
            
            // Update the amount available for future compensation
            $this->ppn_lebih_bayar_dibawa_ke_masa_depan = abs($effectivePayment);
        } else {
            $this->invoice_tax_status = 'Nihil';
        }
        
        $this->save();
        
        return $this->invoice_tax_status;
    }

    /**
     * Get breakdown of invoice numbers that are filtered out
     */
    public function getFilteredOutInvoices()
    {
        return $this->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->whereIn(DB::raw('LEFT(invoice_number, 2)'), ['02', '03', '07', '08'])
            ->select('invoice_number', 'company_name', 'dpp', 'ppn', 'notes')
            ->get();
    }

    /**
     * Get summary statistics excluding revisions
     */
    public function getSummaryStats(): array
    {
        $originalInvoices = $this->originalInvoices();
        
        return [
            'total_invoices' => $originalInvoices->count(),
            'faktur_keluar_count' => $originalInvoices->where('type', 'Faktur Keluaran')->count(),
            'faktur_masuk_count' => $originalInvoices->where('type', 'Faktur Masuk')->count(),
            'filtered_faktur_keluar_count' => $this->getFilteredFakturKeluarCount(),
            'excluded_invoices_count' => $originalInvoices
                ->where('type', 'Faktur Keluaran')
                ->whereIn(DB::raw('LEFT(invoice_number, 2)'), ['02', '03', '07', '08'])
                ->count(),
            'revision_count' => $this->revisionInvoices()->count(),
            'peredaran_bruto' => $this->getPeredaranBruto(),
            'ppn_keluar_filtered' => $this->getTotalPpnKeluarFiltered(),
            'ppn_masuk' => $this->getTotalPpnMasukFiltered(),
            'effective_payment' => $this->getEffectivePpnPayment(),
        ];
    }

    /**
     * Get selisih PPN with filtered invoice numbers (updated version)
     */
    public function getSelisihPpnWithFilter(): float
    {
        $ppnKeluar = $this->getTotalPpnKeluarFiltered();
        $ppnMasuk = $this->getTotalPpnMasukFiltered();
        
        return $ppnKeluar - $ppnMasuk;
    }

    /**
     * Apply compensation from previous tax reports
     */
    public function applyCompensation(array $compensationData)
    {
        $totalCompensation = 0;
        $compensationNotes = [];

        foreach ($compensationData as $sourceId => $amount) {
            if ($amount <= 0) continue;

            $sourceTaxReport = self::find($sourceId);
            if (!$sourceTaxReport) continue;

            // Check available amount
            $available = $sourceTaxReport->ppn_lebih_bayar_dibawa_ke_masa_depan - $sourceTaxReport->ppn_sudah_dikompensasi;
            $actualAmount = min($amount, $available);

            if ($actualAmount > 0) {
                // Create compensation record
                TaxCompensation::create([
                    'source_tax_report_id' => $sourceId,
                    'target_tax_report_id' => $this->id,
                    'amount_compensated' => $actualAmount,
                    'notes' => "Kompensasi dari bulan {$sourceTaxReport->month}"
                ]);

                // Update source report
                $sourceTaxReport->increment('ppn_sudah_dikompensasi', $actualAmount);

                $totalCompensation += $actualAmount;
                $compensationNotes[] = "Rp " . number_format($actualAmount, 0, ',', '.') . " dari bulan {$sourceTaxReport->month}";
            }
        }

        // Update current report
        $this->update([
            'ppn_dikompensasi_dari_masa_sebelumnya' => $totalCompensation,
            'kompensasi_notes' => "Dikompensasi: " . implode(', ', $compensationNotes)
        ]);

        return $totalCompensation;
    }
}