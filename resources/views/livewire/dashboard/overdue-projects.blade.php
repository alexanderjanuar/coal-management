<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    {{-- Header dengan design --}}
    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 px-6 py-4 border-b border-red-100 dark:border-red-800/30">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-xl flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Proyek Terlambat
                    </h3>
                    <p class="text-sm text-red-600 dark:text-red-400 font-medium">
                        Membutuhkan perhatian segera
                    </p>
                </div>
            </div>
            
            @if($overdueCount > 0)
                <div class="flex items-center gap-2 px-3 py-2 bg-red-100 dark:bg-red-900/40 rounded-lg border border-red-200 dark:border-red-700">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-bold text-red-700 dark:text-red-300">
                        {{ $overdueCount }} proyek
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Content --}}
    <div class="p-6">
        @if(count($overdueProjects) > 0)
            <div class="space-y-4">
                @foreach($overdueProjects as $project)
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-lg cursor-pointer border border-gray-100 dark:border-gray-700 transition-all duration-200 hover:shadow-sm"
                         onclick="window.open('{{ $project['url'] }}', '_blank')">
                        
                        {{-- Left: Project Info --}}
                        <div class="flex-1 min-w-0 pr-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white truncate mb-1">
                                {{ $project['name'] }}
                            </h4>
                            <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $project['client_name'] }}</span>
                                </div>
                                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $project['pic_name'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Days Overdue with Priority --}}
                        <div class="text-right flex items-center gap-3">
                            @if($project['priority'] === 'urgent')
                                <div class="px-2 py-1 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 text-xs font-bold rounded-md border border-red-200 dark:border-red-700">
                                    URGENT
                                </div>
                            @endif
                            <div class="text-right">
                                <div class="text-lg font-bold text-red-600 dark:text-red-400">
                                    {{ $project['days_overdue'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    hari terlambat
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($overdueCount > 5)
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('filament.admin.resources.projects.index') }}" 
                       class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors">
                        <span>Lihat semua {{ $overdueCount }} proyek terlambat</span>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>
            @endif

        @else
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Semua Proyek On Track
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada proyek yang melewati due date
                </p>
            </div>
        @endif
    </div>
</div>