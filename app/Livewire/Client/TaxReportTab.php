<?php

namespace App\Livewire\Client;

use App\Models\Client;
use App\Models\UserClient;
use App\Models\TaxReport;
use Livewire\Component;
use Livewire\Attributes\On;

class TaxReportTab extends Component
{
    public $clients = [];
    public $selectedClient = null;
    public $selectedMonth = null;
    
    public $currentClient = null;
    public $currentTaxReport = null;

    public function mount()
    {
        $this->loadClients();
        
        // Auto-select first client and current/latest month
        if ($this->clients->isNotEmpty()) {
            $this->selectedClient = $this->clients->first()->id;
            $this->loadClientData($this->selectedClient);
            
            // Try to select current month or latest available month
            if ($this->currentTaxReport) {
                $this->selectedMonth = $this->currentTaxReport->month;
            }
        }
    }

    public function loadClients()
    {
        // Get all clients linked to current user
        $clientIds = UserClient::where('user_id', auth()->id())
            ->pluck('client_id');

        if ($clientIds->isEmpty()) {
            $this->clients = collect([]);
            return;
        }

        $this->clients = Client::whereIn('id', $clientIds)
            ->with(['pic', 'accountRepresentative'])
            ->orderBy('name')
            ->get();
    }

    public function selectClient($clientId)
    {
        $this->selectedClient = $clientId;
        $this->loadClientData($clientId);
        
        // Emit event to child components to refresh their data
        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }
    }

    public function selectMonth($month)
    {
        $this->selectedMonth = $month;
        $this->loadMonthData($month);
        
        // Emit event to child components to refresh their data
        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }
    }

    public function loadClientData($clientId)
    {
        $this->currentClient = Client::find($clientId);
        
        if (!$this->currentClient) {
            $this->currentTaxReport = null;
            return;
        }
        
        // Get the most recent tax report for this client
        $this->currentTaxReport = TaxReport::where('client_id', $clientId)
            ->with([
                'invoices',
                'incomeTaxs',
                'bupots',
                'taxCalculationSummaries' => function($query) {
                    $query->select('id', 'tax_report_id', 'tax_type', 'report_status');
                }
            ])
            ->latest('created_at')
            ->first();
        
        if ($this->currentTaxReport) {
            $this->selectedMonth = $this->currentTaxReport->month;
        }
    }

    public function loadMonthData($month)
    {
        if (!$this->currentClient) {
            return;
        }

        // Get tax report for selected month and client
        $currentYear = $this->currentTaxReport 
            ? $this->currentTaxReport->created_at->format('Y') 
            : now()->format('Y');

        $this->currentTaxReport = TaxReport::where('client_id', $this->currentClient->id)
            ->where('month', $month)
            ->whereYear('created_at', $currentYear)
            ->with([
                'invoices',
                'incomeTaxs',
                'bupots',
                'taxCalculationSummaries' => function($query) {
                    $query->select('id', 'tax_report_id', 'tax_type', 'report_status');
                }
            ])
            ->first();

        // If no report found for this month, try to get the latest one
        if (!$this->currentTaxReport) {
            $this->currentTaxReport = TaxReport::where('client_id', $this->currentClient->id)
                ->whereYear('created_at', $currentYear)
                ->with([
                    'invoices',
                    'incomeTaxs',
                    'bupots',
                    'taxCalculationSummaries' => function($query) {
                        $query->select('id', 'tax_report_id', 'tax_type', 'report_status');
                    }
                ])
                ->latest('created_at')
                ->first();
        }
    }

    #[On('refresh-data')]
    public function refreshData()
    {
        $this->loadClients();
        if ($this->selectedClient) {
            if ($this->selectedMonth) {
                $this->loadMonthData($this->selectedMonth);
            } else {
                $this->loadClientData($this->selectedClient);
            }
        }
        
        // Emit event to child components after refresh
        if ($this->currentTaxReport) {
            $this->dispatch('taxReportChanged', $this->currentTaxReport->id);
        }
    }

    public function render()
    {
        return view('livewire.client.tax-report-tab');
    }
}