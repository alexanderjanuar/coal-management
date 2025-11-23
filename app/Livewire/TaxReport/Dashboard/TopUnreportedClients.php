<?php

namespace App\Livewire\TaxReport\Dashboard;

use App\Models\TaxReport;
use App\Models\TaxCalculationSummary;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TopUnreportedClients extends Component
{
    public $topClients = [];
    public $loading = true;
    
    // Filter properties
    public $filters = [
        'date_range' => 'this_year',
        'from' => null,
        'to' => null,
        'client_id' => null,
        'tax_type' => null,
        'report_status' => null,
        'payment_status' => null,
    ];

    public function mount()
    {
        // Set default date range
        $this->filters['from'] = now()->startOfYear()->format('Y-m-d');
        $this->filters['to'] = now()->endOfYear()->format('Y-m-d');
        
        $this->loadTopUnreportedClients();
    }

    #[On('filtersUpdated')]
    public function updateFilters($filters)
    {
        $this->filters = array_merge($this->filters, $filters);
        $this->loadTopUnreportedClients();
    }

    public function loadTopUnreportedClients()
    {
        $this->loading = true;
        
        try {
            // Build base query
            $query = TaxCalculationSummary::query()
                ->select([
                    'tax_reports.id',
                    'tax_reports.month',
                    'clients.name as client_name',
                    'clients.id as client_id',
                    'clients.logo as client_logo',
                    'tax_calculation_summaries.pajak_keluar as total_ppn',
                    'tax_calculation_summaries.report_status',
                    'tax_calculation_summaries.tax_type',
                    DB::raw('(SELECT COUNT(*) FROM invoices WHERE invoices.tax_report_id = tax_reports.id AND invoices.is_revision = false) as total_invoices'),
                    DB::raw('(SELECT SUM(dpp) FROM invoices WHERE invoices.tax_report_id = tax_reports.id AND invoices.type = "Faktur Keluaran" AND invoices.is_revision = false) as total_peredaran_bruto')
                ])
                ->join('tax_reports', 'tax_calculation_summaries.tax_report_id', '=', 'tax_reports.id')
                ->join('clients', 'tax_reports.client_id', '=', 'clients.id')
                ->where('clients.status', 'Active');

            // Apply date range filter
            if ($this->filters['from'] && $this->filters['to']) {
                $fromDate = Carbon::parse($this->filters['from']);
                $toDate = Carbon::parse($this->filters['to']);
                
                // Get months in the date range
                $months = [];
                $current = $fromDate->copy()->startOfMonth();
                while ($current <= $toDate->endOfMonth()) {
                    $months[] = $current->format('F');
                    $current->addMonth();
                }
                
                if (!empty($months)) {
                    $query->whereIn('tax_reports.month', $months);
                }
            }

            // Apply client filter
            if ($this->filters['client_id']) {
                $query->where('clients.id', $this->filters['client_id']);
            }

            // Apply tax type filter (default to PPN if not specified for this widget)
            $taxType = $this->filters['tax_type'] ?? 'ppn';
            $query->where('tax_calculation_summaries.tax_type', $taxType);

            // Apply report status filter (default to Belum Lapor for this widget)
            $reportStatus = $this->filters['report_status'] ?? 'Belum Lapor';
            $query->where('tax_calculation_summaries.report_status', $reportStatus);

            // Apply payment status filter
            if ($this->filters['payment_status']) {
                $query->where('tax_calculation_summaries.status_final', $this->filters['payment_status']);
            }

            // Get results and process
            $results = $query
                ->orderByDesc(DB::raw('(SELECT SUM(dpp) FROM invoices WHERE invoices.tax_report_id = tax_reports.id AND invoices.type = "Faktur Keluaran" AND invoices.is_revision = false)'))
                ->limit(5)
                ->get();

            // Enrich with all tax types status
            $this->topClients = $results->map(function ($client) {
                // Get all tax summaries for this tax report
                $ppnSummary = TaxCalculationSummary::where('tax_report_id', $client->id)
                    ->where('tax_type', 'ppn')
                    ->first();
                $pphSummary = TaxCalculationSummary::where('tax_report_id', $client->id)
                    ->where('tax_type', 'pph')
                    ->first();
                $bupotSummary = TaxCalculationSummary::where('tax_report_id', $client->id)
                    ->where('tax_type', 'bupot')
                    ->first();

                $client->ppn_report_status = $ppnSummary?->report_status ?? 'Sudah Lapor';
                $client->pph_report_status = $pphSummary?->report_status ?? 'Sudah Lapor';
                $client->bupot_report_status = $bupotSummary?->report_status ?? 'Sudah Lapor';
                
                // Add payment status
                $client->payment_status = $ppnSummary?->status_final ?? 'Nihil';

                return $client;
            });

        } catch (\Exception $e) {
            \Log::error('Error loading top unreported clients: ' . $e->getMessage());
            $this->topClients = collect([]);
        }

        $this->loading = false;
    }

    public function getUnreportedCount($client)
    {
        $count = 0;
        if ($client->ppn_report_status === 'Belum Lapor') $count++;
        if ($client->pph_report_status === 'Belum Lapor') $count++;
        if ($client->bupot_report_status === 'Belum Lapor') $count++;
        return $count;
    }

    public function formatCurrency($amount)
    {
        if (!$amount) return 'Rp 0';
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function formatCurrencyShort($amount)
    {
        if (!$amount) return 'Rp 0';
        
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
        } elseif ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' Jt';
        } elseif ($amount >= 1000) {
            return 'Rp ' . number_format($amount / 1000, 1, ',', '.') . ' Rb';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getTaxReportUrl($taxReportId)
    {
        return route('filament.admin.resources.tax-reports.view', ['record' => $taxReportId]);
    }

    public function getClientUrl($clientId)
    {
        return route('filament.admin.resources.clients.view', ['record' => $clientId]);
    }

    public function getFilterSummary()
    {
        $summary = [];
        
        // Date range
        if ($this->filters['from'] && $this->filters['to']) {
            $from = Carbon::parse($this->filters['from']);
            $to = Carbon::parse($this->filters['to']);
            
            if ($from->isSameMonth($to)) {
                $summary[] = $from->format('F Y');
            } else {
                $summary[] = $from->format('M Y') . ' - ' . $to->format('M Y');
            }
        }
        
        // Tax type
        if ($this->filters['tax_type']) {
            $taxTypeLabels = [
                'ppn' => 'PPN',
                'pph' => 'PPh',
                'bupot' => 'Bupot'
            ];
            $summary[] = $taxTypeLabels[$this->filters['tax_type']] ?? '';
        }
        
        // Report status
        if ($this->filters['report_status']) {
            $summary[] = $this->filters['report_status'];
        }
        
        // Payment status
        if ($this->filters['payment_status']) {
            $summary[] = $this->filters['payment_status'];
        }
        
        return !empty($summary) ? implode(' • ', $summary) : 'Semua Data';
    }

    public function render()
    {
        return view('livewire.tax-report.dashboard.top-unreported-clients');
    }
}