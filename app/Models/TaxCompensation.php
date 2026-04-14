<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TaxCompensation extends Model
{
    use HasFactory;

    protected $table = 'tax_compensations';

    protected $fillable = [
        'source_tax_report_id',
        'target_tax_report_id',
        'tax_type',
        'amount_compensated',
        'status',
        'type',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'rejection_reason'
    ];

    protected $casts = [
        'amount_compensated' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function sourceTaxReport()
    {
        return $this->belongsTo(TaxReport::class, 'source_tax_report_id');
    }

    public function targetTaxReport()
    {
        return $this->belongsTo(TaxReport::class, 'target_tax_report_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

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

    /**
     * Accessors
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount_compensated, 0, ',', '.');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui'
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'auto' => 'Otomatis',
            'manual' => 'Manual',
            default => 'Manual'
        };
    }

    public function getTaxTypeNameAttribute(): string
    {
        return match($this->tax_type) {
            'ppn' => 'PPN',
            'pph' => 'PPh',
            'bupot' => 'PPh Unifikasi',
            default => 'Unknown'
        };
    }

    /**
     * Check if compensation can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if compensation can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if compensation can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    /**
     * Approve compensation
     */
    public function approve(int $userId): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $updated = $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        if ($updated) {
            // Recalculate source and direct target first
            $this->sourceTaxReport->getOrCreateSummary($this->tax_type)->recalculate();
            $this->targetTaxReport->getOrCreateSummary($this->tax_type)->recalculate();

            // Cascade forward: recalculate every month after the target
            // so the whole chain stays consistent
            $this->targetTaxReport->cascadeRecalculate();
        }

        return $updated;
    }

    /**
     * Reject compensation
     */
    public function reject(int $userId, string $reason): bool
    {
        if (!$this->canBeApproved()) {
            return false;
        }

        $updated = $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        if ($updated) {
            // Recalculate source and direct target first
            $this->sourceTaxReport->getOrCreateSummary($this->tax_type)->recalculate();
            $this->targetTaxReport->getOrCreateSummary($this->tax_type)->recalculate();

            // Cascade forward from the target month onward
            $this->targetTaxReport->cascadeRecalculate();
        }

        return $updated;
    }

    /**
     * Cancel compensation
     */
    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $updated = $this->update(['status' => 'cancelled']);

        if ($updated) {
            // Recalculate source and direct target first
            $this->sourceTaxReport->getOrCreateSummary($this->tax_type)->recalculate();
            $this->targetTaxReport->getOrCreateSummary($this->tax_type)->recalculate();

            // Cascade forward from the target month onward
            $this->targetTaxReport->cascadeRecalculate();
        }

        return $updated;
    }

    /**
     * Revise an approved compensation to a new amount.
     *
     * Atomically:
     *   1. Cancels this record (audit trail kept, cascade runs)
     *   2. Creates a new compensation for the same source → target with
     *      the corrected amount
     *   3. Immediately approves the new one (cascade runs again)
     *
     * Returns the new TaxCompensation on success, or throws on failure.
     *
     * Only call this when the compensation is already approved and the
     * source surplus has changed (e.g. a new invoice was added).
     */
    public function revise(float $newAmount, int $userId, ?string $notes = null): self
    {
        if (!$this->canBeCancelled()) {
            throw new \RuntimeException(
                "Compensation #{$this->id} cannot be revised (status: {$this->status})."
            );
        }

        if ($newAmount <= 0) {
            throw new \InvalidArgumentException('Revised amount must be greater than zero.');
        }

        return DB::transaction(function () use ($newAmount, $userId, $notes) {
            // Step 1 — cancel the existing record; this recalculates source,
            // target, and every month after the target automatically.
            $this->cancel();

            // Step 2 — create a replacement with the updated amount.
            /** @var self $replacement */
            $replacement = self::create([
                'source_tax_report_id' => $this->source_tax_report_id,
                'target_tax_report_id' => $this->target_tax_report_id,
                'tax_type'             => $this->tax_type,
                'amount_compensated'   => $newAmount,
                'status'               => 'pending',
                'type'                 => 'manual',
                'created_by'           => $userId,
                'notes'                => $notes ?? "Revisi dari kompensasi #{$this->id}",
            ]);

            // Step 3 — approve immediately so downstream months pick it up;
            // approve() triggers cascadeRecalculate() from the target onward.
            $replacement->approve($userId);

            return $replacement->fresh();
        });
    }

    /**
     * Boot method
     */
    protected static function booted()
    {
        // Auto-set created_by dari auth user
        static::creating(function ($compensation) {
            if (auth()->check() && !$compensation->created_by) {
                $compensation->created_by = auth()->id();
            }
        });

        // Log activity saat status berubah
        static::updated(function ($compensation) {
            if ($compensation->wasChanged('status')) {
                $message = match($compensation->status) {
                    'approved' => "Kompensasi {$compensation->tax_type_name} sebesar {$compensation->formatted_amount} dari {$compensation->sourceTaxReport->month} ke {$compensation->targetTaxReport->month} telah disetujui",
                    'rejected' => "Kompensasi {$compensation->tax_type_name} sebesar {$compensation->formatted_amount} ditolak: {$compensation->rejection_reason}",
                    'cancelled' => "Kompensasi {$compensation->tax_type_name} sebesar {$compensation->formatted_amount} dibatalkan",
                    default => null
                };

                if ($message) {
                    $compensation->targetTaxReport->logActivity(
                        'compensation_status_changed',
                        $message
                    );
                }
            }
        });
    }
}