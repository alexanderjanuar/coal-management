<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrate existing data dari kolom lama di tax_reports ke tax_calculation_summaries
     */
    public function up(): void
    {
        // Migrate existing data
        TaxReport::chunk(100, function ($reports) {
            foreach ($reports as $report) {
                // Create PPN summary dari data yang ada
                $report->calculationSummaries()->updateOrCreate(
                    ['tax_type' => 'ppn'],
                    [
                        'pajak_masuk' => $report->getTotalPpnMasuk(),
                        'pajak_keluar' => $report->getTotalPpnKeluar(),
                        'selisih' => $report->getSelisihPpn(),
                        'status' => $report->invoice_tax_status ?? 'Nihil',
                        'kompensasi_diterima' => $report->ppn_dikompensasi_dari_masa_sebelumnya ?? 0,
                        'kompensasi_tersedia' => $report->ppn_lebih_bayar_dibawa_ke_masa_depan ?? 0,
                        'kompensasi_terpakai' => $report->ppn_sudah_dikompensasi ?? 0,
                        'saldo_final' => $report->getSelisihPpn() - ($report->ppn_dikompensasi_dari_masa_sebelumnya ?? 0),
                        'status_final' => $report->invoice_tax_status ?? 'Nihil',
                        'notes' => $report->kompensasi_notes,
                        'calculated_at' => now(),
                    ]
                );
                
                // Create PPh summary (initial empty)
                $report->calculationSummaries()->updateOrCreate(
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
                        'calculated_at' => now(),
                    ]
                );
                
                // Create Bupot summary (initial empty)
                $report->calculationSummaries()->updateOrCreate(
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
                        'calculated_at' => now(),
                    ]
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally restore data back to tax_reports
        TaxReport::chunk(100, function ($reports) {
            foreach ($reports as $report) {
                $ppnSummary = $report->ppnSummary;
                if ($ppnSummary) {
                    $report->update([
                        'invoice_tax_status' => $ppnSummary->status_final,
                        'ppn_dikompensasi_dari_masa_sebelumnya' => $ppnSummary->kompensasi_diterima,
                        'ppn_lebih_bayar_dibawa_ke_masa_depan' => $ppnSummary->kompensasi_tersedia,
                        'ppn_sudah_dikompensasi' => $ppnSummary->kompensasi_terpakai,
                        'kompensasi_notes' => $ppnSummary->notes,
                    ]);
                }
            }
        });
    }
};