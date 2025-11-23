<?php

namespace App\Livewire\TaxReport\Dashboard;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\Client;
use App\Models\TaxReport;
use Illuminate\Support\Carbon;

class Filters extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'livewire.tax-report.dashboard.filters';
    
    public ?array $data = [];
    
    protected static ?int $sort = 1; // Display above other widgets

    public function mount(): void
    {
        // Set default values
        $this->form->fill([
            'date_range' => 'this_year',
            'from' => now()->startOfYear()->format('Y-m-d'),
            'to' => now()->endOfYear()->format('Y-m-d'),
            'client_id' => null,
            'tax_type' => null,
            'report_status' => null,
            'payment_status' => null,
        ]);

        // Dispatch initial events
        $this->dispatchFilters();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                // Date Range (hidden, used for internal state)
                Select::make('date_range')
                    ->options([
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_quarter' => 'This Quarter',
                        'last_quarter' => 'Last Quarter',
                        'this_year' => 'This Year',
                        'last_year' => 'Last Year',
                        'custom' => 'Custom',
                    ])
                    ->default('this_year')
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->updateDateRange($state);
                        $this->dispatchFilters();
                    })
                    ->hiddenLabel()
                    ->extraAttributes(['style' => 'display: none;']),

                // From Date
                DatePicker::make('from')
                    ->label('From Date')
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        if ($state) {
                            $this->dispatch('updateFromDate', from: $state);
                            $this->dispatchFilters();
                        }
                    })
                    ->displayFormat('M Y'),

                // To Date
                DatePicker::make('to')
                    ->label('To Date')
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        if ($state) {
                            $this->dispatch('updateToDate', to: $state);
                            $this->dispatchFilters();
                        }
                    })
                    ->displayFormat('M Y'),

                // Client Filter
                Select::make('client_id')
                    ->label('Client')
                    ->options($this->getClientOptions())
                    ->placeholder('All Clients')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->dispatch('updateClient', client_id: $state);
                        $this->dispatchFilters();
                    })
                    ->native(false),

                // Tax Type Filter
                Select::make('tax_type')
                    ->label('Tax Type')
                    ->options([
                        'ppn' => 'PPN',
                        'pph' => 'PPh',
                        'bupot' => 'Bupot',
                    ])
                    ->placeholder('All Tax Types')
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->dispatch('updateTaxType', tax_type: $state);
                        $this->dispatchFilters();
                    })
                    ->native(false),

                // Report Status Filter
                Select::make('report_status')
                    ->label('Report Status')
                    ->options([
                        'Belum Lapor' => 'Belum Lapor',
                        'Sudah Lapor' => 'Sudah Lapor',
                    ])
                    ->placeholder('All Status')
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->dispatch('updateReportStatus', report_status: $state);
                        $this->dispatchFilters();
                    })
                    ->native(false),

                // Payment Status Filter
                Select::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'Lebih Bayar' => 'Lebih Bayar',
                        'Kurang Bayar' => 'Kurang Bayar',
                        'Nihil' => 'Nihil',
                    ])
                    ->placeholder('All Payment Status')
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->dispatch('updatePaymentStatus', payment_status: $state);
                        $this->dispatchFilters();
                    })
                    ->native(false),
            ]);
    }

    // Method to handle date range selection from view
    public function setDateRange(string $range): void
    {
        $this->updateDateRange($range);
        $this->form->fill(array_merge($this->form->getState(), ['date_range' => $range]));
        $this->dispatchFilters();
    }

    // Method to reset filters
    public function resetFilters(): void
    {
        $this->form->fill([
            'date_range' => 'this_year',
            'from' => now()->startOfYear()->format('Y-m-d'),
            'to' => now()->endOfYear()->format('Y-m-d'),
            'client_id' => null,
            'tax_type' => null,
            'report_status' => null,
            'payment_status' => null,
        ]);
        $this->dispatchFilters();
    }

    // Method to get display date based on current filter
    public function getDisplayDate(): string
    {
        $data = $this->form->getState();
        $dateRange = $data['date_range'] ?? 'this_year';

        return match($dateRange) {
            'this_month' => now()->format('M Y'),
            'last_month' => now()->subMonth()->format('M Y'),
            'this_quarter' => 'Q' . now()->quarter . ' ' . now()->format('Y'),
            'last_quarter' => 'Q' . now()->subQuarter()->quarter . ' ' . now()->subQuarter()->format('Y'),
            'this_year' => now()->format('Y'),
            'last_year' => now()->subYear()->format('Y'),
            'custom' => ($data['from'] && $data['to']) 
                ? \Carbon\Carbon::parse($data['from'])->format('M Y') . ' - ' . \Carbon\Carbon::parse($data['to'])->format('M Y')
                : now()->format('M Y'),
            default => now()->format('Y')
        };
    }

    protected function updateDateRange(string $range): void
    {
        $dates = $this->getDateRangeFromString($range);
        
        if ($range !== 'custom') {
            $currentData = $this->form->getState();
            $currentData['from'] = $dates['from'];
            $currentData['to'] = $dates['to'];
            $this->form->fill($currentData);
        }

        $this->dispatch('updateDateRange', 
            range: $range,
            from: $dates['from'],
            to: $dates['to']
        );
    }

    protected function getDateRangeFromString(string $range): array
    {
        return match($range) {
            'this_month' => [
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to' => now()->endOfMonth()->format('Y-m-d'),
            ],
            'last_month' => [
                'from' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                'to' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'this_quarter' => [
                'from' => now()->startOfQuarter()->format('Y-m-d'),
                'to' => now()->endOfQuarter()->format('Y-m-d'),
            ],
            'last_quarter' => [
                'from' => now()->subQuarter()->startOfQuarter()->format('Y-m-d'),
                'to' => now()->subQuarter()->endOfQuarter()->format('Y-m-d'),
            ],
            'this_year' => [
                'from' => now()->startOfYear()->format('Y-m-d'),
                'to' => now()->endOfYear()->format('Y-m-d'),
            ],
            'last_year' => [
                'from' => now()->subYear()->startOfYear()->format('Y-m-d'),
                'to' => now()->subYear()->endOfYear()->format('Y-m-d'),
            ],
            default => [
                'from' => now()->startOfYear()->format('Y-m-d'),
                'to' => now()->endOfYear()->format('Y-m-d'),
            ],
        };
    }

    protected function getClientOptions(): array
    {
        return Client::where('status', 'Active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function getMonthOptions(): array
    {
        // Get distinct months from tax reports
        return TaxReport::distinct('month')
            ->orderBy('month', 'desc')
            ->pluck('month', 'month')
            ->toArray();
    }

    protected function dispatchFilters(): void
    {
        $data = $this->form->getState();
        
        $filters = [
            'date_range' => $data['date_range'] ?? 'this_year',
            'from' => $data['from'] ?? now()->startOfYear()->format('Y-m-d'),
            'to' => $data['to'] ?? now()->endOfYear()->format('Y-m-d'),
            'client_id' => $data['client_id'] ?? null,
            'tax_type' => $data['tax_type'] ?? null,
            'report_status' => $data['report_status'] ?? null,
            'payment_status' => $data['payment_status'] ?? null,
        ];

        $this->dispatch('filtersUpdated', filters: $filters);
    }

    public static function canView(): bool
    {
        return true;
    }
}