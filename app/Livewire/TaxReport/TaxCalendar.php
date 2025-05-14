<?php

namespace App\Livewire\TaxReport;

use Livewire\Component;
use Carbon\Carbon;

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
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        
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
        // Dummy tax events data
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;
        $currentMonth = $currentDate->month;
        
        $day15 = Carbon::createFromDate($currentYear, $currentMonth, 15)->format('Y-m-d');
        $day20 = Carbon::createFromDate($currentYear, $currentMonth, 20)->format('Y-m-d');
        $lastDay = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->format('Y-m-d');
        
        // Previous month for reference in the last day event
        $prevMonth = Carbon::createFromDate($currentYear, $currentMonth, 1)->subMonth();
        $prevMonthName = $prevMonth->translatedFormat('F Y');
        
        return [
            [
                'date' => $day15,
                'title' => 'Batas Akhir Setor PPh dan PPN',
                'description' => 'Batas akhir setor PPh dan PPN & Upload e-Faktur',
                'actionText' => 'Bayar sekarang',
                'actionLink' => '/pay',
                'type' => 'payment',
                'priority' => 'high'
            ],
            [
                'date' => $day20,
                'title' => 'Batas Akhir Lapor SPT Masa PPh 21',
                'description' => 'Batas akhir lapor SPT Masa PPh 21 & PPh Unifikasi, Lapor SPT Bea Meterai',
                'actionText' => 'Lapor di sini',
                'actionLink' => '/report',
                'type' => 'report',
                'priority' => 'medium'
            ],
            [
                'date' => $lastDay,
                'title' => 'Batas Akhir Lapor SPT Masa PPN',
                'description' => "Batas akhir lapor SPT Masa PPN {$prevMonthName}",
                'actionText' => 'Lapor di sini',
                'actionLink' => '/report',
                'type' => 'report',
                'priority' => 'high'
            ],
        ];
    }

    protected function getPendingClientsCount(Carbon $date)
    {
        // Dummy logic to determine the count of pending clients for specific dates
        $day = $date->day;
        $currentMonth = $date->month;
        $currentYear = $date->year;
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        
        // Only show pending clients for current or future months
        if ($currentYear < $thisYear || ($currentYear == $thisYear && $currentMonth < $thisMonth)) {
            return 0;
        }
        
        // Check important tax dates
        if ($day == 15) {
            return 8; // 8 clients pending for PPh and PPN payments
        } elseif ($day == 20) {
            return 5; // 5 clients pending for PPh 21 reports
        } elseif ($day == $date->endOfMonth()->day) {
            return 12; // 12 clients pending for PPN reports
        }
        
        return 0;
    }
    
    protected function getPendingClients(Carbon $date)
    {
        // Dummy data for pending clients
        $day = $date->day;
        $currentMonth = Carbon::now()->month; 
        $monthName = $date->translatedFormat('F');
        
        $reportType = '';
        if ($day == 15) {
            $reportType = 'Setor PPh dan PPN';
        } elseif ($day == 20) {
            $reportType = 'Lapor SPT Masa PPh 21';
        } elseif ($day == $date->endOfMonth()->day) {
            $reportType = 'Lapor SPT Masa PPN';
        }
        
        $clients = [];
        
        // Generate different client lists for different dates
        if ($day == 15) {
            $clients = [
                ['id' => 1, 'name' => 'PT Maju Jaya', 'status' => 'belum bayar', 'dueAmount' => 5250000, 'NPWP' => '01.234.567.8-123.000'],
                ['id' => 2, 'name' => 'CV Teknologi Mandiri', 'status' => 'belum upload e-faktur', 'dueAmount' => 3750000, 'NPWP' => '02.345.678.9-234.000'],
                ['id' => 3, 'name' => 'PT Sejahtera Abadi', 'status' => 'belum bayar', 'dueAmount' => 8150000, 'NPWP' => '03.456.789.0-345.000'],
                ['id' => 4, 'name' => 'Toko Makmur', 'status' => 'belum upload e-faktur', 'dueAmount' => 1250000, 'NPWP' => '04.567.890.1-456.000'],
                ['id' => 5, 'name' => 'PT Global Indonesia', 'status' => 'belum bayar', 'dueAmount' => 12500000, 'NPWP' => '05.678.901.2-567.000'],
                ['id' => 6, 'name' => 'UD Sentosa', 'status' => 'belum bayar', 'dueAmount' => 2150000, 'NPWP' => '06.789.012.3-678.000'],
                ['id' => 7, 'name' => 'PT Cahaya Timur', 'status' => 'belum upload e-faktur', 'dueAmount' => 4500000, 'NPWP' => '07.890.123.4-789.000'],
                ['id' => 8, 'name' => 'CV Karya Utama', 'status' => 'belum bayar', 'dueAmount' => 3250000, 'NPWP' => '08.901.234.5-890.000'],
            ];
        } elseif ($day == 20) {
            $clients = [
                ['id' => 1, 'name' => 'PT Maju Jaya', 'status' => 'belum lapor', 'employees' => 24, 'NPWP' => '01.234.567.8-123.000'],
                ['id' => 2, 'name' => 'PT Sejahtera Abadi', 'status' => 'belum lapor', 'employees' => 18, 'NPWP' => '03.456.789.0-345.000'],
                ['id' => 3, 'name' => 'PT Global Indonesia', 'status' => 'belum bayar PPh 21', 'employees' => 45, 'NPWP' => '05.678.901.2-567.000'],
                ['id' => 4, 'name' => 'PT Cahaya Timur', 'status' => 'belum lapor', 'employees' => 12, 'NPWP' => '07.890.123.4-789.000'],
                ['id' => 5, 'name' => 'PT Harmoni Raya', 'status' => 'belum bayar PPh 21', 'employees' => 35, 'NPWP' => '09.012.345.6-901.000'],
            ];
        } elseif ($day == $date->endOfMonth()->day) {
            $clients = [
                ['id' => 1, 'name' => 'PT Maju Jaya', 'status' => 'belum lapor PPN', 'transaksiCount' => 42, 'NPWP' => '01.234.567.8-123.000'],
                ['id' => 2, 'name' => 'CV Teknologi Mandiri', 'status' => 'belum lapor PPN', 'transaksiCount' => 23, 'NPWP' => '02.345.678.9-234.000'],
                ['id' => 3, 'name' => 'PT Sejahtera Abadi', 'status' => 'belum rekonsiliasi faktur', 'transaksiCount' => 56, 'NPWP' => '03.456.789.0-345.000'],
                ['id' => 4, 'name' => 'Toko Makmur', 'status' => 'belum lapor PPN', 'transaksiCount' => 18, 'NPWP' => '04.567.890.1-456.000'],
                ['id' => 5, 'name' => 'PT Global Indonesia', 'status' => 'belum rekonsiliasi faktur', 'transaksiCount' => 87, 'NPWP' => '05.678.901.2-567.000'],
                ['id' => 6, 'name' => 'UD Sentosa', 'status' => 'belum lapor PPN', 'transaksiCount' => 29, 'NPWP' => '06.789.012.3-678.000'],
                ['id' => 7, 'name' => 'PT Cahaya Timur', 'status' => 'belum lapor PPN', 'transaksiCount' => 34, 'NPWP' => '07.890.123.4-789.000'],
                ['id' => 8, 'name' => 'CV Karya Utama', 'status' => 'belum rekonsiliasi faktur', 'transaksiCount' => 19, 'NPWP' => '08.901.234.5-890.000'],
                ['id' => 9, 'name' => 'PT Harmoni Raya', 'status' => 'belum lapor PPN', 'transaksiCount' => 68, 'NPWP' => '09.012.345.6-901.000'],
                ['id' => 10, 'name' => 'Toko Barokah', 'status' => 'belum lapor PPN', 'transaksiCount' => 12, 'NPWP' => '10.123.456.7-012.000'],
                ['id' => 11, 'name' => 'PT Mitra Sejati', 'status' => 'belum rekonsiliasi faktur', 'transaksiCount' => 45, 'NPWP' => '11.234.567.8-123.000'],
                ['id' => 12, 'name' => 'CV Sukses Jaya', 'status' => 'belum lapor PPN', 'transaksiCount' => 31, 'NPWP' => '12.345.678.9-234.000'],
            ];
        }
        
        return [
            'reportType' => $reportType,
            'date' => $date->translatedFormat('d F Y'),
            'clients' => $clients
        ];
    }

    public function render()
    {
        return view('livewire.tax-report.tax-calendar');
    }
}