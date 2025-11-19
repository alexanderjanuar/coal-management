<?php

namespace App\Livewire\TaxReport;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\TaxReport;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\IncomeTax;
use App\Models\Bupot;
use App\Models\User;
use Filament\Notifications\Notification;

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
            $this->dispatch('open-modal', id: 'pending-clients-modal');
            return;
        }
        
        if ($this->hasTaxEvent($date)) {
            $this->selectedEvents = $this->getTaxEventsForDate($date);
            $this->dispatch('open-modal', id: 'tax-events-modal');
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
        
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $targetMonthFormatted = $targetMonth->format('F');
        $monthName = $targetMonth->translatedFormat('F Y');
        
        $reportType = '';
        $clients = [];
        
        if ($day == 15) {
                $reportType = "Setor PPh dan PPN untuk periode {$monthName}";
                $taxReports = TaxReport::with('client')
                    ->where('month', $targetMonthFormatted)
                    ->where('ppn_report_status', 'Belum Lapor') // Hanya yang belum lapor PPN
                    ->get();
                    
                foreach ($taxReports as $report) {
                    // Hitung peredaran bruto hanya dari Faktur Keluaran untuk PPN
                    $peredaranBruto = $report->originalInvoices()
                        ->where('type', 'Faktur Keluaran')
                        ->sum('dpp');
                    
                    $clients[] = [
                        'id' => $report->client->id,
                        'tax_report_id' => $report->id,
                        'name' => $report->client->name,
                        'logo' => $report->client->logo,
                        'status' => 'Belum bayar PPN', // Spesifik untuk PPN
                        'dueAmount' => $peredaranBruto,
                        'NPWP' => $report->client->NPWP ?? 'Tidak Ada'
                    ];
                }
            }
            elseif ($day == 20) {
            $reportType = "Lapor SPT Masa PPh 21 untuk periode {$monthName}";
            $taxReports = TaxReport::with('client')
                ->where('month', $targetMonthFormatted)
                ->where('pph_report_status', 'Belum Lapor')
                ->get();
                
            foreach ($taxReports as $report) {
                $employeeCount = $report->client->employees()->count();
                
                $clients[] = [
                    'id' => $report->client->id,
                        'tax_report_id' => $report->id,
                    'name' => $report->client->name,
                    'status' => 'Belum lapor PPh 21',
                    'employees' => $employeeCount,
                    'NPWP' => $report->client->NPWP ?? 'Tidak Ada'
                ];
            }
            
        } elseif ($this->isLastDayOfMonth($date)) {
            $reportType = "Lapor SPT Masa PPN untuk periode {$monthName}";
            $taxReports = TaxReport::with('client')
                ->where('month', $targetMonthFormatted)
                ->where('ppn_report_status', 'Belum Lapor')
                ->get();
                
            foreach ($taxReports as $report) {
                $transactionCount = $report->originalInvoices()->count();
                
                $clients[] = [
                    'id' => $report->client->id,
                    'tax_report_id' => $report->id,
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
        
        return !empty($statuses) ? implode(', ', $statuses) : 'PPN sudah dibayar';
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

    public function sendMassReminder()
    {
        try {
            // Get all users with project-manager role
            $projectManagers = User::whereHas('roles', function ($query) {
                $query->where('name', 'project-manager');
            })->get();

            if ($projectManagers->isEmpty()) {
                Notification::make()
                    ->title('Tidak Ada Project Manager')
                    ->body('Tidak ditemukan user dengan role project-manager.')
                    ->warning()
                    ->send();
                return;
            }

            $clientCount = count($this->pendingClients['clients'] ?? []);
            $reportType = $this->pendingClients['reportType'] ?? '';
            $date = $this->pendingClients['date'] ?? '';

            // Create notification for each project manager
            foreach ($projectManagers as $manager) {
                Notification::make()
                    ->title('Pengingat: Klien Tertunggak')
                    ->body("Ada {$clientCount} klien yang belum {$reportType} pada {$date}. Segera tindak lanjuti untuk memastikan kepatuhan pajak.")
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('warning')
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('Lihat Detail')
                            ->url('#') // You can add specific URL if needed
                            ->markAsRead(),
                        \Filament\Notifications\Actions\Action::make('dismiss')
                            ->label('Tutup')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($manager)
                    ->broadcast($manager);
            }

            // Close the modal
            $this->dispatch('close-modal', ['id' => 'pending-clients-modal']);

            // Show success notification to current user
            Notification::make()
                ->title('Pengingat Terkirim')
                ->body("Pengingat telah dikirim ke {$projectManagers->count()} project manager.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Mengirim Pengingat')
                ->body('Terjadi kesalahan saat mengirim pengingat. Silakan coba lagi.')
                ->danger()
                ->send();

            report($e); // Log error untuk debugging
        }
    }


    public function getTaxReportUrl($taxReportId)
    {
        return route('filament.admin.resources.tax-reports.view', ['record' => $taxReportId]);
    }

    public function getClientUrl($clientId)
    {
        return route('filament.admin.resources.clients.edit', ['record' => $clientId]);
    }

    public function render()
    {
        return view('livewire.tax-report.tax-calendar');
    }
}