{{-- resources/views/livewire/daily-task/form/daily-task-filter-component.blade.php --}}
{{-- Editorial / utilitarian filter bar — sharp, warm, distinctive --}}
<div class="dtf space-y-3" x-data="{
    showAdvancedFilters: false,
    viewMode: @entangle('filterData.view_mode').live,

    toggleViewMode(mode) {
        this.viewMode = mode;
        Livewire.dispatch('switchViewMode', { mode: mode });
    }
}">

    {{-- ━━━ Row 1 : Search + View Toggle + Filter Button ━━━ --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        {{-- Search --}}
        <div class="flex-1 order-1">
            <div class="relative group">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg class="w-[18px] h-[18px] text-gray-400 dark:text-gray-500 group-focus-within:text-amber-500 dark:group-focus-within:text-amber-400 transition-colors duration-200"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.500ms="filterData.search"
                    placeholder="Cari task, deskripsi, atau proyek…" class="w-full pl-11 pr-10 py-2.5 text-[.82rem] font-medium tracking-tight
                           bg-white dark:bg-gray-800/80
                           border border-gray-200 dark:border-gray-700
                           rounded-lg
                           text-gray-800 dark:text-gray-100
                           placeholder:text-gray-400 dark:placeholder:text-gray-500
                           focus:outline-none focus:ring-2 focus:ring-amber-400/40 focus:border-amber-400
                           dark:focus:ring-amber-500/30 dark:focus:border-amber-500
                           hover:border-gray-300 dark:hover:border-gray-600
                           transition-all duration-200">
                @if(!empty($filterData['search']))
                    <button wire:click="$set('filterData.search', '')" class="absolute right-3 top-1/2 -translate-y-1/2
                                           p-1 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                                           hover:bg-gray-100 dark:hover:bg-gray-700
                                           transition-all duration-150 active:scale-90">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- Controls cluster --}}
        <div class="flex items-center gap-2 flex-shrink-0 order-2">
            {{-- View mode : segmented control --}}
            <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-0.5
                        border border-gray-200 dark:border-gray-700">
                <button type="button" @click="toggleViewMode('kanban')" class="relative flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold tracking-wide uppercase transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'kanban') === 'kanban'
    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm'
    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                    <span class="hidden sm:inline">Board</span>
                </button>
                <button type="button" @click="toggleViewMode('list')" class="relative flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold tracking-wide uppercase transition-all duration-200
                           {{ ($filterData['view_mode'] ?? 'kanban') === 'list'
    ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm'
    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span class="hidden sm:inline">List</span>
                </button>
            </div>

            {{-- Filter button --}}
            <button @click="showAdvancedFilters = !showAdvancedFilters"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold tracking-wide
                       border transition-all duration-200 active:scale-95
                       {{ !empty($activeFilters)
    ? 'border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300'
    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600' }}">
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showAdvancedFilters }"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span class="hidden sm:inline">Filter</span>
                @if(!empty($activeFilters))
                    <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold
                                         text-white bg-amber-500 dark:bg-amber-600 rounded-full leading-none">
                        {{ count($activeFilters) }}
                    </span>
                @endif
            </button>

            {{-- Task count --}}
            <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-2 rounded-lg
                        bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700
                        text-xs font-semibold text-gray-500 dark:text-gray-400 tabular-nums">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                {{ number_format($totalTasks) }} task{{ $totalTasks !== 1 ? 's' : '' }}
            </div>
        </div>
    </div>

    {{-- ━━━ Row 2 : Quick-access pills ━━━ --}}
    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
        {{-- Date presets --}}
        <span
            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mr-1">Tanggal</span>
        @php
            $datePresets = [
                'today' => 'Hari Ini',
                'this_week' => 'Minggu Ini',
                'this_month' => 'Bulan Ini',
                'overdue' => 'Terlambat',
            ];
        @endphp
        @foreach($datePresets as $presetKey => $presetLabel)
            <button wire:click="setDateFilter('{{ $presetKey }}')"
                class="px-2.5 py-1 rounded-md text-[11px] font-semibold tracking-wide
                               border transition-all duration-150 active:scale-95
                               {{ ($filterData['date_preset'] ?? '') === $presetKey
            ? ($presetKey === 'overdue'
                ? 'border-rose-300 dark:border-rose-700 bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300'
                : 'border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300')
            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300' }}">
                {{ $presetLabel }}
            </button>
        @endforeach

        <span class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></span>

        {{-- Status quick toggles --}}
        <span
            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mr-1">Status</span>
        @php
            $statusQuick = [
                'pending' => ['label' => 'Pending', 'dot' => 'bg-gray-400'],
                'in_progress' => ['label' => 'In Progress', 'dot' => 'bg-yellow-500'],
                'completed' => ['label' => 'Selesai', 'dot' => 'bg-emerald-500'],
            ];
        @endphp
        @foreach($statusQuick as $statusKey => $statusMeta)
            @php $isActive = in_array($statusKey, $filterData['status'] ?? []); @endphp
            <button wire:click="toggleQuickFilter('status', '{{ $statusKey }}')"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold tracking-wide
                               border transition-all duration-150 active:scale-95
                               {{ $isActive
            ? 'border-gray-400 dark:border-gray-500 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100'
            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $statusMeta['dot'] }}"></span>
                {{ $statusMeta['label'] }}
            </button>
        @endforeach

        <span class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></span>

        {{-- Priority quick toggles --}}
        <span
            class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mr-1">Prioritas</span>
        @php
            $priorityQuick = [
                'urgent' => ['label' => 'Mendesak', 'dot' => 'bg-rose-500'],
                'high' => ['label' => 'Tinggi', 'dot' => 'bg-amber-500'],
            ];
        @endphp
        @foreach($priorityQuick as $prioKey => $prioMeta)
            @php $isActive = in_array($prioKey, $filterData['priority'] ?? []); @endphp
            <button wire:click="toggleQuickFilter('priority', '{{ $prioKey }}')"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold tracking-wide
                               border transition-all duration-150 active:scale-95
                               {{ $isActive
            ? 'border-gray-400 dark:border-gray-500 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100'
            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $prioMeta['dot'] }}"></span>
                {{ $prioMeta['label'] }}
            </button>
        @endforeach
    </div>

    {{-- ━━━ Active filter chips ━━━ --}}
    @if(!empty($activeFilters))
        <div class="flex flex-wrap items-center gap-1.5 px-3 py-2
                            bg-gray-50/80 dark:bg-gray-800/40
                            border border-gray-200 dark:border-gray-700
                            rounded-lg">

            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mr-1">
                {{ count($activeFilters) }} aktif
            </span>

            @foreach($activeFilters as $filter)
                @php
                    $chipColors = match ($filter['type'] ?? '') {
                        'status' => 'bg-emerald-50 dark:bg-emerald-900/15 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                        'priority' => 'bg-amber-50 dark:bg-amber-900/15 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                        'date', 'date_preset', 'date_range' => 'bg-sky-50 dark:bg-sky-900/15 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800',
                        'project' => 'bg-violet-50 dark:bg-violet-900/15 text-violet-700 dark:text-violet-300 border-violet-200 dark:border-violet-800',
                        'assignee' => 'bg-cyan-50 dark:bg-cyan-900/15 text-cyan-700 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800',
                        default => 'bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                    };
                @endphp
                <div
                    class="inline-flex items-center gap-1 pl-2 pr-1 py-0.5 rounded-md border text-[11px] font-medium {{ $chipColors }}">
                    <span class="opacity-70">{{ $filter['label'] }}:</span>
                    <span class="font-semibold">{{ $filter['value'] }}</span>
                    <button wire:click="removeFilter('{{ $filter['type'] }}')"
                        class="ml-0.5 p-0.5 rounded opacity-60 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition-all">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach

            <button wire:click="resetFilters" class="ml-auto text-[11px] font-semibold text-rose-500 dark:text-rose-400
                               hover:text-rose-700 dark:hover:text-rose-300 transition-colors">
                Reset semua
            </button>
        </div>
    @endif

    {{-- ━━━ Advanced Filter Panel ━━━ --}}
    <div x-show="showAdvancedFilters" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                rounded-xl shadow-lg overflow-hidden">

        <div class="p-4 sm:p-5 space-y-5">
            {{-- Panel header --}}
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Filter Lanjutan</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Atur filter untuk hasil lebih spesifik
                        </p>
                    </div>
                </div>
                <button @click="showAdvancedFilters = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200
                               hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Filament form --}}
            <div class="dtf-form-grid">
                {{ $this->filterForm }}
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700/50">
                <div class="flex items-center gap-2 text-[11px] text-gray-400 dark:text-gray-500">
                    @if(count($activeFilters) > 0)
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span class="font-medium">{{ count($activeFilters) }} filter aktif</span>
                    @else
                        <span class="font-medium">Tidak ada filter aktif</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="resetFilters" class="px-3 py-1.5 text-[11px] font-semibold text-gray-600 dark:text-gray-400
                                   bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600
                                   rounded-lg border border-gray-200 dark:border-gray-600
                                   transition-all duration-150 active:scale-95">
                        Reset
                    </button>
                    <button @click="showAdvancedFilters = false" class="px-4 py-1.5 text-[11px] font-semibold text-white
                                   bg-gradient-to-r from-amber-500 to-orange-500
                                   hover:from-amber-600 hover:to-orange-600
                                   rounded-lg shadow-sm
                                   transition-all duration-150 active:scale-95">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* ── DTF Overrides ── */
            .dtf-form-grid .fi-fo-field-wrp label {
                font-size: .7rem;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: var(--dtf-label, #9ca3af);
            }

            :root {
                --dtf-label: #9ca3af;
            }

            .dark {
                --dtf-label: #6b7280;
            }

            /* Subtle animations */
            @keyframes dtf-ping {

                0%,
                100% {
                    opacity: 0;
                    transform: scale(1);
                }

                50% {
                    opacity: .25;
                    transform: scale(1.08);
                }
            }

            .animate-ping-slow {
                animation: dtf-ping 2.5s ease-in-out infinite;
            }

            /* Mobile view toggle */
            @media (max-width: 640px) {
                .dtf .flex-shrink-0.order-2 {
                    width: 100%;
                    justify-content: space-between;
                }
            }
        </style>
    @endpush
</div>