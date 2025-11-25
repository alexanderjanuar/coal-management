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
use Kenepa\Banner\Facades\BannerManager;
use Kenepa\Banner\ValueObjects\BannerData;

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
        
        if ($day == 10) return 'pph_report'; // PPh 21 & PPh Unifikasi Report
        if ($day == 20) return 'ppn_report'; // PPN Report
        if ($day == 30) return 'payment_warning'; // Final Payment Warning
        
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
        return $day == 10 || $day == 20 || $day == 30;
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
        
        $targetMonth = $this->currentDate->copy()->subMonth();
        $targetMonthName = $targetMonth->translatedFormat('F Y');
        
        return [
            [
                'date' => Carbon::createFromDate($currentYear, $currentMonth, 10)->format('Y-m-d'),
                'title' => 'Batas Akhir Lapor PPh 21 & PPh Unifikasi',
                'description' => "Batas akhir lapor SPT Masa PPh 21 dan PPh Unifikasi periode {$targetMonthName}",
                'actionText' => 'Kelola PPh',
                'actionLink' => route('filament.admin.resources.tax-reports.index'),
                'type' => 'report',
                'priority' => 'high',
                'icon' => 'document-check'
            ],
            [
                'date' => Carbon::createFromDate($currentYear, $currentMonth, 20)->format('Y-m-d'),
                'title' => 'Batas Akhir Lapor PPN',
                'description' => "Batas akhir lapor SPT Masa PPN periode {$targetMonthName}",
                'actionText' => 'Kelola PPN',
                'actionLink' => route('filament.admin.resources.tax-reports.index'),
                'type' => 'report',
                'priority' => 'high',
                'icon' => 'document-text'
            ],
            [
                'date' => Carbon::createFromDate($currentYear, $currentMonth, 30)->format('Y-m-d'),
                'title' => 'Batas Akhir Bayar PPN, PPh 21 & PPh Unifikasi',
                'description' => "Peringatan terakhir: Batas akhir pembayaran semua jenis pajak periode {$targetMonthName}",
                'actionText' => 'Kelola Pembayaran',
                'actionLink' => route('filament.admin.resources.tax-reports.index'),
                'type' => 'payment',
                'priority' => 'high',
                'icon' => 'banknotes'
            ],
        ];
    }

    protected function getPendingClientsCount(Carbon $date)
    {
        $day = $date->day;
        
        if ($day == 10) {
            // PPh 21 & PPh Unifikasi Report - Count UNIQUE clients with unreported PPh OR Bupot
            return $this->getUniquePPhClientsCount($date);
        } elseif ($day == 20) {
            // PPN Report
            return $this->getUnreportedPPNClientsCount($date);
        } elseif ($day == 30) {
            // Payment Warning - All unpaid
            return $this->getUnpaidTaxClientsCount($date);
        }
        
        return 0;
    }
    
    /**
     * Get count of ACTIVE clients with unreported PPN using tax_calculation_summaries
     */
    protected function getUnreportedPPNClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
            ->where('tax_reports.month', $monthName)
            ->where('clients.status', 'Active') // FILTER ACTIVE CLIENTS
            ->where('tax_calculation_summaries.tax_type', 'ppn')
            ->where('tax_calculation_summaries.report_status', 'Belum Lapor')
            ->distinct('tax_reports.id')
            ->count('tax_reports.id');
    }
    
    /**
     * Get count of ACTIVE clients with unreported PPh using tax_calculation_summaries
     */
    protected function getUnreportedPPhClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
            ->where('tax_reports.month', $monthName)
            ->where('clients.status', 'Active') // FILTER ACTIVE CLIENTS
            ->where('tax_calculation_summaries.tax_type', 'pph')
            ->where('tax_calculation_summaries.report_status', 'Belum Lapor')
            ->distinct('tax_reports.id')
            ->count('tax_reports.id');
    }
    
    /**
     * Get count of ACTIVE clients with unreported Bupot using tax_calculation_summaries
     */
    protected function getUnreportedBupotClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
            ->where('tax_reports.month', $monthName)
            ->where('clients.status', 'Active') // FILTER ACTIVE CLIENTS
            ->where('tax_calculation_summaries.tax_type', 'bupot')
            ->where('tax_calculation_summaries.report_status', 'Belum Lapor')
            ->distinct('tax_reports.id')
            ->count('tax_reports.id');
    }
    
    /**
     * Get count of UNIQUE ACTIVE clients with unreported PPh (PPh 21 OR Bupot)
     * This prevents double-counting when a client has both PPh 21 and Bupot unreported
     */
    protected function getUniquePPhClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        // Get unique client IDs that have either PPh 21 or Bupot (or both) unreported
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
            ->where('tax_reports.month', $monthName)
            ->where('clients.status', 'Active') // FILTER ACTIVE CLIENTS
            ->whereIn('tax_calculation_summaries.tax_type', ['pph', 'bupot'])
            ->where('tax_calculation_summaries.report_status', 'Belum Lapor')
            ->distinct('clients.id') // Count unique CLIENTS, not tax reports
            ->count('clients.id');
    }
    
    /**
     * Get count of ACTIVE clients with unpaid taxes (any type)
     */
    protected function getUnpaidTaxClientsCount(Carbon $date)
    {
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $monthName = $targetMonth->format('F');
        
        return DB::table('tax_calculation_summaries')
            ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
            ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
            ->where('tax_reports.month', $monthName)
            ->where('clients.status', 'Active') // FILTER ACTIVE CLIENTS
            ->where('tax_calculation_summaries.bayar_status', 'Belum Bayar')
            ->where('tax_calculation_summaries.status_final', '!=', 'Nihil') // Only count if there's actual tax to pay
            ->distinct('tax_reports.id')
            ->count('tax_reports.id');
    }
    
    /**
     * Get pending ACTIVE clients with proper join to tax_calculation_summaries
     */
    protected function getPendingClients(Carbon $date)
    {
        $day = $date->day;
        
        $targetMonth = $date->copy()->startOfMonth()->subMonth();
        $targetMonthFormatted = $targetMonth->format('F');
        $monthName = $targetMonth->translatedFormat('F Y');
        
        $reportType = '';
        $clients = [];
        
        if ($day == 10) {
            // PPh 21 & PPh Unifikasi Report
            $reportType = "Lapor PPh 21 & PPh Unifikasi untuk periode {$monthName}";
            
            // Use associative array with client_id as key to prevent duplicates
            $clientsMap = [];
            
            // Get PPh 21 unreported
            $pphReports = TaxReport::with(['client', 'pphSummary'])
                ->where('month', $targetMonthFormatted)
                ->whereHas('client', function($q) {
                    $q->where('status', 'Active'); // FILTER ACTIVE CLIENTS
                })
                ->whereHas('pphSummary', function($q) {
                    $q->where('report_status', 'Belum Lapor');
                })
                ->get();
                
            foreach ($pphReports as $report) {
                $clientId = $report->client->id;
                $employeeCount = $report->client->employees()->count();
                $pphSummary = $report->pphSummary;
                
                // Initialize or update client entry
                if (!isset($clientsMap[$clientId])) {
                    $clientsMap[$clientId] = [
                        'id' => $clientId,
                        'tax_report_id' => $report->id,
                        'name' => $report->client->name,
                        'logo' => $report->client->logo,
                        'NPWP' => $report->client->NPWP ?? 'Tidak Ada',
                        'employees' => $employeeCount,
                        'status' => '',
                        'taxTypes' => [],
                        'pphAmount' => 0,
                        'bupotAmount' => 0,
                        'bupotCount' => 0,
                    ];
                }
                
                // Add PPh 21 info
                $clientsMap[$clientId]['status'] = 'Belum lapor PPh 21';
                $clientsMap[$clientId]['taxTypes'][] = 'PPh 21';
                $clientsMap[$clientId]['pphAmount'] = $pphSummary ? $pphSummary->pajak_keluar : 0;
            }
            
            // Get PPh Unifikasi unreported
            $bupotReports = TaxReport::with(['client', 'bupotSummary'])
                ->where('month', $targetMonthFormatted)
                ->whereHas('client', function($q) {
                    $q->where('status', 'Active'); // FILTER ACTIVE CLIENTS
                })
                ->whereHas('bupotSummary', function($q) {
                    $q->where('report_status', 'Belum Lapor');
                })
                ->get();
                
            foreach ($bupotReports as $report) {
                $clientId = $report->client->id;
                $bupotCount = $report->bupots()->count();
                $bupotSummary = $report->bupotSummary;
                
                // Initialize or update client entry
                if (!isset($clientsMap[$clientId])) {
                    $employeeCount = $report->client->employees()->count();
                    $clientsMap[$clientId] = [
                        'id' => $clientId,
                        'tax_report_id' => $report->id,
                        'name' => $report->client->name,
                        'logo' => $report->client->logo,
                        'NPWP' => $report->client->NPWP ?? 'Tidak Ada',
                        'employees' => $employeeCount,
                        'status' => '',
                        'taxTypes' => [],
                        'pphAmount' => 0,
                        'bupotAmount' => 0,
                        'bupotCount' => 0,
                    ];
                }
                
                // Add Bupot info
                $clientsMap[$clientId]['taxTypes'][] = 'PPh Unifikasi';
                $clientsMap[$clientId]['bupotCount'] = $bupotCount;
                $clientsMap[$clientId]['bupotAmount'] = $bupotSummary ? $bupotSummary->pajak_keluar : 0;
            }
            
            // Build status text based on what's unreported
            foreach ($clientsMap as $clientId => &$client) {
                $taxTypes = $client['taxTypes'];
                if (count($taxTypes) == 2) {
                    $client['status'] = 'Belum lapor PPh 21 & PPh Unifikasi';
                    $client['taxType'] = 'PPh 21 & PPh Unifikasi';
                } elseif (in_array('PPh 21', $taxTypes)) {
                    $client['status'] = 'Belum lapor PPh 21';
                    $client['taxType'] = 'PPh 21';
                } else {
                    $client['status'] = 'Belum lapor PPh Unifikasi';
                    $client['taxType'] = 'PPh Unifikasi';
                }
            }
            
            // Convert to indexed array
            $clients = array_values($clientsMap);
        }
        elseif ($day == 20) {
            // PPN Report
            $reportType = "Lapor PPN untuk periode {$monthName}";
            
            $taxReports = TaxReport::with(['client', 'ppnSummary'])
                ->where('month', $targetMonthFormatted)
                ->whereHas('client', function($q) {
                    $q->where('status', 'Active'); // FILTER ACTIVE CLIENTS
                })
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
                    'statusBadge' => $ppnSummary?->status_final ?? 'Nihil',
                    'taxType' => 'PPN'
                ];
            }
        }
        elseif ($day == 30) {
            // Payment Warning - All Unpaid
            $reportType = "Bayar PPN, PPh 21 & PPh Unifikasi untuk periode {$monthName} (PERINGATAN TERAKHIR)";
            
            // Get all tax reports with unpaid taxes
            $taxReports = TaxReport::with(['client', 'taxCalculationSummaries'])
                ->where('month', $targetMonthFormatted)
                ->whereHas('client', function($q) {
                    $q->where('status', 'Active'); // FILTER ACTIVE CLIENTS
                })
                ->whereHas('taxCalculationSummaries', function($q) {
                    $q->where('bayar_status', 'Belum Bayar')
                      ->where('status_final', '!=', 'Nihil'); // Only unpaid with actual tax
                })
                ->get();
                
            foreach ($taxReports as $report) {
                $unpaidSummaries = $report->taxCalculationSummaries
                    ->where('bayar_status', 'Belum Bayar')
                    ->where('status_final', '!=', 'Nihil');
                
                if ($unpaidSummaries->isNotEmpty()) {
                    $totalUnpaid = $unpaidSummaries->sum(function($summary) {
                        return abs($summary->saldo_final);
                    });
                    
                    $unpaidTypes = $unpaidSummaries->pluck('tax_type')->map(function($type) {
                        return match($type) {
                            'ppn' => 'PPN',
                            'pph' => 'PPh 21',
                            'bupot' => 'PPh Unifikasi',
                            default => $type
                        };
                    })->join(', ');
                    
                    $clients[] = [
                        'id' => $report->client->id,
                        'tax_report_id' => $report->id,
                        'name' => $report->client->name,
                        'logo' => $report->client->logo,
                        'status' => 'Belum bayar: ' . $unpaidTypes,
                        'NPWP' => $report->client->NPWP ?? 'Tidak Ada',
                        'totalUnpaid' => $totalUnpaid,
                        'unpaidCount' => $unpaidSummaries->count(),
                        'taxType' => 'Pembayaran'
                    ];
                }
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
            
            // Extract just the action text (e.g., "Lapor PPh 21 & PPh Unifikasi")
            $actionText = $reportType;
            if (strpos($reportType, 'untuk periode') !== false) {
                $actionText = substr($reportType, 0, strpos($reportType, 'untuk periode'));
            }
            
            // Determine icon and urgent message based on report type
            $icon = 'heroicon-o-exclamation-triangle';
            $urgentText = '⚠️ URGENT';
            
            if (strpos($reportType, 'PPh') !== false && strpos($reportType, 'Bayar') === false) {
                $icon = 'heroicon-o-document-text';
                $urgentText = '📋 LAPOR PPh';
            } elseif (strpos($reportType, 'PPN') !== false && strpos($reportType, 'Bayar') === false) {
                $icon = 'heroicon-o-document-check';
                $urgentText = '📄 LAPOR PPN';
            } elseif (strpos($reportType, 'Bayar') !== false) {
                $icon = 'heroicon-o-banknotes';
                $urgentText = '💰 PEMBAYARAN TERAKHIR';
            }

            // Send notifications to project managers
            foreach ($projectManagers as $manager) {
                Notification::make()
                    ->title('⚠️ Pengingat Mendesak: Klien Tertunggak')
                    ->body("{$clientCount} klien AKTIF belum {$reportType}. Segera tindak lanjuti untuk menghindari denda dan sanksi pajak!")
                    ->icon($icon)
                    ->color('danger')
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->label('🔍 Lihat Detail Sekarang')
                            ->url(route('filament.admin.resources.tax-reports.index'))
                            ->button()
                            ->color('danger')
                            ->markAsRead(),
                        \Filament\Notifications\Actions\Action::make('dismiss')
                            ->label('Tutup')
                            ->color('gray')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($manager)
                    ->broadcast($manager);
            }

            // Create urgent banner for all users
            $bannerData = new BannerData(
                id: 'tax_reminder_' . time(),
                name: 'Pengingat Pajak Mendesak',
                content: "{$urgentText}: {$clientCount} klien AKTIF belum {$actionText} pada {$date}! Klik untuk melihat detail.",
                is_active: true,
                active_since: now()->format('Y-m-d'),
                icon: $icon,
                background_type: 'gradient',
                start_color: '#DC2626', // Red-600
                end_color: '#991B1B', // Red-800
                start_time: '00:00',
                end_time: '23:59',
                can_be_closed_by_user: true,
                text_color: '#FFFFFF',
                icon_color: '#FEE2E2', // Red-100
                render_location: 'Header',
                scope: [],
                link_url: route('filament.admin.resources.tax-reports.index'),
                link_text: '🔍 Lihat Detail Klien',
                link_click_action: 'redirect',
                link_button_style: 'filled',
                link_button_color: '#FFFFFF',
                link_text_color: '#DC2626',
                link_active: 'true',
                link_open_in_new_tab: false,
                link_button_icon: 'heroicon-o-arrow-right',
                link_button_icon_color: '#DC2626',
            );

            BannerManager::store($bannerData);

            // Close the modal
            $this->dispatch('close-modal', ['id' => 'pending-clients-modal']);

            // Success notification
            Notification::make()
                ->title('✅ Pengingat Berhasil Dikirim')
                ->body("Pengingat telah dikirim ke {$projectManagers->count()} project manager dan banner ditampilkan untuk semua pengguna.")
                ->success()
                ->duration(5000)
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Gagal Mengirim Pengingat')
                ->body('Terjadi kesalahan saat mengirim pengingat: ' . $e->getMessage())
                ->danger()
                ->persistent()
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