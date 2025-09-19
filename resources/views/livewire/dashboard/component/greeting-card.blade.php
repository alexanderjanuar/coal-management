<div>
    <style>
        /* Alpine.js accordion animations */
        .accordion-content {
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced subtle styling */
        .welcome-card {
            background: linear-gradient(135deg, #fafafa 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dark .welcome-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid #475569;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }

        .dark .stat-item {
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(71, 85, 105, 0.2);
        }

        .stat-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced item styling */
        .project-item,
        .task-item,
        .review-item {
            transition: all 0.2s ease;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        .project-item:hover,
        .task-item:hover,
        .review-item:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.03), rgba(147, 51, 234, 0.02));
            border-color: rgba(59, 130, 246, 0.1);
            transform: translateX(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .dark .project-item:hover,
        .dark .task-item:hover,
        .dark .review-item:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(147, 51, 234, 0.05));
            border-color: rgba(59, 130, 246, 0.2);
        }

        .status-badge {
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .priority-indicator {
            width: 4px;
            height: 100%;
            border-radius: 2px;
            box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
        }

        /* Icon styling */
        .item-icon {
            transition: all 0.2s ease;
        }

        .project-item:hover .item-icon,
        .task-item:hover .item-icon,
        .review-item:hover .item-icon {
            transform: scale(1.1);
        }

        /* Empty state styling */
        .empty-state {
            padding: 16px;
            text-align: center;
            color: #64748b;
            font-style: italic;
        }

        .dark .empty-state {
            color: #94a3b8;
        }
    </style>

    {{-- Welcome Card with Alpine.js --}}
    <div class="mb-6" x-data="{
        showDetails: false,
        nearCompletionProjects: @js($this->getNearCompletionProjects()),
        get hasNearCompletionProjects() { return this.nearCompletionProjects.length > 0; },
        get hasTasks() { return {{ $dashboardStats['today_tasks'] > 0 && !empty($todayTasks) ? 'true' : 'false' }}; },
        get hasReviews() { return {{ $dashboardStats['documents_need_review'] > 0 && !auth()->user()->hasRole(['staff', 'client']) && !empty($documentsNeedReview) ? 'true' : 'false' }}; }
    }">
        <div class="welcome-card rounded-xl">
            <div class="relative p-6">
                {{-- Greeting Section --}}
                <div class="mb-6">
                    <div class="flex items-center gap-6">
                        {{-- Avatar Section --}}
                        <div class="flex-shrink-0">
                            <div class="relative">
                                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-slate-200 dark:border-slate-600 shadow-sm">
                                    @if($userAvatar)
                                    <img src="{{ $userAvatar }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600">
                                        <span class="text-xl font-bold text-slate-600 dark:text-slate-300">{{ $userInitial }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 border-2 border-white dark:border-slate-800 rounded-full"></div>
                            </div>
                        </div>

                        {{-- Greeting Content --}}
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-1">
                                {{ $greeting }}, {{ $firstName }}!
                            </h1>
                            <div class="flex items-center gap-4 text-sm text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $currentDate }}</span>
                                </div>
                                <span class="w-1 h-1 bg-slate-400 rounded-full"></span>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $currentTime }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats Cards --}}
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Near Completion Projects Card --}}
                        <div class="stat-item rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-amber-50 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div x-show="nearCompletionProjects.length > 0" class="px-2 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-medium rounded">
                                    70%+ selesai
                                </div>
                            </div>

                            <div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white" x-text="nearCompletionProjects.length"></div>
                                <div class="text-sm font-medium text-amber-600 dark:text-amber-400 mb-1">Proyek Hampir Selesai</div>
                                <div class="text-xs text-slate-600 dark:text-slate-400">
                                    <span x-show="nearCompletionProjects.length === 0">Belum ada proyek yang mendekati penyelesaian</span>
                                    <span x-show="nearCompletionProjects.length === 1">1 proyek hampir selesai - finishing touch!</span>
                                    <span x-show="nearCompletionProjects.length > 1" x-text="nearCompletionProjects.length + ' proyek hampir selesai - push terakhir!'"></span>
                                </div>
                            </div>

                            <div x-show="nearCompletionProjects.length > 0" class="mt-3">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-1.5 rounded-full transition-all duration-500" style="width: 85%"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Task Card --}}
                        <div class="stat-item rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                @if($dashboardStats['incomplete_tasks_today'] > 0)
                                <div class="px-2 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-medium rounded">
                                    {{ $dashboardStats['incomplete_tasks_today'] }} belum selesai
                                </div>
                                @endif
                            </div>

                            <div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">
                                    {{ $dashboardStats['completed_tasks_today'] }}/{{ $dashboardStats['today_tasks'] }}
                                </div>
                                <div class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Task Aktif</div>
                                <div class="text-xs text-slate-600 dark:text-slate-400">{{ $taskMessage }}</div>
                            </div>

                            @if($dashboardStats['today_tasks'] > 0)
                            <div class="mt-3">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $this->calculateProgress($dashboardStats['completed_tasks_today'], $dashboardStats['today_tasks']) }}%"></div>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Review Card --}}
                        <div class="stat-item rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-orange-50 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                @if($dashboardStats['documents_need_review'] > 0 && !auth()->user()->hasRole(['staff', 'client']))
                                <div class="px-2 py-1 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-medium rounded animate-pulse">
                                    {{ $dashboardStats['documents_need_review'] }} perlu review
                                </div>
                                @endif
                            </div>

                            <div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white">{{ $dashboardStats['documents_need_review'] }}</div>
                                <div class="text-sm font-medium text-orange-600 dark:text-orange-400 mb-1">Perlu Review</div>
                                <div class="text-xs text-slate-600 dark:text-slate-400">{{ $reviewMessage }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modern Fluid Accordion Section - Always Show --}}
                <div class="mt-6">
                    <div class="bg-gradient-to-r from-slate-50 to-gray-50 dark:from-slate-800/50 dark:to-gray-800/50 rounded-xl border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
                        <button @click="showDetails = !showDetails" 
                                class="group w-full px-6 py-4 flex items-center justify-between text-left hover:bg-white/50 dark:hover:bg-slate-700/30 transition-all duration-300 ease-out">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/50 dark:to-indigo-900/50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-full opacity-60 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                                <div>
                                    <span class="text-base font-semibold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Detail Progress Hari Ini</span>
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Lihat ringkasan aktivitas dan progress terkini</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full" :class="hasNearCompletionProjects ? 'bg-amber-400' : 'bg-slate-300 dark:bg-slate-600'"></div>
                                    <div class="w-1.5 h-1.5 rounded-full" :class="hasTasks ? 'bg-green-400' : 'bg-slate-300 dark:bg-slate-600'"></div>
                                    <div class="w-1.5 h-1.5 rounded-full" :class="hasReviews ? 'bg-orange-400' : 'bg-slate-300 dark:bg-slate-600'"></div>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 dark:text-slate-500 transform transition-all duration-300 group-hover:text-blue-500" 
                                     :class="{ 'rotate-180 text-blue-500 dark:text-blue-400': showDetails }" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>

                        <div x-show="showDetails" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 transform translate-y-0"
                             x-transition:leave-end="opacity-0 transform -translate-y-2"
                             class="border-t border-slate-200/50 dark:border-slate-700/50">
                            <div class="p-6 bg-white/30 dark:bg-slate-800/30">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    
                                    {{-- Near Completion Projects --}}
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/50 dark:to-orange-900/50 rounded-lg flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-gradient-to-br from-amber-400 to-orange-400 rounded-full"></div>
                                            </div>
                                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Proyek Hampir Selesai</h4>
                                        </div>
                                        
                                        <div class="space-y-3 max-h-64 overflow-y-auto">
                                            <template x-for="(projectData, index) in nearCompletionProjects.slice(0, 4)" :key="index">
                                                <a :href="'{{ route('filament.admin.resources.projects.view', '') }}/' + projectData.project.id"
                                                   x-transition:enter="transition ease-out duration-200"
                                                   x-transition:enter-start="opacity-0 transform translate-x-4"
                                                   x-transition:enter-end="opacity-100 transform translate-x-0"
                                                   :style="'transition-delay: ' + (index * 50) + 'ms'"
                                                   class="block group project-item bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm rounded-lg p-4 border border-slate-200/50 dark:border-slate-700/50 hover:bg-white dark:hover:bg-slate-800 hover:shadow-md transition-all duration-200 cursor-pointer">
                                                    <div class="flex items-start justify-between mb-3">
                                                        <div class="flex-1 min-w-0">
                                                            <h5 class="text-sm font-semibold text-slate-900 dark:text-white truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors" x-text="projectData.project.name"></h5>
                                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1" x-text="projectData.project.client ? projectData.project.client.name : 'No Client'"></p>
                                                        </div>
                                                        <div class="text-right ml-3">
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-amber-100 to-orange-100 dark:from-amber-900/50 dark:to-orange-900/50 text-amber-700 dark:text-amber-300" x-text="projectData.progress + '%'"></span>
                                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1" x-text="projectData.completed_items + '/' + projectData.total_items + ' items'"></div>
                                                        </div>
                                                    </div>
                                                    <div class="relative">
                                                        <div class="w-full bg-slate-200/50 dark:bg-slate-700/50 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-gradient-to-r from-amber-400 via-amber-500 to-orange-500 h-2 rounded-full transition-all duration-500 ease-out" 
                                                                 :style="'width: ' + projectData.progress + '%'"></div>
                                                        </div>
                                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent opacity-50 animate-pulse rounded-full"></div>
                                                    </div>
                                                </a>
                                            </template>
                                            
                                            <div x-show="nearCompletionProjects.length === 0" class="text-center py-8">
                                                <div class="w-12 h-12 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                                    <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Belum ada proyek hampir selesai</p>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Proyek akan muncul ketika progress ≥ 70%</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Today's Tasks --}}
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/50 dark:to-emerald-900/50 rounded-lg flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-gradient-to-br from-green-400 to-emerald-400 rounded-full"></div>
                                            </div>
                                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Task Hari Ini</h4>
                                        </div>
                                        
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @if(!empty($todayTasks))
                                                @foreach($todayTasks as $index => $task)
                                                    <div class="group task-item bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm rounded-lg p-3 border border-slate-200/50 dark:border-slate-700/50 hover:bg-white dark:hover:bg-slate-800 hover:shadow-md transition-all duration-200"
                                                         style="animation-delay: {{ $index * 50 }}ms">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                                <div class="w-2 h-8 rounded-full {{ 
                                                                    $task['priority'] === 'urgent' ? 'bg-gradient-to-b from-red-400 to-red-500' : 
                                                                    ($task['priority'] === 'high' ? 'bg-gradient-to-b from-orange-400 to-orange-500' : 
                                                                    ($task['priority'] === 'normal' ? 'bg-gradient-to-b from-blue-400 to-blue-500' : 
                                                                    'bg-gradient-to-b from-slate-300 to-slate-400'))
                                                                }}"></div>
                                                                <div class="flex-1 min-w-0">
                                                                    <h5 class="text-sm font-medium text-slate-900 dark:text-white truncate group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">{{ $task['title'] }}</h5>
                                                                    @if(isset($task['project']['name']))
                                                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $task['project']['name'] }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            <span class="ml-3 px-2 py-1 rounded-full text-xs font-medium {{ 
                                                                $task['status'] === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300' : 
                                                                ($task['status'] === 'in_progress' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 
                                                                'bg-slate-100 text-slate-700 dark:bg-slate-700/50 dark:text-slate-300')
                                                            }}">
                                                                @if($task['status'] === 'completed') ✓ Selesai
                                                                @elseif($task['status'] === 'in_progress') ⟳ Berjalan
                                                                @else ○ Pending @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-center py-8">
                                                    <div class="w-12 h-12 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                                        <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Tidak ada task aktif</p>
                                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Task akan muncul sesuai jadwal periode</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Review Documents --}}
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-6 h-6 bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-900/50 dark:to-red-900/50 rounded-lg flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-gradient-to-br from-orange-400 to-red-400 rounded-full"></div>
                                            </div>
                                            <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Dokumen Perlu Review</h4>
                                        </div>
                                        
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @if(!empty($documentsNeedReview) && !auth()->user()->hasRole(['staff', 'client']))
                                                @foreach($documentsNeedReview as $index => $doc)
                                                    <a href="{{ route('filament.admin.resources.projects.view', $doc['required_document']['project_step']['project']['id'] ?? 1) }}"
                                                       class="block group review-item bg-white/70 dark:bg-slate-800/70 backdrop-blur-sm rounded-lg p-3 border border-slate-200/50 dark:border-slate-700/50 hover:bg-white dark:hover:bg-slate-800 hover:shadow-md transition-all duration-200 cursor-pointer"
                                                       style="animation-delay: {{ $index * 50 }}ms">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                                                <div class="w-8 h-8 bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-900/50 dark:to-red-900/50 rounded-lg flex items-center justify-center flex-shrink-0">
                                                                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <h5 class="text-sm font-medium text-slate-900 dark:text-white truncate group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">{{ $doc['required_document']['name'] ?? 'Document' }}</h5>
                                                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ $doc['required_document']['project_step']['project']['client']['name'] ?? 'Client' }} • {{ $doc['user']['name'] ?? 'Unknown' }}</p>
                                                                </div>
                                                            </div>
                                                            <span class="ml-3 px-2 py-1 rounded-full text-xs font-medium {{ 
                                                                $doc['status'] === 'pending_review' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'
                                                            }}">
                                                                {{ $doc['status'] === 'pending_review' ? '👀 Review' : '📤 Uploaded' }}
                                                            </span>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            @else
                                                <div class="text-center py-8">
                                                    <div class="w-12 h-12 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center mx-auto mb-3">
                                                        <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                                                        @if(auth()->user()->hasRole(['staff', 'client']))
                                                            Tidak ada akses review dokumen
                                                        @else
                                                            Semua dokumen sudah direview
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                                        @if(auth()->user()->hasRole(['staff', 'client']))
                                                            Role Anda tidak memiliki izin review
                                                        @else
                                                            Dokumen baru akan muncul di sini
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
</div>