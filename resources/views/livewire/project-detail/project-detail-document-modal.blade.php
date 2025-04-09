<div class="flex flex-col lg:flex-row h-full bg-gray-50 dark:bg-gray-900">
    <style>
        @media (max-width: 1024px) {
            .comment-truncate {
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
            }

            .history-truncate {
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }
        }

        .status-badge {
            @apply px-2 py-1 text-xs font-medium rounded-full;
        }

        .status-badge.uploaded {
            @apply bg-blue-100 text-blue-700 dark: bg-blue-700 dark:text-blue-300;
        }

        .status-badge.pending_review {
            @apply bg-amber-100 text-amber-700 dark: bg-amber-900/50 dark:text-amber-300;
        }

        .status-badge.approved {
            @apply bg-green-100 text-green-700 dark: bg-green-900/50 dark:text-green-300;
        }

        .status-badge.rejected {
            @apply bg-red-100 text-red-700 dark: bg-red-900/50 dark:text-red-300;
        }
    </style>

    <!-- Left Section: Document Details & Upload -->
    <div
        class="order-1 lg:order-1 flex-1 flex flex-col min-w-0 bg-white dark:bg-gray-900 border-t lg:border-t-0 dark:border-gray-700">
        <!-- Document Header Section -->
        <div
            class="sticky top-0 z-10 flex items-center justify-between p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 min-h-[76px]">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-10 sm:w-12 h-10 sm:h-12 rounded-xl bg-primary-50/50 dark:bg-primary-900/20 ring-1 ring-primary-100 dark:ring-primary-800">
                    @if ($document->submittedDocuments->count() > 0)
                    <x-heroicon-o-document-text class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                    @else
                    <x-heroicon-o-document-plus class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="text-base sm:text-xl font-semibold text-gray-900 dark:text-white leading-tight truncate">
                        {{ $document->name }}
                    </h3>
                </div>
            </div>

            {{-- Close Button --}}
            <button x-on:click="$dispatch('close-modal', { id: 'document-modal-{{ $document->id }}' })" type="button"
                class="flex-shrink-0 rounded-lg p-2 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors duration-200">
                <span class="sr-only">Close</span>
                <x-heroicon-m-x-mark class="w-5 h-5" />
            </button>
        </div>


        <div class="flex-1 p-4 sm:p-6 space-y-4 sm:space-y-6 overflow-y-auto">
            <!-- Enhanced Status Section -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
                <div class="p-4">
                    <!-- Status Header -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                                <x-heroicon-m-signal class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Document Status</span>
                        </div>
                    </div>

                    <!-- Status Content Area -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <!-- Status Badge -->
                        <div class="flex-shrink-0">
                            <div class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium 
                                {{ match ($document->status) {
                                    'uploaded' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
                                    'pending_review' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
                                    'approved' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                                    'rejected' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                                    default => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                } }}">
                                <x-dynamic-component :component="$this->getStatusIcon($document->status)"
                                    class="w-4 h-4 mr-2" />
                                <span>{{ ucwords(str_replace('_', ' ', $document->status ?? 'Not Set')) }}</span>
                            </div>
                        </div>

                        <!-- Approve All Button -->
                        @if (!auth()->user()->hasRole(['staff', 'client']))
                        <x-filament::button x-data="{}"
                            x-on:click="$dispatch('open-modal', { id: 'confirm-approve-all' })" color="success"
                            size="sm" :disabled="$document->submittedDocuments->count() ===
                                    $document->submittedDocuments->where('status', 'approved')->count()">
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-check-badge class="w-4 h-4" />
                                <span>Approve All</span>
                            </div>
                        </x-filament::button>
                        @endif
                    </div>
                </div>



                @if ($document->reviewer_id && in_array($document->status, ['pending_review', 'approved', 'rejected']))
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                            <x-heroicon-m-user class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Reviewer</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $document->reviewer->name
                                }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Upload Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700">
                <div class="px-4 sm:px-6 py-4 sm:py-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                            <x-heroicon-m-arrow-up-tray class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Upload New Document</h4>
                    </div>

                    <form wire:submit="uploadDocument" class="space-y-4">
                        {{ $this->uploadFileForm }}

                        <div class="flex">
                            <x-filament::button type="submit" size="sm"
                                class="w-full justify-center bg-warning-500 hover:bg-warning-600 text-white focus:ring-warning-500/50">
                                <div class="inline-flex items-center gap-2">
                                    <x-heroicon-m-arrow-up-tray class="w-4 h-4" />
                                    <span class="font-medium">Upload Document</span>
                                </div>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Document History -->
            @if ($document->submittedDocuments->count() > 0)
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-4">
                    <!-- Section Header with Download All Button -->
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-9 h-9 rounded-lg bg-primary-50 dark:bg-primary-900/20 ring-1 ring-primary-100 dark:ring-primary-800">
                                <x-heroicon-m-clock class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Document History</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">All uploaded documents
                                </p>
                            </div>
                        </div>

                        <!-- Enhanced Download All Button -->
                        <button wire:click="downloadAllDocuments"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-gray-700 dark:text-gray-200 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600 shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                            <x-heroicon-m-arrow-down-tray class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" />
                            <span class="text-xs font-medium">Download All</span>
                            <span
                                class="flex items-center justify-center w-5 h-5 rounded-full bg-primary-50 dark:bg-primary-900/30 text-xs font-medium text-primary-700 dark:text-primary-300">
                                {{ $document->submittedDocuments->count() }}
                            </span>
                        </button>
                    </div>

                    <!-- Status Groups Container -->
                    <div class="space-y-5">
                        @php
                        $groupedDocuments = $sortedDocuments->groupBy('status');
                        $statusOrder = ['pending_review', 'uploaded', 'approved', 'rejected'];
                        $statusIcons = [
                        'approved' => 'heroicon-o-check-badge',
                        'pending_review' => 'heroicon-o-clock',
                        'rejected' => 'heroicon-o-x-circle',
                        'uploaded' => 'heroicon-o-arrow-up-tray',
                        ];

                        // Enhanced colors for better light theme visibility
                        $statusColors = [
                        'approved' => [
                        'bg' => 'bg-green-50 dark:bg-green-900/20',
                        'text' => 'text-green-700 dark:text-green-400',
                        'light_bg' => 'bg-green-50/70 dark:bg-green-900/10',
                        'border' => 'border-green-200 dark:border-green-800/30',
                        'pill_bg' => 'bg-green-100 dark:bg-green-800/30',
                        'pill_text' => 'text-green-800 dark:text-green-300',
                        ],
                        'pending_review' => [
                        'bg' => 'bg-amber-50 dark:bg-amber-900/20',
                        'text' => 'text-amber-700 dark:text-amber-400',
                        'light_bg' => 'bg-amber-50/70 dark:bg-amber-900/10',
                        'border' => 'border-amber-200 dark:border-amber-800/30',
                        'pill_bg' => 'bg-amber-100 dark:bg-amber-800/30',
                        'pill_text' => 'text-amber-800 dark:text-amber-300',
                        ],
                        'rejected' => [
                        'bg' => 'bg-red-50 dark:bg-red-900/20',
                        'text' => 'text-red-700 dark:text-red-400',
                        'light_bg' => 'bg-red-50/70 dark:bg-red-900/10',
                        'border' => 'border-red-200 dark:border-red-800/30',
                        'pill_bg' => 'bg-red-100 dark:bg-red-800/30',
                        'pill_text' => 'text-red-800 dark:text-red-300',
                        ],
                        'uploaded' => [
                        'bg' => 'bg-blue-50 dark:bg-blue-900/20',
                        'text' => 'text-blue-700 dark:text-blue-400',
                        'light_bg' => 'bg-blue-50/70 dark:bg-blue-900/10',
                        'border' => 'border-blue-200 dark:border-blue-800/30',
                        'pill_bg' => 'bg-blue-100 dark:bg-blue-800/30',
                        'pill_text' => 'text-blue-800 dark:text-blue-300',
                        ],
                        ];
                        @endphp

                        @foreach ($statusOrder as $status)
                        @if (isset($groupedDocuments[$status]) && $groupedDocuments[$status]->count() > 0)
                        <!-- Enhanced Status Group Section -->
                        <div class="relative">
                            <div class="flex items-center gap-2 mb-3">
                                <div
                                    class="flex items-center gap-2 px-3 py-1.5 rounded-full {{ $statusColors[$status]['bg'] }} border {{ $statusColors[$status]['border'] }}">
                                    <x-dynamic-component :component="$statusIcons[$status]"
                                        class="w-4 h-4 {{ $statusColors[$status]['text'] }}" />
                                    <span class="text-xs font-medium {{ $statusColors[$status]['text'] }}">{{
                                        $this->getStatusLabel($status) }}</span>
                                    <span
                                        class="flex items-center justify-center w-5 h-5 rounded-full {{ $statusColors[$status]['pill_bg'] }} text-xs font-medium {{ $statusColors[$status]['pill_text'] }}">
                                        {{ $groupedDocuments[$status]->count() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Status Group Documents with Enhanced UI -->
                            <div class="space-y-2.5">
                                @foreach ($groupedDocuments[$status] as $submission)
                                <div
                                    class="group relative flex items-center gap-3 p-3 rounded-lg border {{ $statusColors[$status]['border'] }} {{ $statusColors[$status]['light_bg'] }} hover:bg-white dark:hover:bg-gray-700/50 transition-all shadow-sm">
                                    <!-- Status Icon -->
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-lg {{ $statusColors[$status]['bg'] }} border {{ $statusColors[$status]['border'] }} flex items-center justify-center">
                                            <x-dynamic-component :component="$statusIcons[$status]"
                                                class="w-5 h-5 {{ $statusColors[$status]['text'] }}" />
                                        </div>
                                    </div>

                                    <!-- Document Info with Notes Indicator -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4
                                                class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[200px] sm:max-w-[300px]">
                                                {{ basename($submission->file_path) }}
                                            </h4>
                                            @if ($submission->notes)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                <x-heroicon-m-document-text class="w-3 h-3" />
                                                Notes
                                            </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ $submission->user->name }}
                                            </span>
                                            <span class="text-xs text-gray-300 dark:text-gray-600">•</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                                {{ $submission->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2">
                                        <!-- Download Button -->
                                        <button wire:click="downloadDocument({{ $submission->id }})"
                                            class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:focus:ring-primary-400/30 transition-all opacity-0 group-hover:opacity-100">
                                            <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                        </button>

                                        <!-- Preview Button - Enhanced Visibility -->
                                        <button wire:click="viewDocument({{ $submission->id }})"
                                            x-on:click="$dispatch('open-modal', { id: 'preview-document' })"
                                            class="inline-flex items-center justify-center p-1.5 rounded-lg text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:focus:ring-primary-400/30 transition-all">
                                            <x-heroicon-m-eye class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Section: Comments -->
    <div
        class="order-2 lg:order-2 lg:w-[400px] border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-gray-700 flex flex-col bg-white dark:bg-gray-900">
        <!-- Comments Header -->
        <div
            class="sticky top-0 z-10 flex items-center p-4 sm:p-6 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-900 min-h-[76px]">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-10 sm:w-12 h-10 sm:h-12 rounded-xl bg-primary-50/50 dark:bg-primary-900/20 ring-1 ring-primary-100 dark:ring-primary-800">
                    <x-heroicon-m-chat-bubble-left-right
                        class="w-5 sm:w-6 h-5 sm:h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base sm:text-xl font-semibold text-gray-900 dark:text-white">Comments</h3>
                    <span
                        class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-medium bg-primary-50 dark:bg-primary-900 text-primary-600 dark:text-primary-400 rounded-full">
                        {{ $document->comments()->count() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Comments List -->
        <!-- Comments List with Indonesian Day Names and Date Separators -->
        <div class="flex-1 p-4 sm:p-6 overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">
            <div class="space-y-4">
                @php
                // Group comments by date
                $groupedComments = $comments->groupBy(function ($comment) {
                $date = $comment->created_at->startOfDay();
                if ($date->isToday()) {
                return 'today';
                } elseif ($date->isYesterday()) {
                return 'yesterday';
                } elseif ($date->diffInDays(now()->startOfDay()) <= 7) { $dayNames=[ 0=> 'Minggu',
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    ];
                    return $dayNames[$date->dayOfWeek];
                    } else {
                    return $date->format('j F');
                    }
                    });

                    $sortedKeys = $groupedComments->keys()->sort(function ($a, $b) {

                    $priority = [
                    'today' => 100, // Today appears first
                    'yesterday' => 99,
                    'Senin' => 98,
                    'Selasa' => 97,
                    'Rabu' => 96,
                    'Kamis' => 95,
                    'Jumat' => 94,
                    'Sabtu' => 93,
                    'Minggu' => 92,
                    ];

                    // If both are in our priority array, compare their values
                    if (isset($priority[$a]) && isset($priority[$b])) {
                    return $priority[$b] <=> $priority[$a];
                        }

                        // If only one is in our priority array, that one is "newer" so should be earlier
                        if (isset($priority[$a])) {
                        return -1;
                        }
                        if (isset($priority[$b])) {
                        return 1;
                        }

                        // For dates in the format "j F" (like "15 February"), compare chronologically
                        try {
                        // Parse both dates with the current year
                        $currentYear = now()->year;
                        $dateA = \Carbon\Carbon::createFromFormat('j F Y', $a . ' ' . $currentYear);
                        $dateB = \Carbon\Carbon::createFromFormat('j F Y', $b . ' ' . $currentYear);


                        if ($dateA > now() && $dateA->month < 6) { $dateA->subYear();
                            }
                            if ($dateB > now() && $dateB->month < 6) { $dateB->subYear();
                                }

                                // Return the comparison with most recent dates first (reversed)
                                return $dateB <=> $dateA;
                                    } catch (\Exception $e) {
                                    // If parsing fails, fall back to string comparison
                                    return $b <=> $a;
                                        }
                                        });
                                        // Create a new collection with the sorted keys
                                        $sortedGroups = collect();
                                        foreach ($sortedKeys as $key) {
                                        $sortedGroups[$key] = $groupedComments[$key];
                                        }
                                        $groupedComments = $sortedGroups;
                                        @endphp
                                        @forelse($groupedComments as $dateGroup => $commentsInGroup)
                                        <!-- Date Separator -->
                                        <div class="flex justify-center relative my-6">
                                            <div class="absolute inset-0 flex items-center">
                                                <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                                            </div>
                                            <div
                                                class="relative px-4 bg-gray-50/80 dark:bg-gray-900/80 rounded-full z-10">
                                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    @if ($dateGroup === 'today')
                                                    Hari Ini
                                                    @elseif($dateGroup === 'yesterday')
                                                    Kemarin
                                                    @else
                                                    {{ $dateGroup }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <!-- Comments for this date group -->
                                        @foreach ($commentsInGroup as $comment)
                                        <div class="flex gap-4 group">
                                            <div class="flex-shrink-0">
                                                <div @class([ 'w-8 h-8 rounded-lg flex items-center justify-center'
                                                    , 'bg-primary-50 dark:bg-primary-900 ring-1 ring-primary-100 dark:ring-primary-800'=>
                                                    $comment->user_id === auth()->id(),
                                                    'bg-gray-100 dark:bg-gray-800 ring-1 ring-gray-200
                                                    dark:ring-gray-700' =>
                                                    $comment->user_id !== auth()->id(),
                                                    ])>
                                                    <span @class([ 'text-xs font-medium'
                                                        , 'text-primary-600 dark:text-primary-400'=>
                                                        $comment->user_id === auth()->id(),
                                                        'text-gray-600 dark:text-gray-400' => $comment->user_id !==
                                                        auth()->id(),
                                                        ])>
                                                        {{ substr($comment->user->name ?? 'U', 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div @class([ 'bg-white dark:bg-gray-800 rounded-lg px-4 py-3 shadow-sm ring-1 transition-all'
                                                    , 'ring-primary-100 dark:ring-primary-800 hover:ring-primary-200 dark:hover:ring-primary-700'=>
                                                    $comment->user_id === auth()->id(),
                                                    'ring-gray-100 dark:ring-gray-700 hover:ring-gray-200
                                                    dark:hover:ring-gray-600' =>
                                                    $comment->user_id !== auth()->id(),
                                                    ])>
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-sm font-medium text-gray-900 dark:text-white">
                                                                {{ $comment->user->name ?? 'Unknown User' }}
                                                            </span>
                                                            @if ($comment->user_id === auth()->id())
                                                            <span
                                                                class="text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/50 px-1.5 py-0.5 rounded">
                                                                You
                                                            </span>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                                {{ $comment->created_at->format('H:i') }}
                                                            </span>
                                                            @if ($comment->user_id === auth()->id())
                                                            <div class="relative" x-data="{ isOpen: false }">
                                                                <!-- Menu Button -->
                                                                <button @click="isOpen = !isOpen"
                                                                    @click.away="isOpen = false"
                                                                    class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400">
                                                                    <x-heroicon-m-ellipsis-vertical class="w-4 h-4" />
                                                                </button>
                                                                <!-- Dropdown Menu -->
                                                                <div x-show="isOpen"
                                                                    x-transition:enter="transition ease-out duration-100"
                                                                    x-transition:enter-start="transform opacity-0 scale-95"
                                                                    x-transition:enter-end="transform opacity-100 scale-100"
                                                                    x-transition:leave="transition ease-in duration-75"
                                                                    x-transition:leave-start="transform opacity-100 scale-100"
                                                                    x-transition:leave-end="transform opacity-0 scale-95"
                                                                    class="absolute right-0 z-10 mt-1 w-36 origin-top-right rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-200 dark:ring-gray-700 focus:outline-none">
                                                                    <div class="py-1">
                                                                        <!-- Edit Button -->
                                                                        <button
                                                                            wire:click="editComment({{ $comment->id }})"
                                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white">
                                                                            <x-heroicon-m-pencil-square
                                                                                class="w-4 h-4" />
                                                                            Edit
                                                                        </button>
                                                                        <!-- Delete Button -->
                                                                        <button
                                                                            wire:click="deleteComment({{ $comment->id }})"
                                                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30">
                                                                            <x-heroicon-m-trash class="w-4 h-4" />
                                                                            Delete
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="prose prose-sm dark:prose-invert max-w-none mt-1 text-gray-600 dark:text-gray-300">
                                                        {!! $comment->content !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        @empty
                                        <div class="text-center py-12">
                                            <div
                                                class="w-12 h-12 mx-auto mb-4 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                                <x-heroicon-o-chat-bubble-left-right
                                                    class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                                            </div>
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">Belum ada
                                                komentar</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Jadilah yang pertama
                                                berkomentar pada
                                                dokumen ini</p>
                                        </div>
                                        @endforelse
            </div>
        </div>

        <!-- Comment Input -->
        <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700"
            x-data="{ showCommentForm: false }">
            <!-- Comment Toggle Button -->
            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                <button @click="showCommentForm = !showCommentForm"
                    class="w-full flex items-center justify-center gap-2 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <div class="flex items-center gap-2" x-show="!showCommentForm">
                        <x-heroicon-m-chat-bubble-left-right class="w-4 h-4" />
                        <span>Add Comment</span>
                    </div>
                    <div class="flex items-center gap-2" x-show="showCommentForm">
                        <x-heroicon-m-x-mark class="w-4 h-4" />
                        <span>Close</span>
                    </div>
                </button>
            </div>

            <!-- Comment Form -->
            <div x-show="showCommentForm" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-2" class="p-4">
                <form wire:submit="addComment">
                    <!-- RichEditor Container with custom styling -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg ring-1 ring-gray-200 dark:ring-gray-700">
                        <div class="relative">
                            {{ $this->createCommentForm }}
                        </div>

                        <!-- Button Container -->
                        <div
                            class="px-3 py-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 rounded-b-lg">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Submit button -->
                                <x-filament::button type="submit" size="sm" icon="heroicon-m-paper-airplane"
                                    class="inline-flex items-center bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 dark:from-amber-500 dark:to-amber-400 dark:hover:from-amber-600 dark:hover:to-amber-500">
                                    <span class="mr-2">Send</span>
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Document Preview Modal with Enhanced UI -->
    <x-filament::modal id="preview-document" wire:model="isPreviewModalOpen" width="7xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
            <!-- Document Info -->
            <div class="flex items-start gap-3 min-w-0">
                <div
                    class="hidden sm:flex flex-shrink-0 items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-primary-50 dark:bg-primary-900/50 ring-1 ring-primary-100 dark:ring-primary-800">
                    @if ($fileType === 'pdf')
                    <x-heroicon-o-document-text class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-primary-400" />
                    @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                    <x-heroicon-o-photo class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-primary-400" />
                    @else
                    <x-heroicon-o-document class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-primary-400" />
                    @endif
                </div>
                @if ($previewingDocument)
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3
                            class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white leading-tight truncate">
                            {{ basename($previewingDocument->file_path) }}
                        </h3>
                        @php $position = $this->getDocumentPosition(); @endphp
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            {{ $position['current'] }} of {{ $position['total'] }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Uploaded {{ $previewingDocument->created_at->diffForHumans() }} by
                        {{ $previewingDocument->user->name }}
                    </p>
                </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2 mt-2 sm:mt-0">
                @if (
                $previewingDocument &&
                !auth()->user()->hasRole(['staff', 'client']))
                <!-- Status Dropdown -->
                <x-filament::dropdown placement="bottom-end">
                    <x-slot name="trigger">
                        <x-filament::button size="sm" :color="match ($previewingDocument->status) {
                                'uploaded' => 'info',
                                'pending_review' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }">
                            <div class="flex items-center gap-2">
                                <x-dynamic-component :component="$this->getStatusIcon($previewingDocument->status)"
                                    class="w-4 h-4" />
                                <span class="hidden sm:inline">{{ $this->getStatusLabel($previewingDocument->status)
                                    }}</span>
                                <span class="sm:hidden">Status</span>
                                <x-heroicon-m-chevron-down class="w-4 h-4" />
                            </div>
                        </x-filament::button>
                    </x-slot>

                    <x-filament::dropdown.list>
                        <x-filament::dropdown.list.item
                            wire:click="updateDocumentStatus({{ $previewingDocument->id }}, 'pending_review')"
                            icon="heroicon-m-clock"
                            :color="$previewingDocument->status === 'pending_review' ? 'warning' : 'gray'">
                            Pending Review
                        </x-filament::dropdown.list.item>

                        <x-filament::dropdown.list.item
                            wire:click="updateDocumentStatus({{ $previewingDocument->id }}, 'approved')"
                            icon="heroicon-m-check-circle"
                            :color="$previewingDocument->status === 'approved' ? 'success' : 'gray'">
                            Approved
                        </x-filament::dropdown.list.item>

                        <x-filament::dropdown.list.item
                            x-on:click="$dispatch('open-modal', { id: 'rejection-reason-modal' }); $wire.openRejectionModal({{ $previewingDocument->id }})"
                            icon="heroicon-m-x-circle"
                            :color="$previewingDocument->status === 'rejected' ? 'danger' : 'gray'">
                            Rejected
                        </x-filament::dropdown.list.item>
                    </x-filament::dropdown.list>
                </x-filament::dropdown>
                @endif

                @if ($previewingDocument)
                <!-- Toggle sidebar button - hide on very small screens -->
                <div x-data="{ showSidebar: false }" class="hidden sm:block relative">
                    <button @click="showSidebar = !showSidebar; $dispatch('toggle-sidebar')" type="button" class="group relative inline-flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200
                            border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500
                            text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400
                            bg-white dark:bg-gray-800 hover:bg-primary-50 dark:hover:bg-primary-900/20
                            shadow-sm hover:shadow-md"
                        :class="{ 'bg-primary-50 dark:bg-primary-900/20 border-primary-500 dark:border-primary-500': showSidebar }">
                        <div class="flex items-center gap-1.5">
                            <template x-if="!showSidebar">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-bars-3 class="w-4 h-4" />
                                    <span class="text-sm font-medium">Details</span>
                                </div>
                            </template>
                            <template x-if="showSidebar">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                    <span class="text-sm font-medium">Close</span>
                                </div>
                            </template>
                        </div>
                    </button>
                </div>
                @endif

                <!-- Close button -->
                <button x-on:click="$dispatch('close-modal', { id: 'preview-document' })" type="button"
                    class="flex-shrink-0 rounded-lg p-2 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors duration-200">
                    <span class="sr-only">Close</span>
                    <x-heroicon-m-x-mark class="w-5 h-5" />
                </button>
            </div>
        </div>


        <!-- Mobile sidebar toggle -->
        @if ($previewingDocument)
        <div class="sm:hidden flex items-center justify-between mt-4 mb-2">
            <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" color="gray" size="sm"
                class="flex-1 justify-center mr-2">
                <x-heroicon-m-arrow-down-tray class="w-4 h-4 mr-1" />
                Download
            </x-filament::button>

            <x-filament::button x-data="{}"
                x-on:click="$dispatch('open-modal', { id: 'confirm-delete-modal-{{ $previewingDocument->id }}' })"
                color="danger" size="sm" class="flex-1 justify-center">
                <x-heroicon-m-trash class="w-4 h-4 mr-1" />
                Remove
            </x-filament::button>
        </div>
        @endif

        <!-- Enhanced Layout with Toggleable Sidebar -->
        <div class="flex flex-col lg:flex-row h-[calc(100vh-14rem)] sm:h-[calc(100vh-10rem)]"
            x-data="{ showSidebar: false }" @toggle-sidebar.window="showSidebar = !showSidebar">
            <!-- Document Preview Section (Takes Full Width by Default) -->
            <div class="w-full transition-all duration-300 ease-in-out h-full p-2 sm:p-4 relative"
                :class="{ 'lg:w-2/3': showSidebar }">
                <!-- Replace the Previous/Next Document navigation section -->
                @if ($this->document->submittedDocuments->count() > 1)
                <div
                    class="absolute left-0 right-0 top-1/2 -translate-y-1/2 flex items-center justify-between px-4 pointer-events-none">
                    <!-- Previous Document Button - Always visible -->
                    <button wire:click="previousDocument" class="p-2 rounded-full bg-white/80 dark:bg-gray-800/80 shadow-lg hover:bg-white dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all group pointer-events-auto
                        hover:scale-110 active:scale-95 transform duration-200"
                        title="Previous document (Press Left Arrow)">
                        <x-heroicon-o-chevron-left
                            class="w-6 h-6 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-400" />
                        <span class="sr-only">Previous document</span>
                    </button>

                    <!-- Next Document Button - Always visible -->
                    <button wire:click="nextDocument" class="p-2 rounded-full bg-white/80 dark:bg-gray-800/80 shadow-lg hover:bg-white dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 transition-all group pointer-events-auto
                            hover:scale-110 active:scale-95 transform duration-200"
                        title="Next document (Press Right Arrow)">
                        <x-heroicon-o-chevron-right
                            class="w-6 h-6 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-400" />
                        <span class="sr-only">Next document</span>
                    </button>
                </div>

                <!-- Optional: Add keyboard navigation support -->
                <script>
                    document.addEventListener('keydown', function(e) {
                            if (e.key === 'ArrowLeft') {
                                @this.previousDocument();
                            } else if (e.key === 'ArrowRight') {
                                @this.nextDocument();
                            }
                        });
                </script>
                @endif

                <!-- Document Preview Container -->
                <div
                    class="h-full rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700">
                    @if ($previewUrl)
                    @if ($fileType === 'pdf')
                    <div class="w-full h-full bg-gray-50 dark:bg-gray-900">
                        <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg">
                            <div class="flex flex-col items-center justify-center p-8">
                                <div
                                    class="w-16 h-16 rounded-full bg-primary-50 dark:bg-primary-900 flex items-center justify-center mb-4">
                                    <x-heroicon-o-document-text
                                        class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 text-center">
                                    Unable to display PDF
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-center">
                                    The PDF viewer is not supported on this device
                                </p>
                            </div>
                        </iframe>
                    </div>
                    @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                    <div class="h-full flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                        <img src="{{ $previewUrl }}" alt="Document Preview"
                            class="max-w-full max-h-full object-contain rounded-lg shadow-sm">
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center h-full py-16">
                        <div
                            class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <x-heroicon-o-document class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Preview not available
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            This file type cannot be previewed directly in the browser
                        </p>
                        @if ($previewingDocument)
                        <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" size="sm"
                            class="inline-flex items-center">
                            <div class="inline-flex items-center gap-2">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4 -ml-1 mr-2" />
                                <span>Download to View</span>
                            </div>
                        </x-filament::button>
                        @endif
                    </div>
                    @endif
                    @else
                    <div class="flex flex-col items-center justify-center h-full py-16">
                        <div
                            class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center animate-pulse">
                            <x-heroicon-o-document class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4">
                            Loading preview...
                        </h3>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right Sidebar (Hidden by Default) -->
            <div x-show="showSidebar" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-8"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-8"
                class="w-full lg:w-1/3 h-full p-4 flex flex-col bg-gray-50/50 dark:bg-gray-900/50 rounded-r-xl overflow-y-auto">
                @if ($previewingDocument)
                <div class="space-y-4 flex flex-col h-full">
                    <!-- Quick Action Buttons at the top (hidden on mobile since we added them at the top) -->
                    <div class="hidden sm:grid grid-cols-2 gap-3">
                        <!-- Download Button -->
                        <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" color="gray"
                            size="md" class="justify-center">
                            <div class="inline-flex items-center gap-2">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                                <span>Download</span>
                            </div>
                        </x-filament::button>

                        <!-- Remove Button with Confirmation Modal -->
                        <x-filament::button x-data="{}"
                            x-on:click="$dispatch('open-modal', { id: 'confirm-delete-modal' })" color="danger"
                            size="md" class="justify-center">
                            <div class="inline-flex items-center gap-2">
                                <x-heroicon-m-trash class="w-4 h-4" />
                                <span>Remove</span>
                            </div>
                        </x-filament::button>
                    </div>

                    <!-- Document Status Card with improved design -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-hidden">
                        <!-- Status Header -->
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Document Status</h3>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match ($previewingDocument->status) {
                                        'uploaded' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300',
                                        'pending_review' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300',
                                        'approved' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300',
                                        'rejected' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300',
                                        default => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                    } }}">
                                    <x-dynamic-component :component="$this->getStatusIcon($previewingDocument->status)"
                                        class="w-3.5 h-3.5 mr-1" />
                                    {{ $this->getStatusLabel($previewingDocument->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Document Info -->
                        <div class="px-4 py-3 space-y-2 bg-gray-50/80 dark:bg-gray-800/80">
                            <div class="flex items-center gap-3 text-sm">
                                <div
                                    class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex-shrink-0 flex items-center justify-center text-gray-500 dark:text-gray-400">
                                    {{ substr($previewingDocument->user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $previewingDocument->user->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Uploaded {{ $previewingDocument->created_at->format('M d, Y • g:i A') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- File Information -->
                        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm">
                                <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center
                                    {{ match ($fileType) {
                                        'pdf' => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
                                        'jpg', 'jpeg', 'png', 'gif' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400',
                                        'doc', 'docx' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
                                        'xls', 'xlsx', 'csv' => 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
                                        default => 'bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                    } }}">
                                    @if ($fileType === 'pdf')
                                    <x-heroicon-o-document-text class="w-4 h-4" />
                                    @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                                    <x-heroicon-o-photo class="w-4 h-4" />
                                    @elseif(in_array($fileType, ['doc', 'docx']))
                                    <x-heroicon-o-document class="w-4 h-4" />
                                    @elseif(in_array($fileType, ['xls', 'xlsx', 'csv']))
                                    <x-heroicon-o-table-cells class="w-4 h-4" />
                                    @else
                                    <x-heroicon-o-document class="w-4 h-4" />
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ basename($previewingDocument->file_path) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <span class="uppercase">{{ strtoupper($fileType) }}</span>
                                        <span class="text-gray-300 dark:text-gray-600">•</span>
                                        <span>Document #{{ $previewingDocument->id }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Rejection Reason (if applicable) -->
                        @if ($previewingDocument->status === 'rejected' && $previewingDocument->rejection_reason)
                        <div
                            class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-red-50/20 dark:bg-red-900/10">
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-exclamation-triangle
                                    class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" />
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">
                                        Rejection Reason:
                                    </p>
                                    <div
                                        class="text-xs text-red-600 dark:text-red-300 prose prose-sm max-h-[13rem] overflow-y-auto">
                                        {!! $previewingDocument->rejection_reason !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Improved Document Notes with Toggle -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-100 dark:ring-gray-700 overflow-y-auto flex-1"
                        x-data="{ showNotesForm: false }">
                        <!-- Notes Header with Toggle Button -->
                        <div
                            class="px-4 py-3 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white flex items-center gap-2">
                                    <x-heroicon-o-document-text
                                        class="w-4 h-4 text-primary-500 dark:text-primary-400" />
                                    Document Notes
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Private to staff</span>
                                    <button @click="showNotesForm = !showNotesForm" type="button"
                                        class="inline-flex items-center justify-center p-1 rounded-md text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors">
                                        <x-heroicon-o-pencil-square x-show="!showNotesForm" class="w-5 h-5" />
                                        <x-heroicon-o-x-mark x-show="showNotesForm" class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notes Content Area -->
                        <div class="p-4 flex-1 overflow-auto">
                            <!-- Notes Display (when not editing) -->
                            <div x-show="!showNotesForm" class="prose prose-sm dark:prose-invert max-w-none h-full">
                                @if ($previewingDocument && $previewingDocument->notes)
                                <div
                                    class="p-4 rounded-lg {{ $statusColors[$previewingDocument->status]['bg'] }} {{ $statusColors[$previewingDocument->status]['bg'] }} border">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            <x-heroicon-m-document-text
                                                class="w-5 h-5 {{ $statusColors[$previewingDocument->status]['text'] }}" />
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <p
                                                class="text-sm font-medium {{ $statusColors[$previewingDocument->status]['text'] }}">
                                                Document Notes</p>
                                            <div class="text-gray-700 dark:text-gray-300 max-h-[16rem] overflow-y-auto">
                                                {!! $previewingDocument->notes !!}
                                            </div>
                                            <div
                                                class="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                                <x-heroicon-m-clock class="w-4 h-4" />
                                                <span>Last updated
                                                    {{ $previewingDocument->updated_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="flex flex-col items-center justify-center py-6 text-center h-full">
                                    <div
                                        class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                                        <x-heroicon-o-document-text class="w-6 h-6 text-gray-400 dark:text-gray-500" />
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No notes added yet</p>
                                    <button @click="showNotesForm = true" type="button"
                                        class="mt-3 inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400 transition-colors">
                                        <x-heroicon-o-pencil class="w-3.5 h-3.5 mr-1" />
                                        <span>Add Notes</span>
                                    </button>
                                </div>
                                @endif
                            </div>
                            <!-- Notes Form (when editing) -->
                            <div x-show="showNotesForm" x-cloak>
                                <form wire:submit.prevent="saveDocumentNotes">
                                    <div class="space-y-4">
                                        {{ $this->documentNotesForm }}
                                        <div class="flex items-center justify-end gap-2">
                                            <x-filament::button type="button" color="gray" size="sm"
                                                @click="showNotesForm = false">
                                                Cancel
                                            </x-filament::button>
                                            <x-filament::button type="submit" size="sm" color="primary">
                                                Save Notes
                                            </x-filament::button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Confirmation Modal for Document Deletion -->
        <x-filament::modal id="confirm-delete-modal" width="md">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                        <x-heroicon-o-trash class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white leading-6">
                        Confirm Document Removal
                    </h2>
                </div>
            </x-slot>

            <div class="space-y-4">
                <p class="text-gray-700 dark:text-gray-300">
                    Are you sure you want to remove this document? This action cannot be undone.
                </p>

                @if ($previewingDocument)
                <div
                    class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 mt-3">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-9 h-9 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                            @php
                            $fileType = $previewingDocument
                            ? strtolower(pathinfo($previewingDocument->file_path, PATHINFO_EXTENSION))
                            : '';
                            @endphp

                            @if ($fileType === 'pdf')
                            <x-heroicon-o-document-text class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                            @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                            <x-heroicon-o-photo class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                            @else
                            <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ basename($previewingDocument->file_path) }}
                            </h4>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Uploaded by {{ $previewingDocument->user->name }}
                                </span>
                                <span class="text-xs text-gray-300 dark:text-gray-600">•</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $previewingDocument->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <x-slot name="footer">
                <div class="flex justify-end gap-3">
                    <x-filament::button
                        x-on:click="$dispatch('close-modal', { id: 'confirm-delete-modal-{{ $previewingDocument->id ?? 'null' }}' })"
                        color="gray">
                        Cancel
                    </x-filament::button>

                    <x-filament::button wire:click="removeDocument({{ $previewingDocument->id ?? 'null' }})"
                        wire:loading.attr="disabled" color="danger">
                        <div class="flex items-center gap-1">
                            <x-heroicon-m-trash class="w-4 h-4" />
                            <span>Delete Document</span>
                        </div>
                    </x-filament::button>
                </div>
            </x-slot>
        </x-filament::modal>

        <!-- Enhanced Document Rejection Modal -->
        <x-filament::modal id="rejection-reason-modal" width="md">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                        <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-white leading-6">
                            Document Rejection
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Please provide a detailed reason for rejecting this document
                        </p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-5">
                <!-- Document Info Card -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            @if ($documentBeingRejected)
                            <div
                                class="w-10 h-10 rounded-lg bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                                @php
                                $fileType = strtolower(
                                pathinfo($documentBeingRejected->file_path, PATHINFO_EXTENSION),
                                );
                                @endphp
                                @if ($fileType === 'pdf')
                                <x-heroicon-o-document-text class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                                <x-heroicon-o-photo class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                @elseif(in_array($fileType, ['doc', 'docx']))
                                <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                @elseif(in_array($fileType, ['xls', 'xlsx', 'csv']))
                                <x-heroicon-o-table-cells class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                @else
                                <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                @endif
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            @if ($documentBeingRejected)
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ basename($documentBeingRejected->file_path) }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Uploaded by {{ $documentBeingRejected->user->name }}
                                </span>
                                <span class="text-xs text-gray-300 dark:text-gray-600">•</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $documentBeingRejected->created_at->diffForHumans() }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Warning -->
                    <div
                        class="bg-red-50 dark:bg-red-900/10 rounded-lg p-4 border border-red-100 dark:border-red-800/30">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-exclamation-triangle
                                class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-300">
                                    This action requires explanation
                                </p>
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    The submitter will be notified of your decision with the reason you provide below.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Rejection Form -->
                    <form wire:submit.prevent="submitRejection" class="space-y-4">
                        <!-- Rejection Reason Field -->
                        <div>
                            <div class="mt-1">
                                {{ $this->rejectionForm }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Please be specific about what needs to be corrected or improved.
                            </p>
                        </div>

                        <!-- Button Group -->
                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <x-filament::button type="button" color="gray"
                                x-on:click="$dispatch('close-modal', { id: 'rejection-reason-modal' })">
                                Cancel
                            </x-filament::button>

                            <x-filament::button type="submit" color="danger" class="px-4">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-m-x-circle class="w-4 h-4" />
                                    <span>Reject Document</span>
                                </div>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
        </x-filament::modal>
    </x-filament::modal>

    <x-filament::modal id="confirm-approve-all" alignment="center" width="sm">
        <x-slot name="header">
            <div class="flex items-center gap-3">
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-full bg-green-50 dark:bg-green-900 flex items-center justify-center">
                    <x-heroicon-o-check-badge class="w-5 h-5 text-green-500 dark:text-green-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Approve All Documents
                </h3>
            </div>
        </x-slot>

        <div class="space-y-3">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                This action will approve all documents regardless of their current status. Are you sure you
                want to
                continue?
            </p>
            <div
                class="bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-700/50 rounded-lg p-3">
                <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                    Note: This action cannot be undone.
                </p>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <x-filament::button x-on:click="$dispatch('close-modal', { id: 'confirm-approve-all' })" color="gray"
                    size="sm">
                    Cancel
                </x-filament::button>

                <x-filament::button wire:click="approveAllDocuments" color="success" size="sm">
                    <div class="flex items-center gap-1">
                        <x-heroicon-m-check-badge class="w-4 h-4" />
                        <span>Approve All</span>
                    </div>
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>