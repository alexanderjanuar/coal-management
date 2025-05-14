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
    public $selectedEvents = [];
    
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


    public function render()
    {
        return view('livewire.tax-report.tax-calendar');
    }
}
