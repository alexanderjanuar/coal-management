<?php

namespace App\Console\Commands;

use App\Models\TaxCompensation;
use App\Models\TaxCalculationSummary;
use App\Models\TaxReport;
use Illuminate\Console\Command;

class RecalculateJanuaryCompensations extends Command
{
    protected $signature = 'compensations:recalculate-january
                            {--dry-run : Preview affected reports without recalculating}';

    protected $description = 'Recalculate tax summaries for January reports affected by the December compensation target fix.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN — no data will be recalculated.');
        }

        // Find all January reports that are targets of December compensations
        // These are the ones that were just re-pointed by the fix command
        $compensations = TaxCompensation::with(['sourceTaxReport.client', 'targetTaxReport.client'])
            ->whereHas('sourceTaxReport', function ($q) {
                $q->where('month', 'December');
            })
            ->whereHas('targetTaxReport', function ($q) {
                $q->where('month', 'January');
            })
            ->get();

        if ($compensations->isEmpty()) {
            $this->info('No December → January compensations found.');
            return self::SUCCESS;
        }

        // Collect unique target report IDs (January reports) to recalculate
        // Also collect the old January reports (same year as December) that lost their compensation
        $targetReportIds = $compensations->pluck('target_tax_report_id')->unique();

        // Also recalculate the old wrong January reports (same year as source)
        // They may still have stale kompensasi_diterima values
        $oldJanuaryIds = collect();
        foreach ($compensations as $compensation) {
            $source = $compensation->sourceTaxReport;
            $sourceYear = (int) $source->year;

            // Find the old wrong January report (same year as December source)
            $oldJanuary = TaxReport::where('client_id', $source->client_id)
                ->where('month', 'January')
                ->where('year', $sourceYear)
                ->first();

            if ($oldJanuary && !$targetReportIds->contains($oldJanuary->id)) {
                $oldJanuaryIds->push($oldJanuary->id);
            }
        }

        $allReportIds = $targetReportIds->merge($oldJanuaryIds)->unique();

        $this->info("Found {$allReportIds->count()} January report(s) to recalculate.");
        $this->newLine();

        $recalculated = 0;

        foreach ($allReportIds as $reportId) {
            $taxReport = TaxReport::with('client')->find($reportId);

            if (!$taxReport) {
                $this->warn("  SKIP Report #{$reportId}: Tax report not found.");
                continue;
            }

            $clientName = $taxReport->client->name ?? "Client #{$taxReport->client_id}";

            // Use getOrCreateSummary to ensure a PPN summary exists
            // (January reports with 0 invoices may never have had one created)
            $summary = $taxReport->getOrCreateSummary('ppn');

            $oldStatus = $summary->status_final;
            $oldSaldo = $summary->saldo_final;
            $oldKompensasi = $summary->kompensasi_diterima;

            if (!$isDryRun) {
                $summary->recalculate();
                $summary->refresh();
            }

            $newStatus = $isDryRun ? '(pending)' : $summary->status_final;
            $newSaldo = $isDryRun ? '(pending)' : number_format($summary->saldo_final, 0, ',', '.');
            $newKompensasi = $isDryRun ? '(pending)' : number_format($summary->kompensasi_diterima, 0, ',', '.');

            $this->info("  RECALC Report #{$reportId} [ppn] | {$clientName} | Jan {$taxReport->year}");
            $this->line("         Kompensasi Diterima: " . number_format($oldKompensasi, 0, ',', '.') . " → {$newKompensasi}");
            $this->line("         Saldo Final: " . number_format($oldSaldo, 0, ',', '.') . " → {$newSaldo}");
            $this->line("         Status: {$oldStatus} → {$newStatus}");
            $this->newLine();

            $recalculated++;
        }

        $this->info("Done. Recalculated: {$recalculated} summaries.");

        if ($isDryRun && $recalculated > 0) {
            $this->warn('Run without --dry-run to apply recalculations.');
        }

        return self::SUCCESS;
    }
}
