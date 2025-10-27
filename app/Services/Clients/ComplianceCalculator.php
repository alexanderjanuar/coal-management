<?php

namespace App\Services\Clients;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ComplianceCalculator
{
    protected Client $client;
    protected array $redFlags = [];
    protected array $result = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function calculate(bool $useCache = true): array
    {
        $cacheKey = "client_compliance_{$this->client->id}";

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $this->redFlags = [];

        $paymentCompliance = $this->calculatePaymentCompliance();
        $documentCompleteness = $this->calculateDocumentCompleteness();
        $deadlineAdherence = $this->calculateDeadlineAdherence();
        $reportingAccuracy = $this->calculateReportingAccuracy();

        // Weighted average
        $overallScore = (
            ($paymentCompliance * 0.30) +
            ($documentCompleteness * 0.25) +
            ($deadlineAdherence * 0.25) +
            ($reportingAccuracy * 0.20)
        );

        $this->result = [
            'overall_score' => round($overallScore, 2),
            'compliance_status' => $this->determineComplianceStatus($overallScore),
            'risk_level' => $this->determineRiskLevel($overallScore, $this->redFlags),
            'payment_compliance' => round($paymentCompliance, 2),
            'document_completeness' => round($documentCompleteness, 2),
            'deadline_adherence' => round($deadlineAdherence, 2),
            'reporting_accuracy' => round($reportingAccuracy, 2),
            'red_flags' => $this->redFlags,
            'red_flags_count' => count($this->redFlags),
            'calculated_at' => now(),
        ];

        // Cache for 1 hour
        Cache::put($cacheKey, $this->result, now()->addHour());

        return $this->result;
    }

    protected function calculatePaymentCompliance(): float
    {
        $score = 100;

        // Check invoices
        $totalInvoices = $this->client->invoices()->count();
        
        if ($totalInvoices > 0) {
            $paidInvoices = $this->client->invoices()
                ->where('invoice_status', 'paid')
                ->count();

            $overdueInvoices = $this->client->invoices()
                ->where('invoice_status', 'overdue')
                ->count();

            $pendingInvoices = $this->client->invoices()
                ->where('invoice_status', 'pending')
                ->where('due_date', '<', now())
                ->count();

            $paymentRate = ($paidInvoices / $totalInvoices) * 100;
            $score = $paymentRate;

            $score -= ($overdueInvoices * 5);
            $score -= ($pendingInvoices * 3);

            if ($overdueInvoices > 0) {
                $this->redFlags[] = "Terdapat {$overdueInvoices} invoice yang overdue";
            }

            if ($overdueInvoices > 3) {
                $this->redFlags[] = "Lebih dari 3 invoice belum dibayar (High Risk)";
            }
        }

        // Check tax payment
        $unpaidTaxes = $this->client->taxReports()
            ->where('payment_status', 'unpaid')
            ->count();

        if ($unpaidTaxes > 0) {
            $score -= ($unpaidTaxes * 10);
            $this->redFlags[] = "Terdapat {$unpaidTaxes} pembayaran pajak yang belum lunas";
        }

        return max(0, min(100, $score));
    }

    protected function calculateDocumentCompleteness(): float
    {
        $score = 100;

        $requiredDocs = $this->client->getApplicableSopDocuments()->count();
        
        if ($requiredDocs > 0) {
            $uploadedDocs = $this->client->clientDocuments()
                ->whereNotNull('sop_legal_document_id')
                ->distinct('sop_legal_document_id')
                ->count('sop_legal_document_id');

            $completenessRate = ($uploadedDocs / $requiredDocs) * 100;
            $score = $completenessRate;

            $expiredDocs = $this->client->clientDocuments()
                ->where('status', 'expired')
                ->orWhere(function($query) {
                    $query->whereNotNull('expired_at')
                          ->where('expired_at', '<', now());
                })
                ->count();

            if ($expiredDocs > 0) {
                $score -= ($expiredDocs * 10);
                $this->redFlags[] = "Terdapat {$expiredDocs} dokumen yang sudah kadaluarsa";
            }

            $rejectedDocs = $this->client->submittedDocuments()
                ->where('status', 'rejected')
                ->count();

            if ($rejectedDocs > 0) {
                $score -= ($rejectedDocs * 5);
            }

            $missingDocs = $requiredDocs - $uploadedDocs;
            if ($missingDocs > 0) {
                $this->redFlags[] = "Kurang {$missingDocs} dokumen wajib yang belum dilengkapi";
            }
        }

        return max(0, min(100, $score));
    }

    protected function calculateDeadlineAdherence(): float
    {
        $score = 100;
        $totalReports = 0;
        $lateReports = 0;

        $taxReports = $this->client->taxReports()
            ->whereNotNull('submission_date')
            ->get();

        foreach ($taxReports as $report) {
            $totalReports++;
            $deadline = $this->getTaxDeadline($report->report_type, $report->period_month, $report->period_year);
            
            if ($deadline && $report->submission_date > $deadline) {
                $lateReports++;
                $daysLate = $report->submission_date->diffInDays($deadline);
                
                if ($daysLate > 30) {
                    $this->redFlags[] = "Laporan {$report->report_type} terlambat {$daysLate} hari";
                }
            }
        }

        if ($totalReports > 0) {
            $onTimeRate = (($totalReports - $lateReports) / $totalReports) * 100;
            $score = $onTimeRate;
        }

        $overdueTasks = $this->client->projects()
            ->withCount(['tasks' => function($query) {
                $query->where('status', '!=', 'completed')
                      ->where('due_date', '<', now());
            }])
            ->get()
            ->sum('tasks_count');

        if ($overdueTasks > 0) {
            $score -= ($overdueTasks * 3);
            
            if ($overdueTasks > 5) {
                $this->redFlags[] = "Terdapat {$overdueTasks} task yang melewati deadline";
            }
        }

        return max(0, min(100, $score));
    }

    protected function calculateReportingAccuracy(): float
    {
        $score = 100;

        $totalReports = $this->client->taxReports()->count();
        
        if ($totalReports > 0) {
            $approvedReports = $this->client->taxReports()
                ->where('report_status', 'approved')
                ->count();

            $rejectedReports = $this->client->taxReports()
                ->where('report_status', 'rejected')
                ->count();

            $accuracyRate = ($approvedReports / $totalReports) * 100;
            $score = $accuracyRate;

            $score -= ($rejectedReports * 10);

            if ($rejectedReports > 2) {
                $this->redFlags[] = "Terdapat {$rejectedReports} laporan yang ditolak";
            }
        }

        $revisedInvoices = $this->client->invoices()
            ->where('is_revised', true)
            ->count();

        $totalInvoices = $this->client->invoices()->count();

        if ($totalInvoices > 0 && $revisedInvoices > 0) {
            $revisionRate = ($revisedInvoices / $totalInvoices) * 100;
            $score -= ($revisionRate / 2);
        }

        return max(0, min(100, $score));
    }

    protected function getTaxDeadline(string $reportType, ?int $month, int $year): ?Carbon
    {
        if (!$month) return null;

        return match($reportType) {
            'spt_tahunan_badan', 'spt_tahunan_pribadi' => Carbon::create($year + 1, 4, 30),
            'spt_masa_ppn' => Carbon::create($year, $month, 1)->addMonth()->endOfMonth(),
            'spt_masa_pph' => Carbon::create($year, $month, 1)->addMonth()->day(20),
            default => null,
        };
    }

    protected function determineComplianceStatus(float $score): string
    {
        return match(true) {
            $score >= 90 => 'excellent',
            $score >= 80 => 'good',
            $score >= 70 => 'fair',
            $score >= 50 => 'poor',
            default => 'critical',
        };
    }

    protected function determineRiskLevel(float $score, array $redFlags): string
    {
        $redFlagsCount = count($redFlags);

        return match(true) {
            $score < 50 || $redFlagsCount >= 5 => 'critical',
            $score < 70 || $redFlagsCount >= 3 => 'high',
            $score < 85 || $redFlagsCount >= 1 => 'medium',
            default => 'low',
        };
    }

    public function clearCache(): void
    {
        Cache::forget("client_compliance_{$this->client->id}");
    }
}