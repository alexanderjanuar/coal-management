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
        
        // Continue with the rest of the query
        $documentStats = $query->whereIn('status', ['draft', 'uploaded', 'pending_review', 'approved', 'rejected'])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
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
                'height' => 400,
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