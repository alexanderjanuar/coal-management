<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TaxCalculationSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_report_id',
        'tax_type',
        'pajak_masuk',
        'pajak_keluar',
        'selisih',
        'status',
        'kompensasi_diterima',
        'kompensasi_tersedia',
        'kompensasi_terpakai',
        'saldo_final',
        'status_final',
        'notes',
        'calculated_at',
        'calculated_by',
    ];

    protected $casts = [
        'pajak_masuk' => 'decimal:2',
        'pajak_keluar' => 'decimal:2',
        'selisih' => 'decimal:2',
        'kompensasi_diterima' => 'decimal:2',
        'kompensasi_tersedia' => 'decimal:2',
        'kompensasi_terpakai' => 'decimal:2',
        'saldo_final' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function taxReport()
    {
        return $this->belongsTo(TaxReport::class);
    }

    public function calculatedBy()
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    /**
     * Scopes
     */
    public function scopePpn(Builder $query): Builder
    {
        return $query->where('tax_type', 'ppn');
    }

    public function scopePph(Builder $query): Builder
    {
        return $query->where('tax_type', 'pph');
    }

    public function scopeBupot(Builder $query): Builder
    {
        return $query->where('tax_type', 'bupot');
    }

    public function scopeLebihBayar(Builder $query): Builder
    {
        return $query->where('status_final', 'Lebih Bayar');
    }

    public function scopeKurangBayar(Builder $query): Builder
    {
        return $query->where('status_final', 'Kurang Bayar');
    }

    /**
     * Accessors
     */
    public function getTaxTypeNameAttribute(): string
    {
        return match($this->tax_type) {
            'ppn' => 'PPN',
            'pph' => 'PPh',
            'bupot' => 'PPh Unifikasi',
            default => 'Unknown'
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status_final ?? $this->status) {
            'Lebih Bayar' => 'success',
            'Kurang Bayar' => 'warning',
            'Nihil' => 'gray',
            default => 'gray'
        };
    }

    public function getFormattedPajakMasukAttribute(): string
    {
        return 'Rp ' . number_format($this->pajak_masuk, 0, ',', '.');
    }

    public function getFormattedPajakKeluarAttribute(): string
    {
        return 'Rp ' . number_format($this->pajak_keluar, 0, ',', '.');
    }

    public function getFormattedSelisihAttribute(): string
    {
        return 'Rp ' . number_format(abs($this->selisih), 0, ',', '.');
    }

    public function getFormattedSaldoFinalAttribute(): string
    {
        return 'Rp ' . number_format(abs($this->saldo_final), 0, ',', '.');
    }

    public function getFormattedKompensasiTersediaAttribute(): string
    {
        return 'Rp ' . number_format($this->kompensasi_tersedia, 0, ',', '.');
    }

    public function getSisaKompensasiTersediaAttribute(): float
    {
        return max(0, $this->kompensasi_tersedia - $this->kompensasi_terpakai);
    }

    public function getFormattedSisaKompensasiAttribute(): string
    {
        return 'Rp ' . number_format($this->sisa_kompensasi_tersedia, 0, ',', '.');
    }

    /**
     * Check methods
     */
    public function isLebihBayar(): bool
    {
        return ($this->status_final ?? $this->status) === 'Lebih Bayar';
    }

    public function isKurangBayar(): bool
    {
        return ($this->status_final ?? $this->status) === 'Kurang Bayar';
    }

    public function isNihil(): bool
    {
        return ($this->status_final ?? $this->status) === 'Nihil';
    }

    public function canBeCompensationSource(): bool
    {
        return $this->isLebihBayar() && $this->sisa_kompensasi_tersedia > 0;
    }

    public function canReceiveCompensation(): bool
    {
        return $this->isKurangBayar();
    }

    /**
     * Recalculate summary
     */
    public function recalculate(): self
    {
        // Get data berdasarkan tax_type
        $data = $this->calculateFromSource();
        
        // Update summary
        $this->fill($data);
        $this->calculated_at = now();
        $this->calculated_by = auth()->id();
        $this->save();
        
        return $this->fresh();
    }

    /**
     * Calculate from source data (invoices, income_taxes, bupots)
     */
    protected function calculateFromSource(): array
    {
        $taxReport = $this->taxReport;
        
        return match($this->tax_type) {
            'ppn' => $this->calculatePpn($taxReport),
            'pph' => $this->calculatePph($taxReport),
            'bupot' => $this->calculateBupot($taxReport),
            default => []
        };
    }

    /**
     * Calculate PPN
     */
    protected function calculatePpn($taxReport): array
    {
        $pajakMasuk = $taxReport->originalInvoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
        
        $pajakKeluar = $taxReport->originalInvoices()
            ->where('type', 'Faktur Keluaran')
            ->sum('ppn');
        
        $selisih = $pajakKeluar - $pajakMasuk;
        
        // Hitung kompensasi yang diterima (approved)
        $kompensasiDiterima = $taxReport->approvedCompensationsReceived()
            ->where('tax_type', 'ppn')
            ->sum('amount_compensated');
        
        // Hitung kompensasi yang sudah terpakai
        $kompensasiTerpakai = $taxReport->approvedCompensationsGiven()
            ->where('tax_type', 'ppn')
            ->sum('amount_compensated');
        
        // Saldo final setelah kompensasi
        $saldoFinal = $selisih - $kompensasiDiterima;
        
        // Status awal (sebelum kompensasi)
        $status = $this->determineStatus($selisih);
        
        // Status final (setelah kompensasi)
        $statusFinal = $this->determineStatus($saldoFinal);
        
        // Kompensasi tersedia (jika lebih bayar)
        $kompensasiTersedia = $statusFinal === 'Lebih Bayar' ? abs($saldoFinal) : 0;
        
        return [
            'pajak_masuk' => $pajakMasuk,
            'pajak_keluar' => $pajakKeluar,
            'selisih' => $selisih,
            'status' => $status,
            'kompensasi_diterima' => $kompensasiDiterima,
            'kompensasi_tersedia' => $kompensasiTersedia,
            'kompensasi_terpakai' => $kompensasiTerpakai,
            'saldo_final' => $saldoFinal,
            'status_final' => $statusFinal,
        ];
    }

    /**
     * Calculate PPh
     */
    protected function calculatePph($taxReport): array
    {
        // TODO: Implement berdasarkan IncomeTax model
        $pajakTerutang = $taxReport->incomeTaxs()->sum('amount');
        $pajakDipotong = 0; // Jika ada withholding
        
        $selisih = $pajakTerutang - $pajakDipotong;
        
        // Similar logic dengan PPN
        $kompensasiDiterima = $taxReport->approvedCompensationsReceived()
            ->where('tax_type', 'pph')
            ->sum('amount_compensated');
        
        $kompensasiTerpakai = $taxReport->approvedCompensationsGiven()
            ->where('tax_type', 'pph')
            ->sum('amount_compensated');
        
        $saldoFinal = $selisih - $kompensasiDiterima;
        $status = $this->determineStatus($selisih);
        $statusFinal = $this->determineStatus($saldoFinal);
        $kompensasiTersedia = $statusFinal === 'Lebih Bayar' ? abs($saldoFinal) : 0;
        
        return [
            'pajak_masuk' => $pajakDipotong,
            'pajak_keluar' => $pajakTerutang,
            'selisih' => $selisih,
            'status' => $status,
            'kompensasi_diterima' => $kompensasiDiterima,
            'kompensasi_tersedia' => $kompensasiTersedia,
            'kompensasi_terpakai' => $kompensasiTerpakai,
            'saldo_final' => $saldoFinal,
            'status_final' => $statusFinal,
        ];
    }

    /**
     * Calculate Bupot
     */
    protected function calculateBupot($taxReport): array
    {
        // TODO: Implement berdasarkan Bupot model
        $pajakTerutang = $taxReport->bupots()->sum('jumlah_pph');
        $pajakDipotong = 0;
        
        $selisih = $pajakTerutang - $pajakDipotong;
        
        // Similar logic
        $kompensasiDiterima = $taxReport->approvedCompensationsReceived()
            ->where('tax_type', 'bupot')
            ->sum('amount_compensated');
        
        $kompensasiTerpakai = $taxReport->approvedCompensationsGiven()
            ->where('tax_type', 'bupot')
            ->sum('amount_compensated');
        
        $saldoFinal = $selisih - $kompensasiDiterima;
        $status = $this->determineStatus($selisih);
        $statusFinal = $this->determineStatus($saldoFinal);
        $kompensasiTersedia = $statusFinal === 'Lebih Bayar' ? abs($saldoFinal) : 0;
        
        return [
            'pajak_masuk' => $pajakDipotong,
            'pajak_keluar' => $pajakTerutang,
            'selisih' => $selisih,
            'status' => $status,
            'kompensasi_diterima' => $kompensasiDiterima,
            'kompensasi_tersedia' => $kompensasiTersedia,
            'kompensasi_terpakai' => $kompensasiTerpakai,
            'saldo_final' => $saldoFinal,
            'status_final' => $statusFinal,
        ];
    }

    /**
     * Determine status berdasarkan amount
     */
    protected function determineStatus(float $amount): string
    {
        if ($amount > 0) {
            return 'Kurang Bayar';
        } elseif ($amount < 0) {
            return 'Lebih Bayar';
        }
        return 'Nihil';
    }

    /**
     * Auto-recalculate on boot
     */
    protected static function booted()
    {
        // Auto-set calculated info
        static::creating(function ($summary) {
            if (!$summary->calculated_at) {
                $summary->calculated_at = now();
            }
            if (!$summary->calculated_by && auth()->check()) {
                $summary->calculated_by = auth()->id();
            }
        });
    }
}