<div>
    {{-- Content --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
        
        {{-- Left Section: Greeting & Quote --}}
        <div class="flex-1 min-w-0">
            {{-- Date & Time Badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-medium mb-4 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors cursor-default">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                <span>{{ $this->currentDate }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-400 dark:bg-gray-600"></span>
                <span wire:poll.60s class="tabular-nums">{{ $this->currentTime }}</span>
            </div>

            {{-- Main Greeting with Animated Emoji --}}
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl sm:text-4xl select-none" style="animation: bounce 3s ease-in-out infinite;">{{ $this->greetingEmoji }}</span>
                <div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">
                        {{ $this->greeting }}, <span class="text-primary-600 dark:text-primary-400">{{ $this->userName }}</span>!
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Siap untuk hari yang produktif?
                    </p>
                </div>
            </div>

            {{-- Motivational Quote - Single Line --}}
            <div class="mt-4">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-700 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400 text-sm italic truncate">
                        {{ $this->motivationalQuote['quote'] }}
                    </p>
                    <span class="text-gray-400 dark:text-gray-600 text-xs font-medium whitespace-nowrap">
                        — {{ $this->motivationalQuote['author'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Right Section: Today's Stats Dashboard --}}
        <div class="lg:shrink-0 lg:w-80">


            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 gap-2">
                {{-- Active Projects --}}
                <a href="{{ route('filament.admin.resources.projects.index') }}" 
                   class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 group cursor-pointer">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100 tabular-nums leading-none">{{ $this->todayStats['active_projects'] }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-medium text-gray-400 dark:text-gray-600 mt-0.5">Proyek</p>
                        </div>
                    </div>
                </a>

                {{-- Today's Tasks --}}
                <a href="{{ route('filament.admin.pages.daily-task-dashboard') }}" 
                   class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200 group cursor-pointer">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100 tabular-nums leading-none">{{ $this->todayStats['total_tasks'] }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-medium text-gray-400 dark:text-gray-600 mt-0.5">Tugas</p>
                        </div>
                    </div>
                </a>

                {{-- Completed Tasks --}}
                <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100 tabular-nums leading-none">{{ $this->todayStats['completed_tasks'] }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-medium text-green-600 dark:text-green-400 mt-0.5">Selesai</p>
                        </div>
                    </div>
                </div>

                {{-- Pending Tasks --}}
                <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-900 dark:text-gray-100 tabular-nums leading-none">{{ $this->todayStats['pending_tasks'] }}</p>
                            <p class="text-[10px] uppercase tracking-wider font-medium text-amber-600 dark:text-amber-400 mt-0.5">Pending</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Completion Progress Bar - Only show if there are tasks --}}
            @if($this->todayStats['total_tasks'] > 0)
            <div class="mt-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-500">Progress Hari Ini</span>
                    <span class="text-sm font-bold tabular-nums
                        {{ $this->todayStats['completion_rate'] >= 80 ? 'text-green-600 dark:text-green-400' : 
                           ($this->todayStats['completion_rate'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-gray-100') }}">
                        {{ $this->todayStats['completion_rate'] }}%
                    </span>
                </div>
                <div class="w-full h-2 bg-gray-200 dark:bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700 ease-out
                        {{ $this->todayStats['completion_rate'] >= 80 ? 'bg-green-500' : 
                           ($this->todayStats['completion_rate'] >= 50 ? 'bg-amber-500' : 'bg-gray-400') }}"
                        style="width: {{ $this->todayStats['completion_rate'] }}%">
                    </div>
                </div>
                @if($this->todayStats['completion_rate'] >= 80)
                <p class="text-[10px] text-green-600 dark:text-green-400 mt-1.5 font-medium">🎉 Luar biasa! Kamu hampir mencapai target hari ini!</p>
                @elseif($this->todayStats['completion_rate'] >= 50)
                <p class="text-[10px] text-amber-600 dark:text-amber-400 mt-1.5 font-medium">💪 Bagus! Terus lanjutkan progressmu!</p>
                @elseif($this->todayStats['completion_rate'] > 0)
                <p class="text-[10px] text-gray-500 dark:text-gray-500 mt-1.5 font-medium">🚀 Ayo mulai selesaikan tugasmu hari ini!</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    <style>
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
    </style>
</div>
