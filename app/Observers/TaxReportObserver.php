<?php

namespace App\Observers;

use App\Models\TaxReport;

class TaxReportObserver
{
    public function saved(TaxReport $taxReport)
    {
        $this->updateTaxStatus($taxReport);
    }

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
                'saldo_final' => 0,
                'status_final' => 'Nihil',
                'report_status' => 'Belum Lapor',
            ]
        );

        // Recalculate the PPN summary
        $ppnSummary->recalculate();

        // Optional: Also ensure PPh and Bupot summaries exist
        $taxReport->taxCalculationSummaries()->firstOrCreate(
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

        $taxReport->taxCalculationSummaries()->firstOrCreate(
            ['tax_type' => 'bupot'],
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
}