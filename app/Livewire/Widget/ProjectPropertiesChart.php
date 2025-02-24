<?php

namespace App\Livewire\Widget;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\RequiredDocument;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectPropertiesChart extends ApexChartWidget
{
    protected static ?string $chartId = 'ProjectPropertiesChart';
    protected static ?string $heading = 'Document Status Distribution';

    protected function getHeight(): ?string
    {
        return '400px';
    }

    public ?string $filter = 'week';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getOptions(): array
    {
        $activeFilter = $this->filter;

        // Start with a base query
        $query = RequiredDocument::select('status', DB::raw('count(*) as count'))
            ->whereIn('status', ['draft', 'uploaded', 'pending_review', 'approved', 'rejected']);

        // Apply time filters
        switch ($activeFilter) {
            case 'today':
                $query->whereDate('updated_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('updated_at', [Carbon::now()->subWeek(), Carbon::now()]);
                break;
            case 'month':
                $query->whereBetween('updated_at', [Carbon::now()->subMonth(), Carbon::now()]);
                break;
            case 'year':
                $query->whereYear('updated_at', Carbon::now()->year);
                break;
            default:
                // No filter, show all data
                break;
        }

        // Complete the query and format the data
        $documentStats = $query->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                // Format status to capitalized words
                $formattedStatus = ucwords(str_replace('_', ' ', $item->status));
                return [$formattedStatus => $item->count];
            });

        // Make sure all statuses have a value, even if zero
        $statusMapping = [
            'draft' => 'Draft',
            'uploaded' => 'Uploaded',
            'pending_review' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];

        foreach ($statusMapping as $dbStatus => $displayStatus) {
            if (!$documentStats->has($displayStatus)) {
                $documentStats[$displayStatus] = 0;
            }
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $documentStats->values()->toArray(),
            'labels' => $documentStats->keys()->toArray(),
            'colors' => [
                '#22c55e', // Approved - green-500
                '#3b82f6', // Uploaded - blue-500
                '#f59e0b', // Pending Review - amber-500
                '#ef4444', // Rejected - red-500
                '#64748b', // Draft - slate-500
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '60%',
                        'expandOnClick' => true,
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                                'fontSize' => '16px',
                                'fontWeight' => 'bold',
                            ],
                            'value' => [
                                'show' => true,
                                'fontSize' => '20px',
                                'fontWeight' => '600',
                                'formatter' => 'function (val) { return val }'
                            ],
                            'total' => [
                                'show' => true,
                                'showAlways' => true,
                                'fontSize' => '14px',
                                'fontWeight' => 'bold',
                                'label' => 'Documents',
                            ]
                        ]
                    ]
                ]
            ],
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) { return val + " Documents" }'
                ]
            ],
        ];
    }
}