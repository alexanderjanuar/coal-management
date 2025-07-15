<?php

namespace App\Livewire\TaxReport;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\TaxReport;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\IncomeTax;
use App\Models\Bupot;

class TaxCalendar extends Component
{
    public $currentDate;
    public $calendarDays = [];
    public $selectedDate = null;
    public $isModalOpen = false;
    public $isClientModalOpen = false;
    public $selectedEvents = [];
    public $pendingClients = [];
    
    public function mount()
    {
        // Initialize with current date
        $this->currentDate = Carbon::now();
        $this->generateCalendarDays();
    }

    public function generateCalendarDays()
    {
        $this->calendarDays = [];
        
        $year = $this->currentDate->year;
        $month = $this->currentDate->month;
        
        // First day of the month
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1);
        
        // Last day of the month
        $lastDayOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // Day of the week for the first day (0 = Sunday, 6 = Saturday)
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeek;
        
        // Add days from previous month to fill the first week
        $prevMonthDays = [];
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $date = Carbon::createFromDate($year, $month, 1)
                ->subDays($firstDayOfWeek - $i);
            
            $prevMonthDays[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'isCurrentMonth' => false,
                'hasEvent' => $this->hasTaxEvent($date),
                'isToday' => $date->isToday(),
                'pendingClientsCount' => $this->getPendingClientsCount($date),
                'isLastDay' => $this->isLastDayOfMonth($date), // Debug helper
            ];
        }
        
        // Add days of the current month
        $currentMonthDays = [];
        for ($i = 1; $i <= $lastDayOfMonth->day; $i++) {
            $date = Carbon::createFromDate($year, $month, $i);
            
            $currentMonthDays[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'isCurrentMonth' => true,
                'hasEvent' => $this->hasTaxEvent($date),
                'isToday' => $date->isToday(),
                'pendingClientsCount' => $this->getPendingClientsCount($date),
            ];
        }
        
        // Add days from next month to complete the grid (6 rows of 7 days)
        $totalDays = count($prevMonthDays) + count($currentMonthDays);
        $remainingDays = 42 - $totalDays; // 6 rows of 7 days
        
        $nextMonthDays = [];
        for ($i = 1; $i <= $remainingDays; $i++) {
            $date = Carbon::createFromDate($year, $month, 1)
                ->addMonth()
                ->addDays($i - 1);
            
            $nextMonthDays[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'isCurrentMonth' => false,
                'hasEvent' => $this->hasTaxEvent($date),
                'isToday' => $date->isToday(),
                'pendingClientsCount' => $this->getPendingClientsCount($date),
            ];
        }
        
        $this->calendarDays = array_merge($prevMonthDays, $currentMonthDays, $nextMonthDays);
    }

    public function goToPreviousMonth()
    {
        $this->currentDate = $this->currentDate->subMonth();
        $this->generateCalendarDays();
    }

    public function goToNextMonth()
    {
        $this->currentDate = $this->currentDate->addMonth();
        $this->generateCalendarDays();
    }

    public function selectDate($dateString)
    {
        $this->selectedDate = $dateString;
        $date = Carbon::parse($dateString);
        
        // Check if this is a date with pending clients
        if ($this->getPendingClientsCount($date) > 0) {
            $this->pendingClients = $this->getPendingClients($date);
            $this->isClientModalOpen = true;
            return;
        }
        
        if ($this->hasTaxEvent($date)) {
            $this->selectedEvents = $this->getTaxEventsForDate($date);
            $this->isModalOpen = true;
        } else {
            $this->selectedEvents = [];
            $this->isModalOpen = false;
        }
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function closeClientModal()
    {
        $this->isClientModalOpen = false;
    }

    protected function hasTaxEvent(Carbon $date)
    {
        $taxEvents = $this->getTaxEvents();
        
        $dateString = $date->format('Y-m-d');
        return collect($taxEvents)->contains('date', $dateString);
    }

    protected function getTaxEventsForDate(Carbon $date)
    {
        $taxEvents = $this->getTaxEvents();
        
        $dateString = $date->format('Y-m-d');
        return collect($taxEvents)->where('date', $dateString)->values()->all();
    }

    public function getTaxSchedule()
    {
        // Get current month's events
        $currentYear = $this->currentDate->year;
        $currentMonth = $this->currentDate->month;
        
        // Get tax events
        $taxEvents = $this->getTaxEvents();
        
        // Filter events for the current month
        return collect($taxEvents)
            ->filter(function ($event) use ($currentYear, $currentMonth) {
                $eventDate = Carbon::parse($event['date']);
                return $eventDate->year === $currentYear && $eventDate->month === $currentMonth;
            })
            ->sortBy('date')
            ->values()
            ->all();
    }

    protected function getTaxEvents()
    {
        // Get tax events for the current displayed month
        $currentYear = $this->currentDate->year;
        $currentMonth = $this->currentDate->month;
        
        $lastDay = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->format('Y-m-d');
        
        // For PPN reporting: The deadline is for the PREVIOUS month's tax report
        // Example: May 31 is the deadline for April tax report
        $targetMonth = $this->currentDate->copy()->subMonth();
        $targetMonthName = $targetMonth->translatedFormat('F Y');
        
        return [
            [
                'date' => $lastDay,
                'title' => 'Batas Akhir Lapor SPT Masa PPN',
                'description' => "Batas akhir lapor SPT Masa PPN periode {$targetMonthName}",
                'actionText' => 'Kelola PPN',
                'actionLink' => '',
                'type' => 'report',
                'priority' => 'high'
            ],
            [
                'date' => Carbon::createFromDate($currentYear, $currentMonth, 15)->format('Y-m-d'),
                'title' => 'Batas Akhir Setor PPh dan PPN',
                'description' => "Batas akhir setor PPh dan PPN periode {$targetMonthName}",
                'actionText' => 'Kelola Pembayaran',
                'actionLink' => '',
                'type' => 'payment',
                'priority' => 'high'
            ],
            [
                'date' => Carbon::createFromDate($currentYear, $currentMonth, 20)->format('Y-m-d'),
                'title' => 'Batas Akhir Lapor SPT Masa PPh 21',
                'description' => "Batas akhir lapor SPT Masa PPh 21 periode {$targetMonthName}",
                'actionText' => 'Kelola PPh 21',
                'actionLink' => '',
                'type' => 'report',
                'priority' => 'medium'
            ],
        ];
    }

    protected function getPendingClientsCount(Carbon $date)
    {
        $day = $date->day;
        
        // Check important tax dates and count pending clients
        if ($day == 15) {
            // PPh and PPN payment deadline - count clients with unpaid taxes for the previous month
            return $this->getUnpaidTaxClientsCount($date);
        } elseif ($day == 20) {
            // PPh 21 reporting deadline - count clients with unreported PPh 21 for the previous month
            return $this->getUnreportedPPhClientsCount($date);
        } elseif ($this->isLastDayOfMonth($date)) {
            // PPN reporting deadline - count clients with unreported PPN for the previous month
            return $this->getUnreportedPPNClientsCount($date);
        }
        
        return 0;
    }
    
    /**
     * Check if the given date is the last day of its month
     */
    protected function isLastDayOfMonth(Carbon $date)
    {
        return $date->day === $date->copy()->endOfMonth()->day;
    }
    
    protected function getUnpaidTaxClientsCount(Carbon $date)
    {
        // For the 15th: Get tax reports for the previous month that need payment
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F'); // Get month name like 'June', 'May'
        
        return TaxReport::where('month', $monthName)
            ->where(function($query) {
                $query->where('ppn_report_status', 'Belum Lapor')
                      ->orWhere('pph_report_status', 'Belum Lapor');
            })
            ->count();
    }
    
    protected function getUnreportedPPhClientsCount(Carbon $date)
    {
        // For the 20th: Get tax reports for the previous month that need PPh 21 reporting
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F'); // Get month name like 'June', 'May'
        
        return TaxReport::where('month', $monthName)
            ->where('pph_report_status', 'Belum Lapor')
            ->count();
    }
    
    protected function getUnreportedPPNClientsCount(Carbon $date)
    {
        // For the last day: Get tax reports for the PREVIOUS month that need PPN reporting
        // Example: On July 31, we check June tax reports
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F'); // Get month name like 'June', 'May'
        
        return TaxReport::where('month', $monthName)
            ->where('ppn_report_status', 'Belum Lapor')
            ->count();
    }
    
    protected function getPendingClients(Carbon $date)
    {
        $day = $date->day;
        
        // The target month is always the month before the calendar date
        // Example: If calendar shows July 31, we check tax reports for June
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $targetMonthFormatted = $targetMonth->format('F'); // Month name like 'June', 'May'
        $monthName = $targetMonth->translatedFormat('F Y');
        
        $reportType = '';
        $clients = [];
        
        if ($day == 15) {
            $reportType = "Setor PPh dan PPN untuk periode {$monthName}";
            // Get clients with unpaid taxes for the target month
            $taxReports = TaxReport::with('client')
                ->where('month', $targetMonthFormatted)
                ->where(function($query) {
                    $query->where('ppn_report_status', 'Belum Lapor')
                          ->orWhere('pph_report_status', 'Belum Lapor');
                })
                ->get();
                
            foreach ($taxReports as $report) {
                $totalTax = 0;
                // Calculate total unpaid tax amount
                if ($report->ppn_report_status === 'Belum Lapor') {
                    $totalTax += $report->invoices()->sum('ppn');
                }
                if ($report->pph_report_status === 'Belum Lapor') {
                    $totalTax += $report->incomeTaxs()->sum('pph_21_amount');
                }
                
                $clients[] = [
                    'id' => $report->client->id,
                    'name' => $report->client->name,
                    'status' => $this->getPaymentStatus($report),
                    'dueAmount' => $totalTax,
                    'NPWP' => $report->client->NPWP ?? 'Tidak Ada'
                ];
            }
            
        } elseif ($day == 20) {
            $reportType = "Lapor SPT Masa PPh 21 untuk periode {$monthName}";
            // Get clients with unreported PPh 21 for the target month
            $taxReports = TaxReport::with('client')
                ->where('month', $targetMonthFormatted)
                ->where('pph_report_status', 'Belum Lapor')
                ->get();
                
            foreach ($taxReports as $report) {
                $employeeCount = $report->client->employees()->count();
                
                $clients[] = [
                    'id' => $report->client->id,
                    'name' => $report->client->name,
                    'status' => 'Belum lapor PPh 21',
                    'employees' => $employeeCount,
                    'NPWP' => $report->client->NPWP ?? 'Tidak Ada'
                ];
            }
            
        } elseif ($this->isLastDayOfMonth($date)) {
            $reportType = "Lapor SPT Masa PPN untuk periode {$monthName}";
            // Get clients with unreported PPN for the target month (PREVIOUS MONTH)
            $taxReports = TaxReport::with('client')
                ->where('month', $targetMonthFormatted)
                ->where('ppn_report_status', 'Belum Lapor')
                ->get();
                
            foreach ($taxReports as $report) {
                $transactionCount = $report->invoices()->count();
                
                $clients[] = [
                    'id' => $report->client->id,
                    'name' => $report->client->name,
                    'status' => 'Belum lapor PPN',
                    'transaksiCount' => $transactionCount,
                    'NPWP' => $report->client->NPWP ?? 'Tidak Ada'
                ];
            }
        }
        
        return [
            'reportType' => $reportType,
            'date' => $date->translatedFormat('d F Y'),
            'clients' => $clients
        ];
    }
    
    protected function getPaymentStatus($taxReport)
    {
        $statuses = [];
        
        if ($taxReport->ppn_report_status === 'Belum Lapor') {
            $statuses[] = 'Belum bayar PPN';
        }
        
        if ($taxReport->pph_report_status === 'Belum Lapor') {
            $statuses[] = 'Belum bayar PPh';
        }
        
        if ($taxReport->bupot_report_status === 'Belum Lapor') {
            $statuses[] = 'Belum upload Bupot';
        }
        
        return !empty($statuses) ? implode(', ', $statuses) : 'Semua sudah dibayar';
    }

    /**
     * Get overdue PPN reports (past deadline)
     */
    public function getOverduePPNReports()
    {
        $today = Carbon::now();
        $currentMonth = $today->format('Y-m');
        
        // Get all previous months that have passed their deadlines
        $overdueReports = TaxReport::with('client')
            ->where('month', '<', $currentMonth)
            ->where('ppn_report_status', 'Belum Lapor')
            ->get();
            
        return $overdueReports;
    }
    
    /**
     * Get upcoming PPN deadlines (within next 7 days)
     */
    public function getUpcomingPPNDeadlines()
    {
        $today = Carbon::now();
        $nextWeek = $today->copy()->addDays(7);
        
        $upcomingDeadlines = [];
        
        // Check if any month-end falls within the next 7 days
        for ($i = 0; $i <= 7; $i++) {
            $checkDate = $today->copy()->addDays($i);
            if ($checkDate->day == $checkDate->copy()->endOfMonth()->day) {
                // This is a month-end date - check for unreported PPN
                $targetMonth = $checkDate->copy()->subMonth()->format('Y-m');
                
                $unreportedCount = TaxReport::where('month', $targetMonth)
                    ->where('ppn_report_status', 'Belum Lapor')
                    ->count();
                    
                if ($unreportedCount > 0) {
                    $upcomingDeadlines[] = [
                        'date' => $checkDate->format('Y-m-d'),
                        'deadline_date' => $checkDate->translatedFormat('d F Y'),
                        'period_month' => $checkDate->copy()->subMonth()->translatedFormat('F Y'),
                        'unreported_count' => $unreportedCount,
                        'days_remaining' => $i
                    ];
                }
            }
        }
        
        return $upcomingDeadlines;
    }

    /**
     * Debug method to check pending counts for specific dates
     */
    public function debugPendingCounts($dateString = null)
    {
        $date = $dateString ? Carbon::parse($dateString) : Carbon::now();
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        
        $debug = [
            'date' => $date->format('Y-m-d'),
            'day' => $date->day,
            'is_15th' => $date->day == 15,
            'is_20th' => $date->day == 20,
            'is_last_day' => $this->isLastDayOfMonth($date),
            'end_of_month_day' => $date->copy()->endOfMonth()->day,
            'target_month' => $targetMonth->format('F'),
            'target_month_debug' => [
                'original_date' => $date->format('Y-m-d'),
                'start_of_month' => $date->copy()->startOfMonth()->format('Y-m-d'),
                'minus_one_month' => $targetMonth->format('Y-m-d'),
                'month_name' => $targetMonth->format('F')
            ],
            'counts' => [
                'unpaid_tax' => $this->getUnpaidTaxClientsCount($date),
                'unreported_pph' => $this->getUnreportedPPhClientsCount($date),
                'unreported_ppn' => $this->getUnreportedPPNClientsCount($date),
            ],
            'sql_check' => [
                'query' => "SELECT COUNT(*) FROM tax_reports WHERE month = '{$targetMonth->format('F')}' AND ppn_report_status = 'Belum Lapor'",
                'manual_count' => TaxReport::where('month', $targetMonth->format('F'))->where('ppn_report_status', 'Belum Lapor')->count()
            ],
            'total_pending' => $this->getPendingClientsCount($date)
        ];
        
        return $debug;
    }

    public function render()
    {
        return view('livewire.tax-report.tax-calendar');
    }
}