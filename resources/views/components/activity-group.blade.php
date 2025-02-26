<div class="border-b border-gray-100 dark:border-gray-700 last:border-b-0">
    <div
        class="py-3 px-3 md:px-6 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between sticky top-0 z-10 shadow-sm backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90">
        <div class="flex items-center">
            <div class="{{ $labelBgClass }} text-xs font-medium px-3 py-1.5 rounded-full shadow-sm">{{ $title }}</div>
            <div class="text-gray-500 dark:text-gray-400 text-sm ml-3 hidden sm:block">{{ $subtitle }}</div>
        </div>

        <!-- Mobile date indicator -->
        <div class="text-xs text-gray-500 dark:text-gray-400 sm:hidden flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ substr($subtitle, 0, 6) }}
        </div>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-gray-700">
        @forelse($activities as $activity)
        <div
            class="px-3 md:px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150 relative group">
            <!-- Timeline line -->
            <div class="absolute top-0 bottom-0 left-[28px] sm:left-[35px] w-0.5 bg-gray-200 dark:bg-gray-600"></div>

            <div class="flex items-start gap-3 md:gap-6">
                <!-- Icon -->
                <div class="flex-shrink-0 relative z-10">
                    @php
                    $iconColorClass = match($activity->event) {
                    'created' => 'bg-emerald-500 text-white dark:bg-emerald-600',
                    'updated' => 'bg-blue-500 text-white dark:bg-blue-600',
                    'deleted' => 'bg-red-500 text-white dark:bg-red-600',
                    default => 'bg-gray-500 text-white dark:bg-gray-600'
                    };
                    @endphp
                    <div
                        class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-full ring-[3px] sm:ring-4 ring-white dark:ring-gray-800 shadow-sm group-hover:shadow-md transition-all duration-200 ease-in-out transform group-hover:scale-110 {{ $iconColorClass }}">
                        @if($activity->event == 'created')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        @elseif($activity->event == 'updated')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        @elseif($activity->event == 'deleted')
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        @endif
                    </div>
                </div>

                <!-- Content -->
                <div
                    class="flex-1 bg-white dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm group-hover:shadow-md transition-all duration-200 px-3 py-3 sm:px-5 sm:py-4">
                    <!-- Entity Type Badge and Header -->
                    @php
                    $entityType = str_replace('App\\Models\\', '', $activity->subject_type ?? '');
                    $badgeClass = match($entityType) {
                    'Project' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 ring-1
                    ring-indigo-300/50 dark:ring-indigo-700/30',
                    'ProjectStep' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 ring-1
                    ring-amber-300/50 dark:ring-amber-700/30',
                    'Task' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300 ring-1 ring-teal-300/50
                    dark:ring-teal-700/30',
                    'RequiredDocument' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300
                    ring-1 ring-purple-300/50 dark:ring-purple-700/30',
                    'SubmittedDocument' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/50 dark:text-rose-300 ring-1
                    ring-rose-300/50 dark:ring-rose-700/30',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 ring-1 ring-gray-300/50
                    dark:ring-gray-700/30'
                    };

                    $eventClass = match($activity->event) {
                    'created' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 ring-1
                    ring-emerald-300/50 dark:ring-emerald-700/30',
                    'updated' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 ring-1
                    ring-blue-300/50 dark:ring-blue-700/30',
                    'deleted' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 ring-1 ring-red-300/50
                    dark:ring-red-700/30',
                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
                    };
                    @endphp

                    <!-- Header with responsive layout -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center mb-1 gap-1.5">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md text-xs font-medium {{ $badgeClass }}">
                                    {{ $entityType }}
                                </span>

                                <span
                                    class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md text-xs font-medium {{ $eventClass }}">
                                    {{ ucfirst($activity->event) }}
                                </span>

                                <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5 mr-1"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $timeFormat == 'short' ? $activity->created_at->format('H:i') :
                                    $activity->created_at->format('M j, Y H:i') }}
                                </span>
                            </div>

                            <div
                                class="font-medium text-gray-900 dark:text-white text-sm sm:text-base line-clamp-2 sm:line-clamp-1">
                                @php
                                // Clean up the description to show only the name
                                $description = $activity->description;

                                // Remove prefixes like "Step", "Task", "Document", etc.
                                $description = preg_replace('/^(Step|Task|Document|Required Document|Submitted
                                Document)\s+/i', '', $description);

                                // Remove suffixes like "was created", "was updated", "updated", etc.
                                $description = preg_replace('/\s+(was created|was updated|was
                                deleted|created|updated|deleted)$/i', '', $description);
                                @endphp
                                {{ $description }}
                            </div>
                        </div>

                        <!-- User Badge -->
                        <div class="flex items-center mt-1 sm:mt-0">
                            @if($activity->causer)
                            <div
                                class="flex items-center bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded-full border border-gray-100 dark:border-gray-600 hover:shadow-sm transition-shadow">
                                <div
                                    class="flex-shrink-0 h-5 w-5 sm:h-6 sm:w-6 md:h-7 md:w-7 bg-primary-100 dark:bg-primary-800 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium text-primary-700 dark:text-primary-300 mr-1.5">
                                    {{ substr($activity->causer->name, 0, 1) }}
                                </div>
                                <div
                                    class="text-xs font-medium text-gray-700 dark:text-gray-300 max-w-[80px] sm:max-w-none truncate">
                                    {{ $activity->causer->name }}
                                </div>
                            </div>
                            @else
                            <div
                                class="flex items-center bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded-full border border-gray-100 dark:border-gray-600 hover:shadow-sm transition-shadow">
                                <div
                                    class="flex-shrink-0 h-5 w-5 sm:h-6 sm:w-6 md:h-7 md:w-7 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 mr-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-3.5 sm:w-3.5"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                </div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    System
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Changes - Simplified and Always Visible -->
                    @if(count($activity->properties) > 0 && isset($activity->properties['attributes']) &&
                    $activity->event !== 'created')
                    <div class="mt-3">
                        <div
                            class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-3 text-xs sm:text-sm border border-gray-100 dark:border-gray-700">
                            <div class="space-y-3">
                                @foreach($activity->properties['attributes'] as $key => $value)
                                @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] !==
                                $value)
                                @php
                                // Format values to be more human-readable
                                $oldValue = $activity->properties['old'][$key];
                                $newValue = $value;

                                // Get appropriate status classes
                                $oldStatusClass = 'bg-gray-50 dark:bg-gray-900/20 border-gray-100 dark:border-gray-800
                                text-gray-800 dark:text-gray-300';
                                $newStatusClass = 'bg-gray-50 dark:bg-gray-900/20 border-gray-100 dark:border-gray-800
                                text-gray-800 dark:text-gray-300';

                                // Special status field styling
                                if ($key == 'status') {
                                // Format status values
                                if (is_string($oldValue)) {
                                $oldValue = ucwords(str_replace('_', ' ', $oldValue));
                                }
                                if (is_string($newValue)) {
                                $newValue = ucwords(str_replace('_', ' ', $newValue));
                                }

                                // Status-specific styling for old value
                                $oldStatusClass = match (strtolower(str_replace(' ', '_', $oldValue))) {
                                'pending', 'pending_review', 'draft', 'waiting_for_documents', 'analysis', 'in_progress'
                                =>
                                'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-800 text-amber-800
                                dark:text-amber-300',
                                'completed', 'approved' =>
                                'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-800
                                text-emerald-800 dark:text-emerald-300',
                                'rejected', 'blocked', 'canceled' =>
                                'bg-red-50 dark:bg-red-900/20 border-red-100 dark:border-red-800 text-red-800
                                dark:text-red-300',
                                default =>
                                'bg-gray-50 dark:bg-gray-900/20 border-gray-100 dark:border-gray-800 text-gray-800
                                dark:text-gray-300'
                                };

                                // Status-specific styling for new value
                                $newStatusClass = match (strtolower(str_replace(' ', '_', $newValue))) {
                                'pending', 'pending_review', 'draft', 'waiting_for_documents', 'analysis', 'in_progress'
                                =>
                                'bg-amber-50 dark:bg-amber-900/20 border-amber-100 dark:border-amber-800 text-amber-800
                                dark:text-amber-300',
                                'completed', 'approved' =>
                                'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-100 dark:border-emerald-800
                                text-emerald-800 dark:text-emerald-300',
                                'rejected', 'blocked', 'canceled' =>
                                'bg-red-50 dark:bg-red-900/20 border-red-100 dark:border-red-800 text-red-800
                                dark:text-red-300',
                                default =>
                                'bg-gray-50 dark:bg-gray-900/20 border-gray-100 dark:border-gray-800 text-gray-800
                                dark:text-gray-300'
                                };
                                }
                                @endphp

                                <div
                                    class="bg-white dark:bg-gray-700/50 p-3 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
                                    <div
                                        class="font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center text-xs sm:text-sm">
                                        <span
                                            class="px-2 py-0.5 rounded bg-primary-100 dark:bg-primary-900/30 text-primary-800 dark:text-primary-300">
                                            {{ ucfirst($key) }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4">
                                        <div class="flex items-center p-2 rounded-md border {{ $oldStatusClass }}">
                                            <div
                                                class="w-1 sm:w-1.5 h-full min-h-[40px] bg-gray-400 dark:bg-gray-500 rounded mr-2 flex-shrink-0">
                                            </div>
                                            <div class="flex-1 flex flex-col">
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Previous</span>
                                                <span class="text-sm font-medium break-words">
                                                    {{ is_array($oldValue) ? json_encode($oldValue, JSON_PRETTY_PRINT) :
                                                    $oldValue }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center p-2 rounded-md border {{ $newStatusClass }}">
                                            <div
                                                class="w-1 sm:w-1.5 h-full min-h-[40px] bg-primary-400 dark:bg-primary-600 rounded mr-2 flex-shrink-0">
                                            </div>
                                            <div class="flex-1 flex flex-col">
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1">Current</span>
                                                <span class="text-sm font-medium break-words">
                                                    {{ is_array($newValue) ? json_encode($newValue, JSON_PRETTY_PRINT) :
                                                    $newValue }}
                                                </span>
                                            </div>
                                        </div>
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
        </div>
        @empty
        <div class="px-4 py-8 text-center">
            <div
                class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 mb-4 animate-pulse">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white">{{ $emptyMessage }}</h3>
            <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ $emptyDescription }}</p>
        </div>
        @endforelse
    </div>
</div>