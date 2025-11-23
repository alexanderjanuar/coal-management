<?php

namespace App\Livewire\TaxReport\Dashboard;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\TaxReport;
use App\Models\Client;
use App\Models\TaxCalculationSummary;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

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
        $this->currentDate = Carbon::now();
        $this->generateCalendarDays();
    }

    public function generateCalendarDays()
    {
        $this->calendarDays = [];
        
        $year = $this->currentDate->year;
        $month = $this->currentDate->month;
        
        $firstDayOfMonth = Carbon::createFromDate($year, $month, 1);
        $lastDayOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeek;
        
        // Previous month days
        $prevMonthDays = [];
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $date = Carbon::createFromDate($year, $month, 1)->subDays($firstDayOfWeek - $i);
            $prevMonthDays[] = $this->buildDayData($date, false);
        }
        
        // Current month days
        $currentMonthDays = [];
        for ($i = 1; $i <= $lastDayOfMonth->day; $i++) {
            $date = Carbon::createFromDate($year, $month, $i);
            $currentMonthDays[] = $this->buildDayData($date, true);
        }
        
        // Next month days
        $totalDays = count($prevMonthDays) + count($currentMonthDays);
        $remainingDays = 42 - $totalDays;
        
        $nextMonthDays = [];
        for ($i = 1; $i <= $remainingDays; $i++) {
            $date = Carbon::createFromDate($year, $month, 1)->addMonth()->addDays($i - 1);
            $nextMonthDays[] = $this->buildDayData($date, false);
        }
        
        $this->calendarDays = array_merge($prevMonthDays, $currentMonthDays, $nextMonthDays);
    }

    protected function buildDayData(Carbon $date, bool $isCurrentMonth): array
    {
        return [
            'date' => $date->format('Y-m-d'),
            'day' => $date->day,
            'isCurrentMonth' => $isCurrentMonth,
            'hasEvent' => $this->hasTaxEvent($date),
            'isToday' => $date->isToday(),
            'pendingClientsCount' => $this->getPendingClientsCount($date),
            'eventType' => $this->getEventType($date),
        ];
    }

    protected function getEventType(Carbon $date): ?string
    {
        $day = $date->day;
        
        if ($day == 15) return 'payment';
        if ($day == 20) return 'pph';
        if ($this->isLastDayOfMonth($date)) return 'ppn';
        
        return null;
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

    protected function hasTaxEvent(Carbon $date): bool
    {
        $day = $date->day;
        return $day == 15 || $day == 20 || $this->isLastDayOfMonth($date);
    }

    protected function getTaxEventsForDate(Carbon $date)
    {
        $taxEvents = $this->getTaxEvents();
        $dateString = $date->format('Y-m-d');
        return collect($taxEvents)->where('date', $dateString)->values()->all();
    }

    public function getTaxSchedule()
    {
        $currentYear = $this->currentDate->year;
        $currentMonth = $this->currentDate->month;
        
        $taxEvents = $this->getTaxEvents();
        
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
        $currentYear = $this->currentDate->year;
        $currentMonth = $this->currentDate->month;
        
        $lastDay = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->format('Y-m-d');
        
        $targetMonth = $this->currentDate->copy()->subMonth();
        $targetMonthName = $targetMonth->translatedFormat('F Y');
        
        return [
            [
                'date' => $lastDay,
                'title' => 'Batas Akhir Lapor SPT Masa PPN',
                'description' => "Batas akhir lapor SPT Masa PPN periode {$targetMonthName}",
                'actionText' => 'Kelola PPN',
                'actionLink' => route('filament.admin.resources.tax-reports.index'),
                'type' => 'report',
                'priority' => 'high',
                'icon' => 'document-text'
            ],
            [
                'date' => Carbon::createFromDate($currentYear, $currentMonth, 15)->format('Y-m-d'),
                'title' => 'Batas Akhir Setor PPh dan PPN',
                'description' => "Batas akhir setor PPh dan PPN periode {$targetMonthName}",
                'actionText' => 'Kelola Pembayaran',
                'actionLink' => route('filament.admin.resources.tax-reports.index'),
                'type' => 'payment',
                'priority' => 'high',
                'icon' => 'banknotes'
            ],
            [
                'date' => Carbon::createFromDate($currentYear, $currentMonth, 20)->format('Y-m-d'),
                'title' => 'Batas Akhir Lapor SPT Masa PPh 21',
                'description' => "Batas akhir lapor SPT Masa PPh 21 periode {$targetMonthName}",
                'actionText' => 'Kelola PPh 21',
                'actionLink' => route('filament.admin.resources.tax-reports.index'),
                'type' => 'report',
                'priority' => 'medium',
                'icon' => 'document-check'
            ],
        ];
    }

    protected function getPendingClientsCount(Carbon $date)
    {
        $day = $date->day;
        
        if ($day == 15) {
            return $this->getUnpaidTaxClientsCount($date);
        } elseif ($day == 20) {
            return $this->getUnreportedPPhClientsCount($date);
        } elseif ($this->isLastDayOfMonth($date)) {
            return $this->getUnreportedPPNClientsCount($date);
        }
        
        return 0;
    }
    
    protected function isLastDayOfMonth(Carbon $date)
    {
        return $date->day === $date->copy()->endOfMonth()->day;
    }
    
    /**
     * FIXED: Get count of clients with unreported PPN using tax_calculation_summaries
     */
    protected function getUnpaidTaxClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->where('tax_reports.month', $monthName)
            ->where('tax_calculation_summaries.tax_type', 'ppn')
            ->where('tax_calculation_summaries.report_status', 'Belum Lapor')
            ->distinct('tax_reports.id')
            ->count('tax_reports.id');
    }
    
    /**
     * FIXED: Get count of clients with unreported PPh using tax_calculation_summaries
     */
    protected function getUnreportedPPhClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->where('tax_reports.month', $monthName)
            ->where('tax_calculation_summaries.tax_type', 'pph')
            ->where('tax_calculation_summaries.report_status', 'Belum Lapor')
            ->distinct('tax_reports.id')
            ->count('tax_reports.id');
    }
    
    /**
     * FIXED: Get count of clients with unreported PPN using tax_calculation_summaries
     */
    protected function getUnreportedPPNClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->where('tax_reports.month', $monthName)
            ->where('tax_calculation_summaries.tax_type', 'ppn')
            ->where('tax_calculation_summaries.report_status', 'Belum Lapor')
            ->distinct('tax_reports.id')
            ->count('tax_reports.id');
    }
    
    /**
     * FIXED: Get pending clients with proper join to tax_calculation_summaries
     */
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
            
            // Get tax reports with unreported PPN
            $taxReports = TaxReport::with(['client', 'ppnSummary'])
                ->where('month', $targetMonthFormatted)
                ->whereHas('ppnSummary', function($q) {
                    $q->where('report_status', 'Belum Lapor');
                })
                ->get();
                
            foreach ($taxReports as $report) {
                $ppnSummary = $report->ppnSummary;
                
                $clients[] = [
                    'id' => $report->client->id,
                    'tax_report_id' => $report->id,
                    'name' => $report->client->name,
                    'logo' => $report->client->logo,
                    'status' => 'Belum bayar PPN',
                    'dueAmount' => $ppnSummary ? $ppnSummary->pajak_keluar : 0,
                    'NPWP' => $report->client->NPWP ?? 'Tidak Ada',
                    'statusBadge' => $ppnSummary?->status_final ?? 'Nihil'
                ];
            }
        }
        elseif ($day == 20) {
            $reportType = "Lapor SPT Masa PPh 21 untuk periode {$monthName}";
            
            $taxReports = TaxReport::with(['client', 'pphSummary'])
                ->where('month', $targetMonthFormatted)
                ->whereHas('pphSummary', function($q) {
                    $q->where('report_status', 'Belum Lapor');
                })
                ->get();
                
            foreach ($taxReports as $report) {
                $employeeCount = $report->client->employees()->count();
                $pphSummary = $report->pphSummary;
                
                $clients[] = [
                    'id' => $report->client->id,
                    'tax_report_id' => $report->id,
                    'name' => $report->client->name,
                    'logo' => $report->client->logo,
                    'status' => 'Belum lapor PPh 21',
                    'employees' => $employeeCount,
                    'NPWP' => $report->client->NPWP ?? 'Tidak Ada',
                    'pphAmount' => $pphSummary ? $pphSummary->pajak_keluar : 0
                ];
            }
            
        } elseif ($this->isLastDayOfMonth($date)) {
            $reportType = "Lapor SPT Masa PPN untuk periode {$monthName}";
            
            $taxReports = TaxReport::with(['client', 'ppnSummary'])
                ->where('month', $targetMonthFormatted)
                ->whereHas('ppnSummary', function($q) {
                    $q->where('report_status', 'Belum Lapor');
                })
                ->get();
                
            foreach ($taxReports as $report) {
                $transactionCount = $report->originalInvoices()->count();
                $ppnSummary = $report->ppnSummary;
                
                $clients[] = [
                    'id' => $report->client->id,
                    'tax_report_id' => $report->id,
                    'name' => $report->client->name,
                    'logo' => $report->client->logo,
                    'status' => 'Belum lapor PPN',
                    'transaksiCount' => $transactionCount,
                    'NPWP' => $report->client->NPWP ?? 'Tidak Ada',
                    'ppnAmount' => $ppnSummary ? $ppnSummary->pajak_keluar : 0,
                    'statusBadge' => $ppnSummary?->status_final ?? 'Nihil'
                ];
            }
        }
        
        return [
            'reportType' => $reportType,
            'date' => $date->translatedFormat('d F Y'),
            'clients' => $clients
        ];
    }

    public function sendMassReminder()
    {
        try {
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
                            ->url(route('filament.admin.resources.tax-reports.index'))
                            ->markAsRead(),
                        \Filament\Notifications\Actions\Action::make('dismiss')
                            ->label('Tutup')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($manager)
                    ->broadcast($manager);
            }

            $this->dispatch('close-modal', ['id' => 'pending-clients-modal']);

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

            report($e);
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
        return view('livewire.tax-report.dashboard.tax-calendar');
    }
}