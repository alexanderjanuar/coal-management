<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Manually recalculate tax report status
     * Useful for data fixes or manual updates
     */
    public function recalculateStatus(): void
    {
        // Get total PPN from Faktur Masuk
        $totalPpnMasuk = $this->invoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
        
        // Get total PPN from Faktur Keluar
        $totalPpnKeluar = $this->invoices()
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
     * Get selisih PPN (difference)
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
     * Calculate complete tax status with compensation
     */
    public function calculateFinalTaxStatus()
    {
        $totalPpnKeluaran = $this->invoices()->where('type', 'Faktur Keluaran')->sum('ppn');
        $totalPpnMasukan = $this->invoices()->where('type', 'Faktur Masuk')->sum('ppn');
        
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
     * Get total PPN Keluar with filtered invoice numbers
     */
    public function getTotalPpnKeluarFiltered(): float
    {
        return $this->invoices()
            ->where('type', 'Faktur Keluaran')
            ->where(function ($query) {
                $query->where('invoice_number', 'NOT LIKE', '02%')
                    ->where('invoice_number', 'NOT LIKE', '03%')
                    ->where('invoice_number', 'NOT LIKE', '07%')
                    ->where('invoice_number', 'NOT LIKE', '08%');
            })
            ->sum('ppn');
    }

    /**
     * Get total PPN Masuk (all incoming invoices without filter)
     */
    public function getTotalPpnMasukFiltered(): float
    {
        return $this->invoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
    }

    /**
     * Get total PPN Keluar (without filter) - for backward compatibility
     */
    public function getTotalPpnKeluar(): float
    {
        return $this->invoices()
            ->where('type', 'Faktur Keluaran')
            ->sum('ppn');
    }

    /**
     * Get total PPN Masuk (without filter) - for backward compatibility
     */
    public function getTotalPpnMasuk(): float
    {
        return $this->invoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
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
     * Get total peredaran bruto (all outgoing invoices DPP without filter)
     */
    public function getPeredaranBruto(): float
    {
        return $this->invoices()
            ->where('type', 'Faktur Keluaran')
            ->sum('dpp');
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
