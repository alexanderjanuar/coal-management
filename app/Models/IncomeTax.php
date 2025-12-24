<?php

namespace App\Models;

use App\Traits\Trackable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class IncomeTax extends Model
{
    use HasFactory, LogsActivity, Trackable;

    protected $fillable = [
        // Core fields
        'tax_report_id',
        'employee_id',                     // Required - Links to Employee (has: name, npwp, salary/DPP)
        
        // DJP PPh Fields (based on CSV export structure)
        // Note: nama, npwp, and dasar_pengenaan_pajak can be synced from Employee table
        'masa_pajak',                      // Tax Period (e.g., "06062025")
        'nomor_pemotongan',                // Withholding Number (e.g., "2503FY3OF")
        'status',                          // Status (e.g., "NORMAL")
        'nitku',                           // NITKU/Sub Unit Organization ID
        'jenis_pajak',                     // Tax Type (e.g., "Pasal 21", "Pasal 23", "Pasal 4(2)")
        'kode_objek_pajak',                // Tax Object Code (e.g., "21-100-01")
        'npwp',                            // Tax ID Number
        'nama',                            // Recipient Name
        'dasar_pengenaan_pajak',           // Tax Base Amount (DPP)
        'pajak_penghasilan',               // Income Tax Amount
        'fasilitas_pajak',                 // Tax Facility (e.g., "Tanpa Fasilitas")
        'dilaporkan_dalam_spt',            // Reported in SPT (boolean)
        'spt_sedang_diperiksa',            // SPT Being Audited (boolean)
        'spt_dalam_penanganan_hukum',      // SPT in Legal Handling (boolean)
        'bukti_potong',                    // Withholding certificate file path
        'notes',
        'created_by'
    ];

    protected $casts = [
        'dasar_pengenaan_pajak' => 'decimal:2',
        'pajak_penghasilan' => 'decimal:2',
        'ter_amount' => 'decimal:2',
        'pph_21_amount' => 'decimal:2',
        'dilaporkan_dalam_spt' => 'boolean',
        'spt_sedang_diperiksa' => 'boolean',
        'spt_dalam_penanganan_hukum' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function taxReport()
    {
        return $this->belongsTo(TaxReport::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors & Mutators
     */
    
    /**
     * Get formatted tax period (e.g., "Juni 2025")
     */
    public function getFormattedMasaPajakAttribute()
    {
        if (!$this->masa_pajak) {
            return null;
        }
        
        // Parse date format: MMDDYYYY (e.g., "06062025")
        try {
            $month = substr($this->masa_pajak, 0, 2);
            $year = substr($this->masa_pajak, 4, 4);
            
            $monthNames = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            ];
            
            return ($monthNames[$month] ?? $month) . ' ' . $year;
        } catch (\Exception $e) {
            return $this->masa_pajak;
        }
    }

    /**
     * Get PPh type in Indonesian
     */
    public function getPphTypeDisplayAttribute()
    {
        return match($this->jenis_pajak) {
            'Pasal 21' => 'PPh 21 - Penghasilan Karyawan',
            'Pasal 23' => 'PPh 23 - Dividen, Bunga, Royalti',
            'Pasal 4(2)' => 'PPh 4(2) - Pajak Final',
            'Pasal 4 ayat 2' => 'PPh 4(2) - Pajak Final',
            default => $this->jenis_pajak ?? 'PPh',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match(strtoupper($this->status ?? 'NORMAL')) {
            'NORMAL' => 'success',
            'PEMBETULAN' => 'warning',
            'PEMBATALAN' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Check if this is PPh 21
     */
    public function isPph21()
    {
        return $this->jenis_pajak === 'Pasal 21';
    }

    /**
     * Check if this is PPh 23
     */
    public function isPph23()
    {
        return $this->jenis_pajak === 'Pasal 23';
    }

    /**
     * Check if this is PPh 4(2)
     */
    public function isPph42()
    {
        return in_array($this->jenis_pajak, ['Pasal 4(2)', 'Pasal 4 ayat 2']);
    }

    /**
     * Get employee data (nama, npwp, DPP from salary)
     * Returns array with employee information
     */
    public function getEmployeeDataAttribute()
    {
        if (!$this->employee) {
            return [
                'nama' => $this->nama,
                'npwp' => $this->npwp,
                'dpp' => $this->dasar_pengenaan_pajak,
            ];
        }

        return [
            'nama' => $this->employee->name,
            'npwp' => $this->employee->npwp,
            'dpp' => $this->employee->salary,
        ];
    }

    /**
     * Sync data from employee to income tax record
     * This will update nama, npwp, and dasar_pengenaan_pajak from employee
     */
    public function syncFromEmployee()
    {
        if (!$this->employee) {
            return false;
        }

        $this->nama = $this->employee->name;
        $this->npwp = $this->employee->npwp ?? '9990000000999000'; // Default if no NPWP
        $this->dasar_pengenaan_pajak = $this->employee->salary ?? 0;

        return true;
    }

    /**
     * Auto-populate fields from employee on save if not set
     */
    public function autoPopulateFromEmployee()
    {
        if (!$this->employee) {
            return;
        }

        // Only populate if fields are empty
        if (empty($this->nama)) {
            $this->nama = $this->employee->name;
        }

        if (empty($this->npwp)) {
            $this->npwp = $this->employee->npwp ?? '9990000000999000';
        }

        if (empty($this->dasar_pengenaan_pajak) || $this->dasar_pengenaan_pajak == 0) {
            $this->dasar_pengenaan_pajak = $this->employee->salary ?? 0;
        }
    }

    /**
     * Scopes
     */
    
    /**
     * Scope: Filter by tax type
     */
    public function scopeByJenisPajak($query, $jenisPajak)
    {
        return $query->where('jenis_pajak', $jenisPajak);
    }

    /**
     * Scope: Filter by tax period
     */
    public function scopeByMasaPajak($query, $masaPajak)
    {
        return $query->where('masa_pajak', $masaPajak);
    }

    /**
     * Scope: Only normal status (exclude corrections/cancellations)
     */
    public function scopeNormalStatus($query)
    {
        return $query->where('status', 'NORMAL');
    }

    /**
     * Scope: Reported in SPT
     */
    public function scopeReported($query)
    {
        return $query->where('dilaporkan_dalam_spt', true);
    }

    /**
     * Model Events
     */
    protected static function booted()
    {
        // Auto-populate from employee before creating
        static::creating(function ($incomeTax) {
            $incomeTax->autoPopulateFromEmployee();
        });

        static::created(function ($incomeTax) {
            $recipientName = $incomeTax->nama ?? $incomeTax->employee?->name ?? 'Penerima';
            $taxType = $incomeTax->jenis_pajak ?? 'PPh';
            
            $incomeTax->logActivity(
                'income_tax_created',
                "{$taxType} untuk {$recipientName} telah dibuat (DPP: Rp " . number_format($incomeTax->dasar_pengenaan_pajak, 0, ',', '.') . 
                ", PPh: Rp " . number_format($incomeTax->pajak_penghasilan, 0, ',', '.') . ")"
            );

            // AUTO-RECALCULATE: Update PPh summary when income tax created
            $incomeTax->recalculateTaxSummary();
        });

        static::updated(function ($incomeTax) {
            $recipientName = $incomeTax->nama ?? $incomeTax->employee?->name ?? 'Penerima';
            
            if ($incomeTax->wasChanged(['dasar_pengenaan_pajak', 'pajak_penghasilan'])) {
                $incomeTax->logActivity(
                    'income_tax_amount_updated',
                    "Jumlah PPh untuk {$recipientName} telah diperbarui"
                );

                // AUTO-RECALCULATE: Update PPh summary when amount changed
                $incomeTax->recalculateTaxSummary();
            }

            if ($incomeTax->wasChanged('bukti_potong')) {
                $incomeTax->logActivity(
                    'income_tax_bukti_potong_uploaded',
                    "Bukti potong PPh untuk {$recipientName} telah diunggah"
                );
            }
        });

        static::deleted(function ($incomeTax) {
            $recipientName = $incomeTax->nama ?? $incomeTax->employee?->name ?? 'Penerima';
            $incomeTax->logActivity(
                'income_tax_deleted',
                "PPh untuk {$recipientName} telah dihapus"
            );

            // AUTO-RECALCULATE: Update PPh summary when income tax deleted
            $incomeTax->recalculateTaxSummary();
        });
    }

    /**
     * Recalculate PPh tax summary for this income tax's tax report
     */
    protected function recalculateTaxSummary()
    {
        try {
            if ($this->taxReport) {
                // Get or create PPh summary
                $pphSummary = $this->taxReport->taxCalculationSummaries()
                    ->firstOrCreate(
                        ['tax_type' => 'pph'],
                        [
                            'pajak_masuk' => 0,
                            'pajak_keluar' => 0,
                            'selisih' => 0,
                            'status' => 'Nihil',
                            'kompensasi_diterima' => 0,
                            'kompensasi_tersedia' => 0,
                            'kompensasi_terpakai' => 0,
                            'saldo_final' => 0,
                            'status_final' => 'Nihil',
                            'report_status' => 'Belum Lapor',
                        ]
                    );

                // Recalculate using TaxCalculationSummary's recalculate method
                $pphSummary->recalculate();
            }
        } catch (\Exception $e) {
            // Log error but don't break the income tax operation
            \Log::error('Failed to recalculate PPh summary for income tax ' . $this->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Spatie ActivityLog (detailed audit)
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'tax_report_id',
                'employee_id',
                'masa_pajak',
                'nomor_pemotongan',
                'jenis_pajak',
                'npwp',
                'nama',
                'dasar_pengenaan_pajak',
                'pajak_penghasilan',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function(string $eventName) {
                $clientName = $this->taxReport?->client?->name ?? 'Klien';
                $recipientName = $this->nama ?? $this->employee?->name ?? 'Penerima';
                $taxPeriod = $this->taxReport?->month ?? 'Periode';
                $userName = auth()->user()?->name ?? 'System';
                $taxType = $this->jenis_pajak ?? 'PPh';
                
                return match($eventName) {
                    'created' => "[{$clientName}] 💰 {$taxType} BARU: {$recipientName} - {$taxPeriod} | Dibuat oleh: {$userName}",
                    'updated' => "[{$clientName}] 🔄 DIPERBARUI: {$taxType} {$recipientName} - {$taxPeriod} | Diperbarui oleh: {$userName}",
                    'deleted' => "[{$clientName}] 🗑️ DIHAPUS: {$taxType} {$recipientName} - {$taxPeriod} | Dihapus oleh: {$userName}",
                    default => "[{$clientName}] {$taxType} {$recipientName} - {$taxPeriod} telah {$eventName} oleh {$userName}"
                };
            });
    }
}