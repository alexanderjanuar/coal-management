<?php

namespace App\Filament\Pages\ClientCommunication;

use Filament\Pages\Page;
use App\Models\ClientCommunication;
use App\Models\Client;
use Livewire\WithPagination;
use Carbon\Carbon;

class Index extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'Komunikasi Klien';
    
    protected static ?string $navigationGroup = 'Client Management';
    protected static ?int $navigationSort = 2;
    

    protected static string $view = 'filament.pages.client-communication.index';

    // public static function shouldRegisterNavigation(): bool
    // {
    //     return auth()->user()->hasRole(['super-admin']);
    // }


    // Filters
    public $search = '';
    public $filterClient = '';
    public $filterType = '';
    public $filterStatus = '';
    public $filterPriority = '';
    
    // Date & Time Filters
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterDateRange = 'all'; // all, today, week, month, custom
    public $filterTimeOfDay = ''; // morning, afternoon, evening, all
    
    public $sortField = 'communication_date';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterClient' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterPriority' => ['except' => ''],
        'filterDateRange' => ['except' => 'all'],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function mount()
    {
        // Set default date range to this month
        if (!$this->filterDateRange || $this->filterDateRange === 'all') {
            $this->filterDateRange = 'month';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterClient()
    {
        $this->resetPage();
    }

    public function updatedFilterDateRange($value)
    {
        // Auto-set date range based on selection
        switch ($value) {
            case 'today':
                $this->filterDateFrom = now()->format('Y-m-d');
                $this->filterDateTo = now()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->filterDateFrom = now()->subDay()->format('Y-m-d');
                $this->filterDateTo = now()->subDay()->format('Y-m-d');
                break;
            case 'week':
                $this->filterDateFrom = now()->startOfWeek()->format('Y-m-d');
                $this->filterDateTo = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->filterDateFrom = now()->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->filterDateFrom = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'all':
                $this->filterDateFrom = '';
                $this->filterDateTo = '';
                break;
        }
        
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterClient = '';
        $this->filterType = '';
        $this->filterStatus = '';
        $this->filterPriority = '';
        $this->filterDateRange = 'month';
        $this->filterDateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->filterDateTo = now()->endOfMonth()->format('Y-m-d');
        $this->filterTimeOfDay = '';
        $this->resetPage();
    }

    public function getCommunicationsProperty()
    {
        $query = ClientCommunication::query()
            ->with(['client', 'user', 'project']);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Date Range Filter
        if ($this->filterDateFrom && $this->filterDateTo) {
            $query->whereBetween('communication_date', [
                $this->filterDateFrom,
                $this->filterDateTo
            ]);
        } elseif ($this->filterDateFrom) {
            $query->where('communication_date', '>=', $this->filterDateFrom);
        } elseif ($this->filterDateTo) {
            $query->where('communication_date', '<=', $this->filterDateTo);
        }

        // Time of Day Filter
        if ($this->filterTimeOfDay) {
            $query->whereNotNull('communication_time');
            switch ($this->filterTimeOfDay) {
                case 'morning': // 6:00 - 11:59
                    $query->whereTime('communication_time', '>=', '06:00:00')
                          ->whereTime('communication_time', '<', '12:00:00');
                    break;
                case 'afternoon': // 12:00 - 17:59
                    $query->whereTime('communication_time', '>=', '12:00:00')
                          ->whereTime('communication_time', '<', '18:00:00');
                    break;
                case 'evening': // 18:00 - 23:59
                    $query->whereTime('communication_time', '>=', '18:00:00')
                          ->whereTime('communication_time', '<=', '23:59:59');
                    break;
            }
        }

        // Other Filters
        if ($this->filterClient) {
            $query->where('client_id', $this->filterClient);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(12);
    }

    public function getClientsProperty()
    {
        return Client::orderBy('name')->get();
    }

    public function getStatsProperty()
    {
        $baseQuery = ClientCommunication::query();

        // Apply date filter to stats if set
        if ($this->filterDateFrom && $this->filterDateTo) {
            $baseQuery->whereBetween('communication_date', [
                $this->filterDateFrom,
                $this->filterDateTo
            ]);
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'scheduled' => (clone $baseQuery)->where('status', 'scheduled')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'upcoming' => ClientCommunication::where('status', 'scheduled')
                ->where('communication_date', '>=', now()->format('Y-m-d'))
                ->count(),
        ];
    }

    public function getDateRangeLabelProperty()
    {
        if (!$this->filterDateFrom || !$this->filterDateTo) {
            return 'All Time';
        }

        $from = Carbon::parse($this->filterDateFrom);
        $to = Carbon::parse($this->filterDateTo);

        if ($from->isSameDay($to)) {
            return $from->format('d M Y');
        }

        if ($from->isSameMonth($to)) {
            return $from->format('d') . ' - ' . $to->format('d M Y');
        }

        return $from->format('d M Y') . ' - ' . $to->format('d M Y');
    }
}