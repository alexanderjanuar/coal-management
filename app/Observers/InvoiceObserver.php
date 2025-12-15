<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $this->updateTaxReportStatus($invoice);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        $this->updateTaxReportStatus($invoice);
        
        // If tax_report_id changed, update both old and new tax reports
        if ($invoice->isDirty('tax_report_id') && $invoice->getOriginal('tax_report_id')) {
            $oldTaxReport = \App\Models\TaxReport::find($invoice->getOriginal('tax_report_id'));
            if ($oldTaxReport) {
                $this->calculateAndUpdateStatus($oldTaxReport);
            }
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        $this->updateTaxReportStatus($invoice);
    }

    /**
     * Update tax report status based on invoice changes
     */
    private function updateTaxReportStatus(Invoice $invoice): void
    {
        if ($invoice->taxReport) {
            $this->calculateAndUpdateStatus($invoice->taxReport);
        }
    }

    /**
     * Calculate and update tax report status using the new tax_calculation_summaries structure
     */
    private function calculateAndUpdateStatus($taxReport): void
    {
        try {
            // Get or create PPN summary
            $ppnSummary = $taxReport->taxCalculationSummaries()
                ->firstOrCreate(
                    ['tax_type' => 'ppn'],
                    [
                        'pajak_masuk' => 0,
                        'pajak_keluar' => 0,
                        'selisih' => 0,
                        'status' => 'Nihil',
                        'kompensasi_diterima' => 0,
                        'kompensasi_tersedia' => 0,
                        'kompensasi_terpakai' => 0,
                        'manual_kompensasi' => 0,
                        'manual_kompensasi_notes' => null,
                        'saldo_final' => 0,
                        'status_final' => 'Nihil',
                        'report_status' => 'Belum Lapor',
                    ]
                );

            // Calculate PPN from invoices (excluding revisions)
            $totalPpnMasuk = $taxReport->originalInvoices()
                ->where('type', 'Faktur Masuk')
                ->sum('ppn');
            
            // Calculate PPN from Faktur Keluaran (excluding certain prefixes and revisions)
            $excludedPrefixes = ['02', '03', '07', '08'];
            $totalPpnKeluar = $taxReport->originalInvoices()
                ->where('type', 'Faktur Keluaran')
                ->where(function($query) use ($excludedPrefixes) {
                    foreach ($excludedPrefixes as $prefix) {
                        $query->where('invoice_number', 'not like', $prefix . '%');
                    }
                })
                ->sum('ppn');
            
            // Calculate difference (Keluar - Masuk)
            $selisihPpn = $totalPpnKeluar - $totalPpnMasuk;
            
            // Get approved compensations received
            $kompensasiDiterima = $taxReport->approvedCompensationsReceived()
                ->where('tax_type', 'ppn')
                ->sum('amount_compensated');
            
            // Get approved compensations given
            $kompensasiTerpakai = $taxReport->approvedCompensationsGiven()
                ->where('tax_type', 'ppn')
                ->sum('amount_compensated');
            
            // Preserve existing manual kompensasi value
            $manualKompensasi = $ppnSummary->manual_kompensasi ?? 0;
            
            // Calculate final balance after compensation (including manual kompensasi)
            $saldoFinal = $selisihPpn - $kompensasiDiterima - $manualKompensasi;
            
            // Determine status before compensation
            $status = $this->determineStatus($selisihPpn);
            
            // Determine status after compensation
            $statusFinal = $this->determineStatus($saldoFinal);
            
            // Calculate available compensation (if overpayment)
            $kompensasiTersedia = $statusFinal === 'Lebih Bayar' ? abs($saldoFinal) : 0;
            
            // Update PPN summary (preserve manual_kompensasi and manual_kompensasi_notes)
            $ppnSummary->update([
                'pajak_masuk' => $totalPpnMasuk,
                'pajak_keluar' => $totalPpnKeluar,
                'selisih' => $selisihPpn,
                'status' => $status,
                'kompensasi_diterima' => $kompensasiDiterima,
                'kompensasi_tersedia' => $kompensasiTersedia,
                'kompensasi_terpakai' => $kompensasiTerpakai,
                'manual_kompensasi' => $manualKompensasi, // Preserve existing value
                // manual_kompensasi_notes is intentionally not updated here to preserve it
                'saldo_final' => $saldoFinal,
                'status_final' => $statusFinal,
                'calculated_at' => now(),
                'calculated_by' => auth()->id(),
            ]);
            
            // Optional: Log the calculation for debugging
            \Log::info("Tax Report PPN Summary Updated", [
                'tax_report_id' => $taxReport->id,
                'total_ppn_masuk' => $totalPpnMasuk,
                'total_ppn_keluar' => $totalPpnKeluar,
                'selisih_ppn' => $selisihPpn,
                'kompensasi_diterima' => $kompensasiDiterima,
                'manual_kompensasi' => $manualKompensasi,
                'saldo_final' => $saldoFinal,
                'status' => $status,
                'status_final' => $statusFinal
            ]);
            
        } catch (\Exception $e) {
            // Log error but don't break the invoice operation
            \Log::error('Failed to update tax report summary for invoice change', [
                'tax_report_id' => $taxReport->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Determine status based on amount
     */
    private function determineStatus(float $amount): string
    {
        if ($amount > 0) {
            return 'Kurang Bayar';
        } elseif ($amount < 0) {
            return 'Lebih Bayar';
        }
        return 'Nihil';
    }
}