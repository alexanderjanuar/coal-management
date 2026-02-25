<?php

namespace App\Console\Commands;

use App\Models\TaxCompensation;
use App\Models\TaxReport;
use Illuminate\Console\Command;

class FixDecemberCompensationTargets extends Command
{
    protected $signature = 'compensations:fix-december-targets
                            {--dry-run : Preview what would be changed without writing to DB}';

    protected $description = 'Fix December compensations that incorrectly target January of the same year instead of January of the next year.';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN — no data will be written.');
        }

        // Find all compensations where the source is a December report
        // and the target is a January report of the SAME year (the bug)
        $compensations = TaxCompensation::with(['sourceTaxReport', 'targetTaxReport'])
            ->whereHas('sourceTaxReport', function ($q) {
                $q->where('month', 'December');
            })
            ->whereHas('targetTaxReport', function ($q) {
                $q->where('month', 'January');
            })
            ->get();

        $fixed = 0;
        $skipped = 0;

        foreach ($compensations as $compensation) {
            $source = $compensation->sourceTaxReport;
            $target = $compensation->targetTaxReport;

            // Only fix if source and target are in the same year (the bug scenario)
            if ((int) $source->year !== (int) $target->year) {
                $this->line("  SKIP #{$compensation->id}: already correct — Dec {$source->year} → Jan {$target->year}");
                $skipped++;
                continue;
            }

            $correctYear = (int) $source->year + 1;

            // Find the correct January report for the next year
            $correctTarget = TaxReport::where('client_id', $source->client_id)
                ->where('month', 'January')
                ->where('year', $correctYear)
                ->first();

            if (!$correctTarget) {
                $this->error("  SKIP #{$compensation->id}: No January {$correctYear} report found for client #{$source->client_id}. Create the report first.");
                $skipped++;
                continue;
            }

            $this->info("  FIX  #{$compensation->id}: Dec {$source->year} → Jan {$target->year} (wrong) → Jan {$correctYear} (correct) | Client #{$source->client_id} | Amount: {$compensation->amount_compensated} | Status: {$compensation->status}");

            if (!$isDryRun) {
                $compensation->update([
                    'target_tax_report_id' => $correctTarget->id,
                ]);
            }

            $fixed++;
        }

        $this->newLine();
        $this->info("Done. Fixed: {$fixed}, Skipped: {$skipped}");

        if ($isDryRun && $fixed > 0) {
            $this->warn('Run without --dry-run to apply changes.');
        }

        return self::SUCCESS;
    }
}
