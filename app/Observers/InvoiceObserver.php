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
     * Calculate and update tax report status
     */
    private function calculateAndUpdateStatus($taxReport): void
    {
        // Get total PPN from Faktur Masuk (input VAT)
        $totalPpnMasuk = $taxReport->invoices()
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
        
        // Get total PPN from Faktur Keluar (output VAT)
        $totalPpnKeluar = $taxReport->invoices()
            ->where('type', 'Faktur Keluaran')
            ->sum('ppn');
        
        // Calculate difference (Keluar - Masuk)
        $selisihPpn = $totalPpnKeluar - $totalPpnMasuk;
        
        // Determine status
        $invoiceTaxStatus = 'Nihil';
        if ($selisihPpn > 0) {
            $invoiceTaxStatus = 'Kurang Bayar'; // Need to pay more (output > input)
        } elseif ($selisihPpn < 0) {
            $invoiceTaxStatus = 'Lebih Bayar'; // Overpaid (input > output)
        }
        
        // Update only the invoice_tax_status column
        $taxReport->update([
            'invoice_tax_status' => $invoiceTaxStatus
        ]);
        
        // Optional: Log the calculation for debugging
        \Log::info("Tax Report Status Updated", [
            'tax_report_id' => $taxReport->id,
            'total_ppn_masuk' => $totalPpnMasuk,
            'total_ppn_keluar' => $totalPpnKeluar,
            'selisih_ppn' => $selisihPpn,
            'status' => $invoiceTaxStatus
        ]);
    }
}