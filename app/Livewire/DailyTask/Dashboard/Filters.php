<?php

namespace App\Livewire\DailyTask\Dashboard;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use App\Models\User;
use Illuminate\Support\Carbon;

class Filters extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'livewire.daily-task.dashboard.filters';
    
    public ?array $data = [];
    
    protected static ?int $sort = 1; // Tampil di atas widgets lain

    public function mount(): void
    {
        // Set default values
        $this->form->fill([
            'date_range' => 'today',
            'from' => now()->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
            'department' => null,
            'position' => null,
            'user_id' => null,
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
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'this_week' => 'This Week',
                        'last_week' => 'Last Week',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year',
                        'custom' => 'Custom',
                    ])
                    ->default('today')
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
                    ->displayFormat('M j, Y'),

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
                    ->displayFormat('M j, Y'),

                // Department Filter
                Select::make('department')
                    ->label('Department')
                    ->options($this->getDepartmentOptions())
                    ->placeholder('All Departments')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->dispatch('updateDepartment', department: $state);
                        $this->dispatchFilters();
                    })
                    ->native(false),

                // Position Filter
                Select::make('position')
                    ->label('Position')
                    ->options($this->getPositionOptions())
                    ->placeholder('All Positions')
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (?string $state) {
                        $this->dispatch('updatePosition', position: $state);
                        $this->dispatchFilters();
                    })
                    ->native(false),
            ]);
    }

    // Add method to handle date range selection from view
    public function setDateRange(string $range): void
    {
        $this->updateDateRange($range);
        $this->form->fill(array_merge($this->form->getState(), ['date_range' => $range]));
        $this->dispatchFilters();
    }

    // Add method to reset filters
    public function resetFilters(): void
    {
        $this->form->fill([
            'date_range' => 'today',
            'from' => now()->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
            'department' => null,
            'position' => null,
        ]);
        $this->dispatchFilters();
    }

    // Add method to get display date based on current filter
    public function getDisplayDate(): string
    {
        $data = $this->form->getState();
        $dateRange = $data['date_range'] ?? 'today';

        return match($dateRange) {
            'today' => now()->format('j M Y'),
            'yesterday' => now()->subDay()->format('j M Y'),
            'this_week' => now()->startOfWeek()->format('j M') . ' - ' . now()->endOfWeek()->format('j M Y'),
            'last_week' => now()->subWeek()->startOfWeek()->format('j M') . ' - ' . now()->subWeek()->endOfWeek()->format('j M Y'),
            'this_month' => now()->format('M Y'),
            'last_month' => now()->subMonth()->format('M Y'),
            'this_year' => now()->format('Y'),
            'custom' => ($data['from'] && $data['to']) 
                ? \Carbon\Carbon::parse($data['from'])->format('j M') . ' - ' . \Carbon\Carbon::parse($data['to'])->format('j M Y')
                : now()->format('j M Y'),
            default => now()->format('j M Y')
        };
    }

    protected function updateDateRange(string $range): void
    {
        $dates = $this->getDateRangeFromString($range);
        
        if ($range !== 'custom') {
            // Update hanya field tanggal, jangan reset semua form
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
            'today' => [
                'from' => now()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
            'yesterday' => [
                'from' => now()->subDay()->format('Y-m-d'),
                'to' => now()->subDay()->format('Y-m-d'),
            ],
            'this_week' => [
                'from' => now()->startOfWeek()->format('Y-m-d'),
                'to' => now()->endOfWeek()->format('Y-m-d'),
            ],
            'last_week' => [
                'from' => now()->subWeek()->startOfWeek()->format('Y-m-d'),
                'to' => now()->subWeek()->endOfWeek()->format('Y-m-d'),
            ],
            'this_month' => [
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to' => now()->endOfMonth()->format('Y-m-d'),
            ],
            'last_month' => [
                'from' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
                'to' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            'this_year' => [
                'from' => now()->startOfYear()->format('Y-m-d'),
                'to' => now()->endOfYear()->format('Y-m-d'),
            ],
            default => [
                'from' => now()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ],
        };
    }

    protected function getDepartmentOptions(): array
    {
        return User::whereNotNull('department')
            ->distinct('department')
            ->pluck('department', 'department')
            ->toArray();
    }

    protected function getPositionOptions(): array
    {
        return User::whereNotNull('position')
            ->distinct('position')
            ->pluck('position', 'position')
            ->toArray();
    }

    protected function dispatchFilters(): void
    {
        $data = $this->form->getState();
        
        $filters = [
            'date_range' => $data['date_range'] ?? 'today',
            'from' => $data['from'] ?? now()->format('Y-m-d'),
            'to' => $data['to'] ?? now()->format('Y-m-d'),
            'department' => $data['department'] ?? null,
            'position' => $data['position'] ?? null,
        ];

        $this->dispatch('filtersUpdated', filters: $filters);
    }

    public static function canView(): bool
    {
        return true;
    }
}