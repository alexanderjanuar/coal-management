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
        $calculation = $taxReport->calculateFinalTaxStatus();
        
        // Prepare update data
        $updateData = [
            'invoice_tax_status' => $calculation['status'] // Will be 'Lebih Bayar', 'Kurang Bayar', or 'Nihil'
        ];

        
        // If lebih bayar, set amount available for future compensation
        if ($calculation['status'] === 'Lebih Bayar') {
            $updateData['ppn_lebih_bayar_dibawa_ke_masa_depan'] = abs($calculation['final_amount']);
        } else {
            $updateData['ppn_lebih_bayar_dibawa_ke_masa_depan'] = 0;
        }
        
        
        // Update without triggering events to avoid infinite loop
        $taxReport->updateQuietly($updateData);
    }

    /**
     * Handle the TaxReport "created" event.
     */
    public function created(TaxReport $taxReport): void
    {
        //
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
