<?php

namespace App\Livewire\Widget;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\RequiredDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProjectPropertiesChart extends ApexChartWidget
{
    protected static ?string $chartId = 'ProjectPropertiesChart';
    protected static ?string $heading = 'Document Status Overview';
    protected static ?string $subheading = 'Distribution of documents by current status';

    protected function getOptions(): array
    {
        // Build the query with proper filtering by user's clients
        $query = RequiredDocument::query();

        // Use proper relationship syntax without colon
        $query->whereHas('projectStep.project', function ($query) {
            if (!Auth::user()->hasRole('super-admin')) {
                $query->whereIn('client_id', function ($subQuery) {
                    $subQuery->select('client_id')
                        ->from('user_clients')
                        ->where('user_id', Auth::id());
                });
            }
        });

        // Define status mapping with consistent display names
        $statusMapping = [
            'approved' => 'Approved',
            'uploaded' => 'Uploaded',
            'pending_review' => 'Pending Review',
            'rejected' => 'Rejected',
            'draft' => 'Draft'
        ];

        // Continue with the rest of the query
        $documentStats = $query->whereIn('status', array_keys($statusMapping))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) use ($statusMapping) {
                // Map DB status to consistent display name
                $displayStatus = $statusMapping[$item->status] ?? ucwords(str_replace('_', ' ', $item->status));
                return [$displayStatus => $item->count];
            });

        // Make sure all statuses have a value, even if zero
        foreach ($statusMapping as $displayStatus) {
            if (!$documentStats->has($displayStatus)) {
                $documentStats[$displayStatus] = 0;
            }
        }

        // Create arrays for series and labels with consistent order to match colors
        $orderedLabels = [];
        $orderedSeries = [];

        // Ensure consistent order matching our color array
        foreach (array_values($statusMapping) as $status) {
            $orderedLabels[] = $status;
            $orderedSeries[] = $documentStats[$status] ?? 0;
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 400,
            ],
            'series' => $orderedSeries,
            'labels' => $orderedLabels,
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
                                'fontSize' => '24px',
                                'fontWeight' => '600',
                                'formatter' => 'function (val) { return val }'
                            ],
                            'total' => [
                                'show' => true,
                                'showAlways' => true,
                                'fontSize' => '16px',
                                'fontWeight' => 'bold',
                                'label' => 'Documents',
                            ]
                        ]
                    ]
                ]
            ],
            'legend' => [
                'position' => 'bottom',
                'horizontalAlign' => 'center',
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