<?php

namespace App\Observers;

use App\Models\TaxReport;

class TaxReportObserver
{
    /**
     * Handle the TaxReport "saved" event.
     */
    public function saved(TaxReport $taxReport)
    {
        $this->updateTaxStatus($taxReport);
    }

    /**
     * Handle the TaxReport "created" event.
     */
    public function created(TaxReport $taxReport): void
    {
        // Ensure all three tax calculation summaries are created
        $this->updateTaxStatus($taxReport);
    }

    /**
     * Handle the TaxReport "updated" event.
     */
    public function updated(TaxReport $taxReport): void
    {
        //
    }

    /**
     * Handle the TaxReport "deleted" event.
     */
    public function deleted(TaxReport $taxReport): void
    {
        //
    }

    /**
     * Handle the TaxReport "restored" event.
     */
    public function restored(TaxReport $taxReport): void
    {
        //
    }

    /**
     * Handle the TaxReport "force deleted" event.
     */
    public function forceDeleted(TaxReport $taxReport): void
    {
        //
    }

    /**
     * Update tax calculation summaries for all tax types
     */
    private function updateTaxStatus(TaxReport $taxReport)
    {
        // Get or create PPN summary
        $ppnSummary = $taxReport->taxCalculationSummaries()->firstOrCreate(
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

        // Recalculate the PPN summary (this preserves manual_kompensasi)
        $ppnSummary->recalculate();

        // Get or create PPh summary
        $pphSummary = $taxReport->taxCalculationSummaries()->firstOrCreate(
            ['tax_type' => 'pph'],
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

        // Recalculate the PPh summary (this preserves manual_kompensasi)
        $pphSummary->recalculate();

        // Get or create Bupot summary
        $bupotSummary = $taxReport->taxCalculationSummaries()->firstOrCreate(
            ['tax_type' => 'bupot'],
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

        // Recalculate the Bupot summary (this preserves manual_kompensasi)
        $bupotSummary->recalculate();
    }
}