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
        .document-item,
        .task-item,
        .review-item {
            transition: all 0.2s ease;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        .document-item:hover,
        .task-item:hover,
        .review-item:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.03), rgba(147, 51, 234, 0.02));
            border-color: rgba(59, 130, 246, 0.1);
            transform: translateX(2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .dark .document-item:hover,
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

        .document-item:hover .item-icon,
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
             activeAccordion: null,
             hasDocuments: {{ $dashboardStats['submitted_documents'] > 0 && !empty($userDocuments) ? 'true' : 'false' }},
             hasTasks: {{ $dashboardStats['today_tasks'] > 0 && !empty($todayTasks) ? 'true' : 'false' }},
             hasReviews: {{ $dashboardStats['documents_need_review'] > 0 && !auth()->user()->hasRole(['staff', 'client']) && !empty($documentsNeedReview) ? 'true' : 'false' }},
             toggle(accordion) {
                 this.activeAccordion = this.activeAccordion === accordion ? null : accordion;
             },
             get showDocuments() { return this.activeAccordion === 'documents'; },
             get showTasks() { return this.activeAccordion === 'tasks'; },
             get showReviews() { return this.activeAccordion === 'reviews'; }
         }">
        <div class="welcome-card rounded-xl">
            <div class="relative p-6">
                {{-- Greeting Section --}}
                <div class="mb-6">
                    <div class="flex items-center gap-6">
                        {{-- Avatar Section --}}
                        <div class="flex-shrink-0">
                            <div class="relative">
                                <div
                                    class="w-16 h-16 rounded-full overflow-hidden border-2 border-slate-200 dark:border-slate-600 shadow-sm">
                                    @if($userAvatar)
                                    <img src="{{ $userAvatar }}" alt="{{ auth()->user()->name }}"
                                        class="w-full h-full object-cover">
                                    @else
                                    <div
                                        class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600">
                                        <span class="text-xl font-bold text-slate-600 dark:text-slate-300">{{
                                            $userInitial }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div
                                    class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 border-2 border-white dark:border-slate-800 rounded-full">
                                </div>
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
                                        <path fill-rule="evenodd"
                                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $currentDate }}</span>
                                </div>
                                <span class="w-1 h-1 bg-slate-400 rounded-full"></span>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $currentTime }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats Cards with Alpine.js --}}
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Document Card --}}
                        <div class="stat-item rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div
                                    class="w-8 h-8 bg-blue-50 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                @if($dashboardStats['pending_documents'] > 0)
                                <div
                                    class="px-2 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-medium rounded">
                                    {{ $dashboardStats['pending_documents'] }} pending
                                </div>
                                @elseif($dashboardStats['rejected_documents'] > 0)
                                <div
                                    class="px-2 py-1 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-xs font-medium rounded">
                                    {{ $dashboardStats['rejected_documents'] }} ditolak
                                </div>
                                @endif
                            </div>

                            <button @click="hasDocuments ? toggle('documents') : null" class="w-full text-left group"
                                :class="{ 'cursor-default': !hasDocuments }">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-white">
                                            {{ $dashboardStats['approved_documents'] }}/{{
                                            $dashboardStats['submitted_documents'] }}
                                        </div>
                                        <div class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Dokumen
                                            Saya</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400">{{ $documentMessage }}
                                        </div>
                                    </div>
                                    <svg x-show="hasDocuments"
                                        class="w-4 h-4 text-slate-400 transition-all duration-300 group-hover:text-blue-500"
                                        :class="{ 'rotate-180 text-blue-500': showDocuments }" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>

                            @if($dashboardStats['submitted_documents'] > 0)
                            <div class="mt-3">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-1.5 rounded-full transition-all duration-500"
                                        style="width: {{ $this->calculateProgress($dashboardStats['approved_documents'], $dashboardStats['submitted_documents']) }}%">
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Document Accordion Content --}}
                            @if(!empty($userDocuments))
                            <div x-show="showDocuments" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 transform -translate-y-2 scale-95"
                                class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-600">
                                <div class="space-y-3">
                                    @foreach($userDocuments as $index => $doc)
                                    <div x-show="showDocuments" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform translate-x-0"
                                        x-transition:leave-end="opacity-0 transform translate-x-4"
                                        style="transition-delay: {{ $index * 50 }}ms"
                                        class="document-item flex items-center gap-3 p-3 rounded-lg">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-3 h-3 rounded-full item-icon
                                                        {{ $doc['status'] === 'approved' ? 'bg-green-400 shadow-lg shadow-green-200' : '' }}
                                                        {{ $doc['status'] === 'pending_review' ? 'bg-amber-400 shadow-lg shadow-amber-200 animate-pulse' : '' }}
                                                        {{ $doc['status'] === 'uploaded' ? 'bg-blue-400 shadow-lg shadow-blue-200' : '' }}
                                                        {{ $doc['status'] === 'rejected' ? 'bg-red-400 shadow-lg shadow-red-200' : '' }}">
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-xs font-semibold text-slate-900 dark:text-white truncate leading-tight">
                                                {{ $doc['required_document']['name'] ?? 'Document' }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                                {{
                                                $doc['required_document']['project_step']['project']['client']['name']
                                                ?? 'Client' }}
                                            </p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                                {{ \Carbon\Carbon::parse($doc['created_at'])->format('d M, H:i') }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span
                                                class="status-badge
                                                        {{ $doc['status'] === 'approved' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-200' : '' }}
                                                        {{ $doc['status'] === 'pending_review' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200' : '' }}
                                                        {{ $doc['status'] === 'uploaded' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200' : '' }}
                                                        {{ $doc['status'] === 'rejected' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-200' : '' }}">
                                                {{ $doc['status'] === 'pending_review' ? 'Review' :
                                                ucfirst(str_replace('_', ' ', $doc['status'])) }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Task Card --}}
                        <div class="stat-item rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div
                                    class="w-8 h-8 bg-green-50 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                @if($dashboardStats['incomplete_tasks_today'] > 0)
                                <div
                                    class="px-2 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 text-xs font-medium rounded">
                                    {{ $dashboardStats['incomplete_tasks_today'] }} belum selesai
                                </div>
                                @endif
                            </div>

                            <button @click="hasTasks ? toggle('tasks') : null" class="w-full text-left group"
                                :class="{ 'cursor-default': !hasTasks }">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-white">
                                            {{ $dashboardStats['completed_tasks_today'] }}/{{
                                            $dashboardStats['today_tasks'] }}
                                        </div>
                                        <div class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Task
                                            Aktif</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400">{{ $taskMessage }}</div>
                                    </div>
                                    <svg x-show="hasTasks"
                                        class="w-4 h-4 text-slate-400 transition-all duration-300 group-hover:text-green-500"
                                        :class="{ 'rotate-180 text-green-500': showTasks }" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>

                            @if($dashboardStats['today_tasks'] > 0)
                            <div class="mt-3">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-1.5 rounded-full transition-all duration-500"
                                        style="width: {{ $this->calculateProgress($dashboardStats['completed_tasks_today'], $dashboardStats['today_tasks']) }}%">
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Task Accordion Content --}}
                            @if(!empty($todayTasks))
                            <div x-show="showTasks" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 transform -translate-y-2 scale-95"
                                class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-600">
                                <div class="space-y-3">
                                    @foreach($todayTasks as $index => $task)
                                    <div x-show="showTasks" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform translate-x-0"
                                        x-transition:leave-end="opacity-0 transform translate-x-4"
                                        style="transition-delay: {{ $index * 50 }}ms"
                                        class="task-item flex items-center gap-3 p-3 rounded-lg">
                                        <div class="priority-indicator flex-shrink-0
                                                    {{ $task['priority'] === 'urgent' ? 'bg-red-400' : '' }}
                                                    {{ $task['priority'] === 'high' ? 'bg-orange-400' : '' }}
                                                    {{ $task['priority'] === 'normal' ? 'bg-blue-400' : '' }}
                                                    {{ $task['priority'] === 'low' ? 'bg-slate-400' : '' }}">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-xs font-semibold text-slate-900 dark:text-white truncate leading-tight">
                                                {{ $task['title'] }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                                {{ $task['project']['client']['name'] ?? 'No Project' }} • {{
                                                ucfirst($task['priority']) }}
                                            </p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                                Due: {{ \Carbon\Carbon::parse($task['task_date'])->format('d M Y') }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span
                                                class="status-badge
                                                        {{ $task['status'] === 'completed' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-200' : '' }}
                                                        {{ $task['status'] === 'in_progress' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200' : '' }}
                                                        {{ $task['status'] === 'pending' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200' : '' }}
                                                        {{ $task['status'] === 'cancelled' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-200' : '' }}">
                                                {{ $task['status'] === 'in_progress' ? 'Progress' :
                                                ucfirst(str_replace('_', ' ', $task['status'])) }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Review Card --}}
                        <div class="stat-item rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div
                                    class="w-8 h-8 bg-orange-50 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                @if($dashboardStats['documents_need_review'] > 0 && !auth()->user()->hasRole(['staff',
                                'client']))
                                <div
                                    class="px-2 py-1 bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 text-xs font-medium rounded animate-pulse">
                                    {{ $dashboardStats['documents_need_review'] }} perlu review
                                </div>
                                @endif
                            </div>

                            <button @click="hasReviews ? toggle('reviews') : null" class="w-full text-left group"
                                :class="{ 'cursor-default': !hasReviews }">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xl font-bold text-slate-900 dark:text-white">{{
                                            $dashboardStats['documents_need_review'] }}</div>
                                        <div class="text-sm font-medium text-orange-600 dark:text-orange-400 mb-1">Perlu
                                            Review</div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400">{{ $reviewMessage }}
                                        </div>
                                    </div>
                                    <svg x-show="hasReviews"
                                        class="w-4 h-4 text-slate-400 transition-all duration-300 group-hover:text-orange-500"
                                        :class="{ 'rotate-180 text-orange-500': showReviews }" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>

                            {{-- Review Accordion Content --}}
                            @if(!empty($documentsNeedReview) && !auth()->user()->hasRole(['staff', 'client']))
                            <div x-show="showReviews" x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 transform -translate-y-2 scale-95"
                                class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-600">
                                <div class="space-y-3">
                                    @foreach($documentsNeedReview as $index => $doc)
                                    <div x-show="showReviews" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 transform translate-x-4"
                                        x-transition:enter-end="opacity-100 transform translate-x-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 transform translate-x-0"
                                        x-transition:leave-end="opacity-0 transform translate-x-4"
                                        style="transition-delay: {{ $index * 50 }}ms"
                                        class="review-item flex items-center gap-3 p-3 rounded-lg">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center item-icon">
                                                <svg class="w-5 h-5 text-slate-600 dark:text-slate-300"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-xs font-semibold text-slate-900 dark:text-white truncate leading-tight">
                                                {{ $doc['required_document']['name'] ?? 'Document' }}
                                            </p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                                {{
                                                $doc['required_document']['project_step']['project']['client']['name']
                                                ?? 'Client' }} •
                                                Oleh {{ $doc['user']['name'] ?? 'Unknown' }}
                                            </p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                                {{ \Carbon\Carbon::parse($doc['created_at'])->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span
                                                class="status-badge
                                                        {{ $doc['status'] === 'pending_review' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200' : '' }}
                                                        {{ $doc['status'] === 'uploaded' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200' : '' }}">
                                                {{ $doc['status'] === 'pending_review' ? 'Review' : 'Uploaded' }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                @if(count($documentsNeedReview) >= 5)
                                <div class="mt-3 text-center">
                                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                                        class="text-xs text-orange-600 dark:text-orange-400 hover:underline font-medium transition-colors">
                                        Lihat semua dokumen yang perlu review →
                                    </a>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>