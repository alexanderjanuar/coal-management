<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->info('Starting recalculation of tax summaries...');
        
        // Get all tax reports
        $taxReports = TaxReport::with([
            'invoices' => function($query) {
                $query->orderBy('original_invoice_id')->orderBy('revision_number', 'desc');
            }
        ])->get();
        
        $totalReports = $taxReports->count();
        $processedCount = 0;
        
        foreach ($taxReports as $taxReport) {
            $processedCount++;
            $this->info("Processing tax report {$processedCount}/{$totalReports} - ID: {$taxReport->id} - {$taxReport->client->name} - {$taxReport->month}");
            
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
                        'saldo_final' => 0,
                        'status_final' => 'Nihil',
                        'report_status' => 'Belum Lapor',
                    ]
                );
            
            // Recalculate PPN with new rules
            $ppnData = $this->calculatePpnWithNewRules($taxReport);
            $ppnSummary->update($ppnData);
            
            // NOTE: invoice_tax_status was removed in migration 2025_11_17_145138
            // Status is now tracked in tax_calculation_summaries table
            
            $this->info("  ✓ PPN Summary updated - Status: {$ppnSummary->status_final}");
        }
        
        $this->info("✓ Successfully recalculated {$processedCount} tax summaries!");
    }

    /**
     * Calculate PPN with new rules:
     * 1. Exclude Faktur Keluaran with prefixes 02, 03, 07, 08
     * 2. For invoices with revisions, only count the latest revision
     */
    protected function calculatePpnWithNewRules(TaxReport $taxReport): array
    {
        // Get all invoices for this tax report
        $allInvoices = $taxReport->invoices;
        
        // Group invoices by original_invoice_id to handle revisions
        $invoiceGroups = $allInvoices->groupBy(function($invoice) {
            // If invoice is a revision, use original_invoice_id as group key
            // If invoice is original, use its own id as group key
            return $invoice->original_invoice_id ?? $invoice->id;
        });
        
        // For each group, get only the latest version (highest revision_number)
        $validInvoices = collect();
        foreach ($invoiceGroups as $groupId => $invoices) {
            $latestInvoice = $invoices->sortByDesc('revision_number')->first();
            $validInvoices->push($latestInvoice);
        }
        
        // Calculate Pajak Masuk (all Faktur Masuk from valid invoices)
        $pajakMasuk = $validInvoices
            ->where('type', 'Faktur Masuk')
            ->sum('ppn');
        
        // Calculate Pajak Keluar (exclude prefixes 02, 03, 07, 08 from valid invoices)
        $excludedPrefixes = ['02', '03', '07', '08'];
        $pajakKeluar = $validInvoices
            ->where('type', 'Faktur Keluaran')
            ->filter(function($invoice) use ($excludedPrefixes) {
                $prefix = substr($invoice->invoice_number, 0, 2);
                return !in_array($prefix, $excludedPrefixes);
            })
            ->sum('ppn');
        
        $selisih = $pajakKeluar - $pajakMasuk;
        
        // Get approved compensations received
        $kompensasiDiterima = $taxReport->approvedCompensationsReceived()
            ->where('tax_type', 'ppn')
            ->sum('amount_compensated');
        
        // Get approved compensations given
        $kompensasiTerpakai = $taxReport->approvedCompensationsGiven()
            ->where('tax_type', 'ppn')
            ->sum('amount_compensated');
        
        // Calculate final balance after compensation
        $saldoFinal = $selisih - $kompensasiDiterima;
        
        // Determine status before compensation
        $status = $this->determineStatus($selisih);
        
        // Determine status after compensation
        $statusFinal = $this->determineStatus($saldoFinal);
        
        // Calculate available compensation (if overpayment)
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
            'calculated_at' => now(),
            'calculated_by' => 1, // System user
        ];
    }

    /**
     * Determine status based on amount
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
     * Output info message
     */
    protected function info(string $message): void
    {
        echo $message . "\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you could restore old values here
        // But since this is a data recalculation, there's no easy way to reverse
        $this->info('Reversal not supported for this migration. Please restore from backup if needed.');
    }
};