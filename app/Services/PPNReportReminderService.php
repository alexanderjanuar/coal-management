<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\TaxReport;
use App\Models\Client;

class PPNReportReminderService
{
    /**
     * Get all PPN reporting reminders
     */
    public function getAllPPNReminders()
    {
        return [
            'overdue' => $this->getOverduePPNReports(),
            'due_today' => $this->getPPNReportsDueToday(),
            'due_this_week' => $this->getPPNReportsDueThisWeek(),
            'upcoming' => $this->getUpcomingPPNDeadlines(),
        ];
    }

    /**
     * Get overdue PPN reports (past deadline)
     */
    public function getOverduePPNReports()
    {
        $today = Carbon::now();
        
        // Get all months before current month
        $overdueReports = TaxReport::with(['client' => function($query) {
                $query->select('id', 'name', 'NPWP');
            }])
            ->where('month', '<', $today->format('Y-m'))
            ->where('ppn_report_status', 'Belum Lapor')
            ->get()
            ->map(function($report) {
                $reportMonth = Carbon::createFromFormat('Y-m', $report->month);
                $deadline = $reportMonth->copy()->addMonth()->endOfMonth();
                
                return [
                    'tax_report_id' => $report->id,
                    'client_id' => $report->client_id,
                    'client_name' => $report->client->name,
                    'client_npwp' => $report->client->NPWP,
                    'period_month' => $reportMonth->translatedFormat('F Y'),
                    'deadline_date' => $deadline->translatedFormat('d F Y'),
                    'days_overdue' => $deadline->diffInDays(Carbon::now()),
                    'priority' => 'critical'
                ];
            });

        return $overdueReports;
    }

    /**
     * Get PPN reports due today
     */
    public function getPPNReportsDueToday()
    {
        $today = Carbon::now();
        
        // Check if today is month-end
        if (!$today->isLastOfMonth()) {
            return collect([]);
        }

        $targetMonth = $today->copy()->subMonth()->format('Y-m');
        
        return TaxReport::with(['client' => function($query) {
                $query->select('id', 'name', 'NPWP');
            }])
            ->where('month', $targetMonth)
            ->where('ppn_report_status', 'Belum Lapor')
            ->get()
            ->map(function($report) use ($today) {
                $reportMonth = Carbon::createFromFormat('Y-m', $report->month);
                
                return [
                    'tax_report_id' => $report->id,
                    'client_id' => $report->client_id,
                    'client_name' => $report->client->name,
                    'client_npwp' => $report->client->NPWP,
                    'period_month' => $reportMonth->translatedFormat('F Y'),
                    'deadline_date' => $today->translatedFormat('d F Y'),
                    'days_remaining' => 0,
                    'priority' => 'urgent'
                ];
            });
    }

    /**
     * Get PPN reports due this week
     */
    public function getPPNReportsDueThisWeek()
    {
        $today = Carbon::now();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $dueThisWeek = collect([]);
        
        // Check each day this week to see if it's a month-end
        for ($date = $today->copy(); $date->lte($endOfWeek); $date->addDay()) {
            if ($date->isLastOfMonth()) {
                $targetMonth = $date->copy()->subMonth()->format('Y-m');
                
                $reports = TaxReport::with(['client' => function($query) {
                        $query->select('id', 'name', 'NPWP');
                    }])
                    ->where('month', $targetMonth)
                    ->where('ppn_report_status', 'Belum Lapor')
                    ->get()
                    ->map(function($report) use ($date, $today) {
                        $reportMonth = Carbon::createFromFormat('Y-m', $report->month);
                        
                        return [
                            'tax_report_id' => $report->id,
                            'client_id' => $report->client_id,
                            'client_name' => $report->client->name,
                            'client_npwp' => $report->client->NPWP,
                            'period_month' => $reportMonth->translatedFormat('F Y'),
                            'deadline_date' => $date->translatedFormat('d F Y'),
                            'days_remaining' => $today->diffInDays($date, false),
                            'priority' => 'high'
                        ];
                    });
                    
                $dueThisWeek = $dueThisWeek->merge($reports);
            }
        }
        
        return $dueThisWeek;
    }

    /**
     * Get upcoming PPN deadlines (next 30 days)
     */
    public function getUpcomingPPNDeadlines()
    {
        $today = Carbon::now();
        $next30Days = $today->copy()->addDays(30);
        
        $upcomingDeadlines = collect([]);
        
        // Check each day in the next 30 days
        for ($date = $today->copy()->addDay(); $date->lte($next30Days); $date->addDay()) {
            if ($date->isLastOfMonth()) {
                $targetMonth = $date->copy()->subMonth()->format('Y-m');
                
                $reports = TaxReport::with(['client' => function($query) {
                        $query->select('id', 'name', 'NPWP');
                    }])
                    ->where('month', $targetMonth)
                    ->where('ppn_report_status', 'Belum Lapor')
                    ->get()
                    ->map(function($report) use ($date, $today) {
                        $reportMonth = Carbon::createFromFormat('Y-m', $report->month);
                        
                        return [
                            'tax_report_id' => $report->id,
                            'client_id' => $report->client_id,
                            'client_name' => $report->client->name,
                            'client_npwp' => $report->client->NPWP,
                            'period_month' => $reportMonth->translatedFormat('F Y'),
                            'deadline_date' => $date->translatedFormat('d F Y'),
                            'days_remaining' => $today->diffInDays($date, false),
                            'priority' => 'medium'
                        ];
                    });
                    
                $upcomingDeadlines = $upcomingDeadlines->merge($reports);
            }
        }
        
        return $upcomingDeadlines;
    }

    /**
     * Get summary of all PPN report statuses
     */
    public function getPPNReportSummary()
    {
        $overdue = $this->getOverduePPNReports();
        $dueToday = $this->getPPNReportsDueToday();
        $dueThisWeek = $this->getPPNReportsDueThisWeek();
        $upcoming = $this->getUpcomingPPNDeadlines();

        return [
            'overdue_count' => $overdue->count(),
            'due_today_count' => $dueToday->count(),
            'due_this_week_count' => $dueThisWeek->count(),
            'upcoming_count' => $upcoming->count(),
            'total_pending' => TaxReport::where('ppn_report_status', 'Belum Lapor')->count(),
            'details' => [
                'overdue' => $overdue,
                'due_today' => $dueToday,
                'due_this_week' => $dueThisWeek,
                'upcoming' => $upcoming,
            ]
        ];
    }

    /**
     * Get PPN reports for a specific client
     */
    public function getClientPPNReports($clientId)
    {
        return TaxReport::with('client')
            ->where('client_id', $clientId)
            ->where('ppn_report_status', 'Belum Lapor')
            ->get()
            ->map(function($report) {
                $reportMonth = Carbon::createFromFormat('Y-m', $report->month);
                $deadline = $reportMonth->copy()->addMonth()->endOfMonth();
                $today = Carbon::now();
                
                $status = 'upcoming';
                if ($deadline->isPast()) {
                    $status = 'overdue';
                } elseif ($deadline->isToday()) {
                    $status = 'due_today';
                } elseif ($deadline->diffInDays($today) <= 7) {
                    $status = 'due_this_week';
                }
                
                return [
                    'tax_report_id' => $report->id,
                    'period_month' => $reportMonth->translatedFormat('F Y'),
                    'deadline_date' => $deadline->translatedFormat('d F Y'),
                    'days_remaining' => $deadline->diffInDays($today, false),
                    'status' => $status,
                    'priority' => $this->getPriorityByStatus($status)
                ];
            });
    }

    /**
     * Mark PPN report as completed
     */
    public function markPPNReportCompleted($taxReportId, $reportedAt = null)
    {
        $taxReport = TaxReport::findOrFail($taxReportId);
        
        $taxReport->update([
            'ppn_report_status' => 'Sudah Lapor',
            'ppn_reported_at' => $reportedAt ?? Carbon::now()
        ]);
        
        return $taxReport;
    }

    /**
     * Get priority level by status
     */
    private function getPriorityByStatus($status)
    {
        return match($status) {
            'overdue' => 'critical',
            'due_today' => 'urgent',
            'due_this_week' => 'high',
            'upcoming' => 'medium',
            default => 'low'
        };
    }
}