<x-filament::page>
    <div class="space-y-8">
        <!-- Header with stats -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden border border-gray-200 dark:border-gray-700 transition-colors">
            <!-- Header Section - White background with colored accents -->
            <div class="bg-white dark:bg-gray-800 p-4 md:p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <h2 class="text-xl md:text-2xl font-bold flex items-center text-primary-600 dark:text-primary-400">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 md:h-6 md:w-6 mr-2 text-primary-600 dark:text-primary-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Activity Timeline
                    </h2>

                    <!-- Client, Project Info & Report Button - Clean styling -->
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('filament.admin.resources.clients.view', ['record' => $record->client]) }}"
                            class="inline-flex items-center px-3 py-1 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-primary-600 dark:text-primary-400 rounded-full text-sm transition-colors group border border-gray-200 dark:border-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-primary-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="truncate">{{ $record->client->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5 ml-1 opacity-0 group-hover:opacity-100 transition-opacity text-primary-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>

                        <a href="{{ route('filament.admin.resources.projects.edit', $record) }}"
                            class="inline-flex items-center px-3 py-1 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-primary-600 dark:text-primary-400 rounded-full text-sm font-medium transition-colors group border border-gray-200 dark:border-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-primary-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="truncate">{{ $record->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="h-3.5 w-3.5 ml-1 opacity-0 group-hover:opacity-100 transition-opacity text-primary-500"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>

                        <!-- Generate Report Button -->
                        <button type="button" x-data="{}" x-on:click="
                        $wire.generateProjectReport().then(url => {
                            window.open(url, '_blank');
                        })
                    " class="inline-flex items-center px-3 py-1 bg-primary-600 hover:bg-primary-700 dark:bg-primary-700 dark:hover:bg-primary-600 text-white rounded-full text-sm font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Generate Report
                        </button>
                    </div>
                </div>

                <p class="mt-3 text-sm md:text-base text-gray-600 dark:text-gray-400">
                    Tracking all changes and activities for this project
                </p>
            </div>

            <!-- Activity Stats - White cards with colored icons -->
            <div class="p-4 md:p-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
                <!-- Created Stat -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg p-3 md:p-4 border border-gray-200 dark:border-gray-700 flex items-center transition-colors hover:shadow-sm hover:border-green-300 dark:hover:border-green-700">
                    <div
                        class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full mr-3 md:mr-4 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs md:text-sm font-medium text-green-600 dark:text-green-400">Created</div>
                        <div class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">{{
                            $activities->where('event', 'created')->count() }}</div>
                    </div>
                </div>

                <!-- Updated Stat -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg p-3 md:p-4 border border-gray-200 dark:border-gray-700 flex items-center transition-colors hover:shadow-sm hover:border-blue-300 dark:hover:border-blue-700">
                    <div
                        class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full mr-3 md:mr-4 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs md:text-sm font-medium text-blue-600 dark:text-blue-400">Updated</div>
                        <div class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">{{
                            $activities->where('event', 'updated')->count() }}</div>
                    </div>
                </div>

                <!-- Total Activity Stat -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg p-3 md:p-4 border border-gray-200 dark:border-gray-700 flex items-center transition-colors hover:shadow-sm hover:border-amber-300 dark:hover:border-amber-700">
                    <div
                        class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 rounded-full mr-3 md:mr-4 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs md:text-sm font-medium text-amber-600 dark:text-amber-400">Total Activity
                        </div>
                        <div class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">{{
                            $activities->count() }}</div>
                    </div>
                </div>

                <!-- Recent Activity Stat -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg p-3 md:p-4 border border-gray-200 dark:border-gray-700 flex items-center transition-colors hover:shadow-sm hover:border-purple-300 dark:hover:border-purple-700">
                    <div
                        class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full mr-3 md:mr-4 shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs md:text-sm font-medium text-purple-600 dark:text-purple-400">Recent Activity
                        </div>
                        <div class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">{{
                            $activities->where('created_at', '>=', now()->subDays(7))->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
            <div
                class="border-b border-gray-200 dark:border-gray-700 px-4 md:px-6 py-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Activity Timeline
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">All activities are sorted chronologically
                    </p>
                </div>


            </div>

            <!-- Today's Activities -->
            <x-activity-group :activities="$activities->where('created_at', '>=', today())" title="Today"
                subtitle="{{ now()->format('F j, Y') }}"
                labelBgClass="bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-300"
                timeFormat="short" emptyMessage="No activities today"
                emptyDescription="There have been no recorded activities for today." />

            <!-- Yesterday's Activities -->
            <x-activity-group
                :activities="$activities->where('created_at', '>=', today()->subDay())->where('created_at', '<', today())"
                title="Yesterday" subtitle="{{ now()->subDay()->format('F j, Y') }}"
                labelBgClass="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300" timeFormat="short"
                emptyMessage="No activities yesterday"
                emptyDescription="There were no recorded activities for yesterday." />

            <!-- Older Activities -->
            <x-activity-group :activities="$activities->where('created_at', '<', today()->subDay())" title="Older"
                subtitle="Prior to {{ now()->subDay()->format('F j, Y') }}"
                labelBgClass="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300" timeFormat="long"
                emptyMessage="No older activities"
                emptyDescription="There are no recorded activities older than yesterday." />
        </div>




    </div>
</x-filament::page>