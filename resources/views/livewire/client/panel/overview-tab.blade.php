<div class="space-y-4 sm:space-y-6" x-data="{ 
        showAllDocuments: false,
        showAllProjects: false,
        mounted: false 
    }" x-init="setTimeout(() => mounted = true, 100)">

    @if($clients->isEmpty())
    {{-- No Client Warning - Responsive --}}
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl border border-amber-200/50 bg-gradient-to-br from-amber-50 via-white to-orange-50 p-5 sm:p-8 shadow-sm dark:border-amber-900/30 dark:from-amber-950/20 dark:via-gray-900 dark:to-orange-950/20"
        x-show="mounted" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100">
        <div class="relative flex flex-col sm:flex-row items-start gap-4 sm:gap-5">
            <div class="flex-shrink-0">
                <div
                    class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl sm:rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-lg shadow-amber-500/25">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 sm:h-7 sm:w-7 text-white" />
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">
                    Akun Belum Terhubung
                </h3>
                <p class="mt-2 text-xs sm:text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                    Akun Anda belum terhubung dengan data klien. Silakan hubungi administrator untuk menghubungkan akun
                    Anda ke data klien yang sesuai.
                </p>
                <div class="mt-4 sm:mt-5">
                    <a href="mailto:admin@example.com"
                        class="group inline-flex items-center gap-2 sm:gap-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2 sm:px-5 sm:py-2.5 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-300 hover:from-amber-600 hover:to-orange-600 hover:shadow-xl hover:-translate-y-0.5">
                        <x-heroicon-o-envelope
                            class="h-4 w-4 transition-transform duration-300 group-hover:scale-110" />
                        Hubungi Administrator
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else

    {{-- Client Selector - Responsive --}}
    @if($clients->count() > 1)
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white/80 p-4 sm:p-5 shadow-sm backdrop-blur-sm dark:border-gray-700/60 dark:bg-gray-800/80"
        x-show="mounted" x-transition:enter="transition ease-out duration-400"
        x-transition:enter-start="opacity-0 transform -translate-y-3"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-3 sm:gap-4">
            <div
                class="flex items-center gap-2 sm:gap-2.5 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">
                <div
                    class="flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-heroicon-o-building-office-2 class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </div>
                <span>Pilih Perusahaan:</span>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                @foreach($clients as $client)
                <button wire:click="selectClient({{ $client->id }})" class="group relative rounded-lg sm:rounded-xl px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium transition-all duration-300
                        {{ $selectedClientId === $client->id 
                            ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25 hover:bg-primary-700' 
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' 
                        }}">
                    {{ $client->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Welcome Message - Responsive --}}
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-gradient-to-br from-white via-gray-50/50 to-primary-50/30 p-4 sm:p-6 shadow-sm dark:border-gray-700/60 dark:from-gray-800 dark:via-gray-800/50 dark:to-primary-900/10"
        x-show="mounted" x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        {{-- Decorative Elements --}}
        <div
            class="absolute right-0 top-0 -mr-20 -mt-20 h-64 w-64 rounded-full bg-gradient-to-br from-primary-400/10 to-transparent blur-3xl">
        </div>

        <div class="relative flex flex-col sm:flex-row items-start justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-3 sm:gap-5 w-full sm:w-auto">
                @if($selectedClient && $selectedClient->logo)
                <div class="relative flex-shrink-0">
                    <img src="{{ Storage::url($selectedClient->logo) }}" alt="{{ $selectedClient->name }}"
                        class="h-12 w-12 sm:h-16 sm:w-16 rounded-xl sm:rounded-2xl object-cover shadow-lg ring-2 ring-white dark:ring-gray-700">
                    <div
                        class="absolute -bottom-1 -right-1 h-3 w-3 sm:h-4 sm:w-4 rounded-full border-2 border-white bg-primary-500 dark:border-gray-800">
                    </div>
                </div>
                @else
                <div
                    class="flex h-12 w-12 sm:h-16 sm:w-16 flex-shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-primary-600 shadow-lg shadow-primary-500/25">
                    <x-heroicon-o-building-office-2 class="h-6 w-6 sm:h-8 sm:w-8 text-white" />
                </div>
                @endif
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg sm:text-2xl font-bold tracking-tight text-gray-900 truncate dark:text-gray-100">
                        {{ $selectedClient ? $selectedClient->name : 'Dashboard Overview' }}
                    </h2>
                    <p
                        class="mt-1 sm:mt-1.5 flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                        Ringkasan aktivitas dan status perusahaan Anda
                    </p>
                </div>
            </div>
            <button wire:click="refresh"
                class="group flex h-9 w-9 sm:h-11 sm:w-11 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-white/80 text-gray-500 shadow-sm ring-1 ring-gray-200/60 backdrop-blur-sm transition-all duration-300 hover:bg-primary-50 hover:text-primary-600 hover:ring-primary-200 dark:bg-gray-700/80 dark:ring-gray-600/60 dark:hover:bg-primary-900/30 dark:hover:text-primary-400"
                title="Refresh Data">
                <x-heroicon-o-arrow-path
                    class="h-4 w-4 sm:h-5 sm:w-5 transition-transform duration-500 group-hover:rotate-180" />
            </button>
        </div>
    </div>

    {{-- Stats Cards - Responsive Grid --}}
    <div class="grid gap-3 sm:gap-4 md:gap-5 grid-cols-2 lg:grid-cols-4">
        {{-- Active Projects Card --}}
        <div class="group relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white p-4 sm:p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1 dark:border-gray-700/60 dark:bg-gray-900"
            x-show="mounted" x-transition:enter="transition ease-out duration-400 delay-100"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            </div>

            <div class="relative flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                        Proyek Aktif
                    </p>
                    <div class="mt-2 sm:mt-3 flex items-baseline gap-1 sm:gap-2">
                        <p class="text-2xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ $projectStats['active'] }}
                        </p>
                        <span class="text-xs sm:text-sm text-gray-400 dark:text-gray-500">/ {{ $projectStats['total']
                            }}</span>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="h-1.5 sm:h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full bg-primary-600 transition-all duration-700 ease-out dark:bg-primary-500"
                                style="width: {{ $projectStats['total'] > 0 ? round(($projectStats['active'] / $projectStats['total']) * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-primary-600 shadow-lg shadow-primary-500/25 transition-transform duration-300 group-hover:scale-110 dark:bg-primary-500">
                    <x-heroicon-o-folder class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                </div>
            </div>
        </div>

        {{-- Completed Projects Card --}}
        <div class="group relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white p-4 sm:p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1 dark:border-gray-700/60 dark:bg-gray-900"
            x-show="mounted" x-transition:enter="transition ease-out duration-400 delay-150"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            </div>

            <div class="relative flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                        Proyek Selesai
                    </p>
                    <div class="mt-2 sm:mt-3 flex items-baseline gap-1 sm:gap-2">
                        <p class="text-2xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ $projectStats['completed'] }}
                        </p>
                        <span
                            class="rounded-md bg-amber-100 px-1 sm:px-1.5 py-0.5 text-[10px] sm:text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            {{ $projectStats['pending'] }} pending
                        </span>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="h-1.5 sm:h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full bg-primary-600 transition-all duration-700 ease-out dark:bg-primary-500"
                                style="width: {{ $projectStats['total'] > 0 ? round(($projectStats['completed'] / $projectStats['total']) * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-primary-600 shadow-lg shadow-primary-500/25 transition-transform duration-300 group-hover:scale-110 dark:bg-primary-500">
                    <x-heroicon-o-check-circle class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                </div>
            </div>
        </div>

        {{-- Tax Reports Card --}}
        <div class="group relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white p-4 sm:p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1 dark:border-gray-700/60 dark:bg-gray-900"
            x-show="mounted" x-transition:enter="transition ease-out duration-400 delay-200"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            </div>

            <div class="relative flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                        Laporan Dilaporkan
                    </p>
                    <div class="mt-2 sm:mt-3 flex items-baseline gap-1 sm:gap-2">
                        <p class="text-2xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ $taxReportStats['reported'] }}
                        </p>
                        <span class="text-xs sm:text-sm font-semibold text-primary-600 dark:text-primary-400">
                            {{ $taxReportStats['completion_percentage'] }}%
                        </span>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="h-1.5 sm:h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full bg-primary-600 transition-all duration-700 ease-out dark:bg-primary-500"
                                style="width: {{ $taxReportStats['completion_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
                <div
                    class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-primary-600 shadow-lg shadow-primary-500/25 transition-transform duration-300 group-hover:scale-110 dark:bg-primary-500">
                    <x-heroicon-o-document-chart-bar class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                </div>
            </div>
        </div>

        {{-- Documents Card --}}
        <div class="group relative overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white p-4 sm:p-6 shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1 dark:border-gray-700/60 dark:bg-gray-900"
            x-show="mounted" x-transition:enter="transition ease-out duration-400 delay-250"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            </div>

            <div class="relative flex items-start justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-500 truncate dark:text-gray-400">
                        Total Dokumen
                    </p>
                    <div class="mt-2 sm:mt-3 flex items-baseline gap-1 sm:gap-2">
                        <p class="text-2xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                            {{ $documentStats['total'] }}
                        </p>
                    </div>
                    <div class="mt-3 sm:mt-4 flex items-center gap-2 sm:gap-3 flex-wrap">
                        <span
                            class="inline-flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs font-medium text-gray-600 dark:text-gray-400">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-primary-500"></span>
                            {{ $documentStats['valid'] }} valid
                        </span>
                        @if($documentStats['expired'] > 0)
                        <span
                            class="inline-flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-xs font-medium text-gray-600 dark:text-gray-400">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-red-500"></span>
                            {{ $documentStats['expired'] }} expired
                        </span>
                        @endif
                    </div>
                </div>
                <div
                    class="flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-xl sm:rounded-2xl bg-primary-600 shadow-lg shadow-primary-500/25 transition-transform duration-300 group-hover:scale-110 dark:bg-primary-500">
                    <x-heroicon-o-document-text class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
                </div>
            </div>
        </div>
    </div>

    {{-- Required Documents Alert Section - Responsive --}}
    @if($pendingDocuments->count() > 0)
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl border-2 border-amber-300 bg-gradient-to-br from-amber-50 via-amber-50/50 to-orange-50 p-4 sm:p-6 shadow-md dark:border-amber-600/50 dark:from-amber-950/30 dark:via-amber-900/20 dark:to-orange-950/20"
        x-show="mounted" x-transition:enter="transition ease-out duration-500 delay-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100">
        {{-- Animated Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0"
                style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, currentColor 10px, currentColor 11px);">
            </div>
        </div>
        <div class="relative">
            <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                <div class="flex-shrink-0">
                    <div
                        class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-xl sm:rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg shadow-amber-500/30">
                        <x-heroicon-o-arrow-up-tray class="h-6 w-6 sm:h-7 sm:w-7 text-white" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <h3 class="text-lg sm:text-xl font-bold text-amber-900 dark:text-amber-100">
                            Dokumen Perlu Diupload
                        </h3>
                        <span
                            class="inline-flex items-center justify-center rounded-full bg-amber-500 px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs sm:text-sm font-bold text-white shadow-sm">
                            {{ $pendingDocuments->count() }}
                        </span>
                    </div>
                    <p class="mt-1 sm:mt-1.5 text-xs sm:text-sm text-amber-700 dark:text-amber-300">
                        Anda memiliki dokumen yang perlu segera diupload untuk melengkapi persyaratan
                    </p>
                </div>
            </div>

            <div class="mt-4 sm:mt-5 grid gap-2 sm:gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($pendingDocuments->take(6) as $doc)
                <div
                    class="group flex items-center gap-2 sm:gap-3 rounded-lg sm:rounded-xl bg-white/80 p-3 sm:p-4 shadow-sm ring-1 ring-amber-200/60 transition-all duration-200 hover:bg-white hover:shadow-md hover:ring-amber-300 dark:bg-gray-800/80 dark:ring-amber-700/40 dark:hover:bg-gray-800 dark:hover:ring-amber-600">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl 
                        {{ $doc['type'] === 'requirement' 
                            ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' 
                            : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' 
                        }}">
                        @if($doc['type'] === 'requirement')
                        <x-heroicon-o-exclamation-triangle class="h-4 w-4 sm:h-5 sm:w-5" />
                        @else
                        <x-heroicon-o-document-plus class="h-4 w-4 sm:h-5 sm:w-5" />
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $doc['name'] }}
                        </p>
                        <div class="mt-0.5 flex flex-wrap items-center gap-1 sm:gap-2">
                            @if($doc['type'] === 'requirement')
                            <span class="text-[10px] sm:text-xs font-medium text-red-600 dark:text-red-400">
                                Diminta Admin
                            </span>
                            @else
                            <span class="text-[10px] sm:text-xs text-amber-600 dark:text-amber-400">
                                {{ $doc['category'] ?? 'Dokumen Legal' }}
                            </span>
                            @endif

                            @if($doc['is_required'])
                            <span
                                class="rounded bg-amber-100 px-1 sm:px-1.5 py-0.5 text-[9px] sm:text-[10px] font-bold text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                                WAJIB
                            </span>
                            @endif
                        </div>

                        @if($doc['due_date'])
                        <p class="mt-1 flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] 
                            {{ \Carbon\Carbon::parse($doc['due_date'])->isPast() 
                                ? 'font-semibold text-red-600 dark:text-red-400' 
                                : 'text-gray-500 dark:text-gray-400' 
                            }}">
                            <x-heroicon-o-clock class="h-3 w-3" />
                            Tenggat: {{ \Carbon\Carbon::parse($doc['due_date'])->format('d M Y') }}
                            @if(\Carbon\Carbon::parse($doc['due_date'])->isPast())
                            <span
                                class="ml-0.5 sm:ml-1 rounded bg-red-100 px-1 text-[9px] sm:text-[10px] font-bold text-red-700 dark:bg-red-900/50 dark:text-red-400">
                                TERLAMBAT
                            </span>
                            @endif
                        </p>
                        @endif
                    </div>
                    <x-heroicon-o-chevron-right
                        class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0 text-amber-400 transition-transform duration-200 group-hover:translate-x-1 dark:text-amber-500" />
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Two Column Layout - Responsive --}}
    <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
        {{-- Active Projects Section - Responsive --}}
        <div class="flex flex-col overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900"
            x-show="mounted" x-transition:enter="transition ease-out duration-500 delay-300"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Header --}}
            <div
                class="flex-shrink-0 border-b border-gray-200/60 bg-gradient-to-r from-gray-50 to-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700/50 dark:from-gray-800/80 dark:to-gray-900">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                        <div
                            class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-primary-600 shadow-md shadow-primary-500/20 dark:bg-primary-500 dark:shadow-primary-500/10">
                            <x-heroicon-o-folder class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate dark:text-gray-50">
                                Proyek
                            </h3>
                            <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                                {{ $projects->count() }} total proyek
                            </p>
                        </div>
                    </div>

                    {{-- Status Legend - Hidden on mobile --}}
                    <div class="hidden lg:flex items-center gap-3 xl:gap-4">
                        <div class="flex items-center gap-1.5" title="Sedang Dikerjakan">
                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Progress</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Dalam Review">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Review</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Sedang Dianalisis">
                            <span class="h-2 w-2 rounded-full bg-purple-500"></span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Analisis</span>
                        </div>
                        <div class="flex items-center gap-1.5" title="Selesai">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Projects List - Scrollable with Mobile Optimization --}}
            <div class="flex-1 divide-y divide-gray-100 overflow-y-auto dark:divide-gray-800"
                style="max-height: 420px;">
                @forelse($projects as $project)
                @php
                $statusConfig = [
                'draft' => [
                'bg' => 'bg-gray-50/80 hover:bg-gray-100 dark:bg-gray-800/60 dark:hover:bg-gray-800',
                'border' => 'border-l-4 border-l-gray-400 dark:border-l-gray-500',
                'icon_bg' => 'bg-gray-200 dark:bg-gray-700',
                'icon_color' => 'text-gray-500 dark:text-gray-400',
                'badge_bg' => 'bg-gray-200 dark:bg-gray-700',
                'badge_text' => 'text-gray-700 dark:text-gray-300',
                'dot' => 'bg-gray-400 dark:bg-gray-500',
                'label' => 'Draft',
                'description' => 'Proyek belum dimulai dan masih dalam tahap persiapan',
                ],
                'analysis' => [
                'bg' => 'bg-purple-50/60 hover:bg-purple-100/60 dark:bg-purple-950/40 dark:hover:bg-purple-950/60',
                'border' => 'border-l-4 border-l-purple-500 dark:border-l-purple-400',
                'icon_bg' => 'bg-purple-200 dark:bg-purple-900/60',
                'icon_color' => 'text-purple-600 dark:text-purple-400',
                'badge_bg' => 'bg-purple-200 dark:bg-purple-900/60',
                'badge_text' => 'text-purple-700 dark:text-purple-300',
                'dot' => 'bg-purple-500 dark:bg-purple-400',
                'label' => 'Analisis',
                'description' => 'Tim sedang menganalisis kebutuhan dan menyusun rencana kerja',
                ],
                'in_progress' => [
                'bg' => 'bg-blue-50/60 hover:bg-blue-100/60 dark:bg-blue-950/40 dark:hover:bg-blue-950/60',
                'border' => 'border-l-4 border-l-blue-500 dark:border-l-blue-400',
                'icon_bg' => 'bg-blue-200 dark:bg-blue-900/60',
                'icon_color' => 'text-blue-600 dark:text-blue-400',
                'badge_bg' => 'bg-blue-200 dark:bg-blue-900/60',
                'badge_text' => 'text-blue-700 dark:text-blue-300',
                'dot' => 'bg-blue-500 dark:bg-blue-400 animate-pulse',
                'label' => 'Dikerjakan',
                'description' => 'Proyek sedang dalam pengerjaan aktif oleh tim',
                ],
                'review' => [
                'bg' => 'bg-amber-50/60 hover:bg-amber-100/60 dark:bg-amber-950/40 dark:hover:bg-amber-950/60',
                'border' => 'border-l-4 border-l-amber-500 dark:border-l-amber-400',
                'icon_bg' => 'bg-amber-200 dark:bg-amber-900/60',
                'icon_color' => 'text-amber-600 dark:text-amber-400',
                'badge_bg' => 'bg-amber-200 dark:bg-amber-900/60',
                'badge_text' => 'text-amber-700 dark:text-amber-300',
                'dot' => 'bg-amber-500 dark:bg-amber-400',
                'label' => 'Review',
                'description' => 'Proyek sedang direview dan menunggu persetujuan',
                ],
                'completed' => [
                'bg' => 'bg-emerald-50/60 hover:bg-emerald-100/60 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/60',
                'border' => 'border-l-4 border-l-emerald-500 dark:border-l-emerald-400',
                'icon_bg' => 'bg-emerald-200 dark:bg-emerald-900/60',
                'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                'badge_bg' => 'bg-emerald-200 dark:bg-emerald-900/60',
                'badge_text' => 'text-emerald-700 dark:text-emerald-300',
                'dot' => 'bg-emerald-500 dark:bg-emerald-400',
                'label' => 'Selesai',
                'description' => 'Proyek telah selesai dikerjakan',
                ],
                'completed (Not Payed Yet)' => [
                'bg' => 'bg-emerald-50/60 hover:bg-emerald-100/60 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/60',
                'border' => 'border-l-4 border-l-emerald-500 dark:border-l-emerald-400',
                'icon_bg' => 'bg-emerald-200 dark:bg-emerald-900/60',
                'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                'badge_bg' => 'bg-emerald-200 dark:bg-emerald-900/60',
                'badge_text' => 'text-emerald-700 dark:text-emerald-300',
                'dot' => 'bg-emerald-500 dark:bg-emerald-400',
                'label' => 'Selesai',
                'description' => 'Proyek telah selesai dikerjakan',
                ],
                'on_hold' => [
                'bg' => 'bg-orange-50/60 hover:bg-orange-100/60 dark:bg-orange-950/40 dark:hover:bg-orange-950/60',
                'border' => 'border-l-4 border-l-orange-500 dark:border-l-orange-400',
                'icon_bg' => 'bg-orange-200 dark:bg-orange-900/60',
                'icon_color' => 'text-orange-600 dark:text-orange-400',
                'badge_bg' => 'bg-orange-200 dark:bg-orange-900/60',
                'badge_text' => 'text-orange-700 dark:text-orange-300',
                'dot' => 'bg-orange-500 dark:bg-orange-400',
                'label' => 'Ditunda',
                'description' => 'Proyek sementara ditunda',
                ],
                'canceled' => [
                'bg' => 'bg-red-50/60 hover:bg-red-100/60 dark:bg-red-950/40 dark:hover:bg-red-950/60',
                'border' => 'border-l-4 border-l-red-500 dark:border-l-red-400',
                'icon_bg' => 'bg-red-200 dark:bg-red-900/60',
                'icon_color' => 'text-red-600 dark:text-red-400',
                'badge_bg' => 'bg-red-200 dark:bg-red-900/60',
                'badge_text' => 'text-red-700 dark:text-red-300',
                'dot' => 'bg-red-500 dark:bg-red-400',
                'label' => 'Dibatalkan',
                'description' => 'Proyek telah dibatalkan',
                ],
                ];

                $config = $statusConfig[$project->status] ?? $statusConfig['draft'];
                $daysUntilDue = $project->due_date ? now()->diffInDays($project->due_date, false) : null;
                @endphp

                <div class="group relative {{ $config['bg'] }} {{ $config['border'] }} px-3 py-3 sm:px-5 sm:py-4 transition-all duration-200"
                    x-data="{ showTooltip: false }" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">

                    {{-- Status Tooltip - Hidden on Mobile --}}
                    <div x-show="showTooltip" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-1"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute left-3 sm:left-5 top-0 z-20 -translate-y-full hidden lg:block" x-cloak>
                        <div
                            class="mb-2 rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-xl dark:bg-gray-950 dark:ring-1 dark:ring-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full {{ $config['dot'] }}"></span>
                                <span class="font-semibold">{{ $config['label'] }}</span>
                            </div>
                            <p class="mt-1 max-w-[220px] leading-relaxed text-gray-300">
                                {{ $config['description'] }}
                            </p>
                            <div class="absolute -bottom-1 left-4 h-2 w-2 rotate-45 bg-gray-900 dark:bg-gray-950"></div>
                        </div>
                    </div>

                    {{-- Card Content - Mobile Optimized --}}
                    <div class="flex items-start gap-2 sm:gap-4">
                        {{-- Left: Icon --}}
                        <div class="flex-shrink-0">
                            <div
                                class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg sm:rounded-xl {{ $config['icon_bg'] }} transition-transform duration-200 group-hover:scale-105">
                                @switch($project->status)
                                @case('completed')
                                @case('completed (Not Payed Yet)')
                                <x-heroicon-s-check-circle class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @break
                                @case('in_progress')
                                <x-heroicon-s-play class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @break
                                @case('review')
                                <x-heroicon-s-eye class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @break
                                @case('analysis')
                                <x-heroicon-s-magnifying-glass
                                    class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @break
                                @case('canceled')
                                <x-heroicon-s-x-circle class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @break
                                @case('on_hold')
                                <x-heroicon-s-pause class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @break
                                @default
                                <x-heroicon-s-document-text class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @endswitch
                            </div>
                        </div>

                        {{-- Middle: Content --}}
                        <div class="min-w-0 flex-1">
                            {{-- Row 1: Title + Priority --}}
                            <div class="flex items-start gap-1 sm:gap-2">
                                <h4
                                    class="flex-1 truncate text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-50">
                                    {{ $project->name }}
                                </h4>
                                @if($project->priority === 'urgent')
                                <span
                                    class="inline-flex flex-shrink-0 items-center gap-0.5 sm:gap-1 rounded bg-red-100 px-1 sm:px-1.5 py-0.5 text-[9px] sm:text-[10px] font-bold uppercase text-red-700 dark:bg-red-900/50 dark:text-red-300">
                                    <x-heroicon-s-bolt class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                                    Urgent
                                </span>
                                @endif
                            </div>

                            {{-- Row 2: Status Badge + PIC --}}
                            <div class="mt-1.5 sm:mt-2 flex flex-wrap items-center gap-2 sm:gap-3">
                                <span
                                    class="inline-flex items-center gap-1 sm:gap-1.5 rounded-md {{ $config['badge_bg'] }} px-1.5 py-0.5 sm:px-2 sm:py-1 text-[10px] sm:text-[11px] font-semibold {{ $config['badge_text'] }}">
                                    <span class="h-1 w-1 sm:h-1.5 sm:w-1.5 rounded-full {{ $config['dot'] }}"></span>
                                    {{ $config['label'] }}
                                </span>

                                @if($project->pic)
                                <span
                                    class="flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] text-gray-500 dark:text-gray-400">
                                    <x-heroicon-o-user-circle class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                                    <span class="hidden sm:inline">{{ $project->pic->name }}</span>
                                    <span class="inline sm:hidden">{{ Str::limit($project->pic->name, 10) }}</span>
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Due Date --}}
                        @if($project->due_date)
                        <div class="flex-shrink-0 text-right">
                            <p class="text-[10px] sm:text-xs font-medium text-gray-900 dark:text-gray-100">
                                {{ $project->due_date->format('d M') }}
                            </p>
                            @if($daysUntilDue !== null)
                            @if($daysUntilDue < 0) <p
                                class="mt-0.5 sm:mt-1 text-[9px] sm:text-[11px] font-semibold text-red-600 dark:text-red-400">
                                <span class="hidden sm:inline">Terlambat</span> {{ abs($daysUntilDue) }}h
                                </p>
                                @elseif($daysUntilDue <= 7) <p
                                    class="mt-0.5 sm:mt-1 text-[9px] sm:text-[11px] font-medium text-amber-600 dark:text-amber-400">
                                    {{ $daysUntilDue }}h<span class="hidden sm:inline"> lagi</span>
                                    </p>
                                    @else
                                    <p
                                        class="mt-0.5 sm:mt-1 text-[9px] sm:text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $daysUntilDue }}h<span class="hidden sm:inline"> lagi</span>
                                    </p>
                                    @endif
                                    @endif
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                {{-- Empty State --}}
                <div class="p-8 sm:p-12 text-center">
                    <div
                        class="mx-auto flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-xl sm:rounded-2xl bg-gray-100 dark:bg-gray-800">
                        <x-heroicon-o-folder-open class="h-7 w-7 sm:h-8 sm:w-8 text-gray-400 dark:text-gray-500" />
                    </div>
                    <p class="mt-3 sm:mt-4 text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Tidak ada proyek
                    </p>
                    <p class="mt-1 text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                        Proyek akan muncul di sini saat ada pekerjaan baru
                    </p>
                </div>
                @endforelse
            </div>

            {{-- Footer - Sticky at Bottom --}}
            <div
                class="flex-shrink-0 border-t border-gray-200/60 bg-gray-50 px-4 py-2.5 sm:px-6 sm:py-3 dark:border-gray-700/50 dark:bg-gray-800/80">
                <div class="flex items-center justify-between gap-2">
                    {{-- Quick Stats --}}
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-[10px] sm:text-[11px]">
                        @php
                        $inProgress = $projects->whereIn('status', ['in_progress'])->count();
                        $inReview = $projects->whereIn('status', ['review'])->count();
                        $completed = $projects->whereIn('status', ['completed', 'completed (Not Payed Yet)'])->count();
                        @endphp
                        @if($inProgress > 0)
                        <span class="flex items-center gap-1 sm:gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-blue-500"></span>
                            <span class="hidden xs:inline">{{ $inProgress }} dikerjakan</span>
                            <span class="inline xs:hidden">{{ $inProgress }}</span>
                        </span>
                        @endif
                        @if($inReview > 0)
                        <span class="flex items-center gap-1 sm:gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-amber-500"></span>
                            <span class="hidden xs:inline">{{ $inReview }} review</span>
                            <span class="inline xs:hidden">{{ $inReview }}</span>
                        </span>
                        @endif
                        @if($completed > 0)
                        <span class="flex items-center gap-1 sm:gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-emerald-500"></span>
                            <span class="hidden xs:inline">{{ $completed }} selesai</span>
                            <span class="inline xs:hidden">{{ $completed }}</span>
                        </span>
                        @endif
                    </div>

                    {{-- View All Link --}}
                    <a href="" wire:navigate
                        class="inline-flex items-center gap-1 sm:gap-1.5 rounded-lg bg-primary-600 px-2.5 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-primary-700 hover:shadow dark:bg-primary-500 dark:hover:bg-primary-600">
                        <span class="hidden sm:inline">Lihat Semua</span>
                        <span class="inline sm:hidden">Semua</span>
                        <x-heroicon-o-arrow-right class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                    </a>
                </div>
            </div>
        </div>

        {{-- Document Widget - Mobile Optimized (continuing in next message due to length) --}}
        <div class="flex flex-col overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200/60 bg-white shadow-sm dark:border-gray-700/50 dark:bg-gray-900"
            x-data="{ activeSection: 'pending' }" x-show="mounted"
            x-transition:enter="transition ease-out duration-500 delay-350"
            x-transition:enter-start="opacity-0 transform translate-y-6"
            x-transition:enter-end="opacity-100 transform translate-y-0">

            {{-- Header with Tabs - Responsive --}}
            <div
                class="flex-shrink-0 border-b border-gray-200/60 bg-gradient-to-r from-gray-50 to-white px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-700/50 dark:from-gray-800/80 dark:to-gray-900">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <div
                            class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-primary-600 shadow-md shadow-primary-500/20 dark:bg-primary-500 dark:shadow-primary-500/10">
                            <x-heroicon-o-document-text class="h-4 w-4 sm:h-5 sm:w-5 text-white" />
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900 truncate dark:text-gray-50">
                                Dokumen
                            </h3>
                            <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                                Kelola dokumen perusahaan
                            </p>
                        </div>
                    </div>

                    {{-- Tab Switcher - Responsive --}}
                    <div
                        class="flex items-center gap-0.5 sm:gap-1 rounded-lg bg-gray-100 p-0.5 sm:p-1 w-full sm:w-auto dark:bg-gray-800">
                        <button @click="activeSection = 'pending'" :class="activeSection === 'pending' 
                        ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-gray-50' 
                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="relative flex-1 sm:flex-none rounded-md px-2 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-medium transition-all duration-200">
                            <span class="hidden sm:inline">Perlu Upload</span>
                            <span class="inline sm:hidden">Upload</span>
                            @if($pendingDocuments->count() > 0)
                            <span
                                class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] sm:text-[10px] font-bold text-white">
                                {{ $pendingDocuments->count() }}
                            </span>
                            @endif
                        </button>
                        <button @click="activeSection = 'uploaded'" :class="activeSection === 'uploaded' 
                        ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-700 dark:text-gray-50' 
                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                            class="flex-1 sm:flex-none rounded-md px-2 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-medium transition-all duration-200">
                            Terupload
                        </button>
                    </div>
                </div>
            </div>

            {{-- Progress Bar - Responsive --}}
            @php
            $totalRequired = $allDocumentsChecklist->where('is_required', true)->count();
            $uploadedRequired = $allDocumentsChecklist->where('is_required', true)->where('is_uploaded', true)->count();
            $percentage = $totalRequired > 0 ? round(($uploadedRequired / $totalRequired) * 100) : 100;
            @endphp
            <div
                class="flex-shrink-0 border-b border-gray-100 bg-gray-50/50 px-4 py-2 sm:px-6 sm:py-3 dark:border-gray-800 dark:bg-gray-800/50">
                <div class="flex items-center justify-between text-[10px] sm:text-xs">
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        <span class="hidden sm:inline">Kelengkapan Dokumen Wajib</span>
                        <span class="inline sm:hidden">Dokumen Wajib</span>
                    </span>
                    <span
                        class="font-bold {{ $percentage === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-900 dark:text-gray-100' }}">
                        {{ $uploadedRequired }}/{{ $totalRequired }} ({{ $percentage }}%)
                    </span>
                </div>
                <div class="mt-1.5 sm:mt-2 h-1.5 sm:h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full rounded-full transition-all duration-500 {{ $percentage === 100 ? 'bg-emerald-500' : 'bg-primary-600 dark:bg-primary-500' }}"
                        style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            {{-- Content Area - Mobile Optimized --}}
            <div class="flex-1 overflow-y-auto" style="max-height: 420px;">

                {{-- Pending Documents Section - Responsive --}}
                <div x-show="activeSection === 'pending'" x-cloak>
                    @if($pendingDocuments->count() > 0)
                    @php
                    // Separate by type
                    $sopDocs = $pendingDocuments->where('type', 'sop_legal');
                    $reqDocs = $pendingDocuments->where('type', 'requirement');
                    @endphp

                    {{-- Admin Requirements (Priority) --}}
                    @if($reqDocs->count() > 0)
                    <div class="border-b border-gray-100 dark:border-gray-800">
                        <div
                            class="sticky top-0 z-10 bg-red-50/90 px-4 py-2 sm:px-6 sm:py-2.5 backdrop-blur-sm dark:bg-red-950/60">
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <x-heroicon-o-exclamation-triangle
                                    class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-red-600 dark:text-red-400" />
                                <span
                                    class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-red-700 dark:text-red-400">
                                    Diminta oleh Admin
                                </span>
                                <span
                                    class="rounded-full bg-red-200 px-1.5 py-0.5 sm:px-2 text-[9px] sm:text-[10px] font-bold text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                    {{ $reqDocs->count() }}
                                </span>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($reqDocs as $doc)
                            <div
                                class="group flex items-center gap-2 sm:gap-4 bg-red-50/30 px-4 py-3 sm:px-6 sm:py-4 transition-colors hover:bg-red-50 dark:bg-red-950/20 dark:hover:bg-red-950/40">
                                {{-- Icon --}}
                                <div
                                    class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-red-100 dark:bg-red-900/50">
                                    <x-heroicon-o-arrow-up-tray
                                        class="h-4 w-4 sm:h-5 sm:w-5 text-red-600 dark:text-red-400" />
                                </div>

                                {{-- Content --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1 sm:gap-2">
                                        <h4
                                            class="truncate text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $doc['name'] }}
                                        </h4>
                                        @if($doc['is_required'])
                                        <span
                                            class="flex-shrink-0 rounded bg-red-100 px-1 sm:px-1.5 py-0.5 text-[9px] sm:text-[10px] font-bold uppercase text-red-700 dark:bg-red-900/50 dark:text-red-400">
                                            Wajib
                                        </span>
                                        @endif
                                    </div>
                                    @if($doc['description'])
                                    <p class="mt-0.5 truncate text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                                        {{ $doc['description'] }}
                                    </p>
                                    @endif
                                    @if($doc['due_date'])
                                    <p
                                        class="mt-0.5 sm:mt-1 flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] {{ \Carbon\Carbon::parse($doc['due_date'])->isPast() ? 'font-semibold text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                                        <x-heroicon-o-clock class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                                        <span class="hidden sm:inline">Tenggat: {{
                                            \Carbon\Carbon::parse($doc['due_date'])->format('d M Y') }}</span>
                                        <span class="inline sm:hidden">{{
                                            \Carbon\Carbon::parse($doc['due_date'])->format('d/m') }}</span>
                                        @if(\Carbon\Carbon::parse($doc['due_date'])->isPast())
                                        <span
                                            class="ml-0.5 sm:ml-1 rounded bg-red-100 px-1 text-[9px] sm:text-[10px] font-bold text-red-700 dark:bg-red-900/50 dark:text-red-400">TERLAMBAT</span>
                                        @endif
                                    </p>
                                    @endif
                                </div>

                                {{-- Upload Button --}}
                                <a href=""
                                    class="flex-shrink-0 rounded-lg bg-red-600 px-2.5 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">
                                    <span class="hidden sm:inline">Upload</span>
                                    <span class="inline sm:hidden">+</span>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- SOP Legal Documents --}}
                    @if($sopDocs->count() > 0)
                    @php
                    $groupedByCategory = $sopDocs->groupBy('category');
                    @endphp

                    @foreach($groupedByCategory as $category => $docs)
                    <div class="border-b border-gray-100 last:border-b-0 dark:border-gray-800">
                        <div
                            class="sticky top-0 z-10 bg-amber-50/90 px-4 py-1.5 sm:px-6 sm:py-2 backdrop-blur-sm dark:bg-amber-950/60">
                            <span
                                class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                                {{ $category ?: 'Dokumen Legal' }}
                            </span>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($docs as $doc)
                            <div
                                class="group flex items-center gap-2 sm:gap-4 px-4 py-3 sm:px-6 sm:py-4 transition-colors hover:bg-amber-50/50 dark:hover:bg-amber-950/30">
                                {{-- Icon --}}
                                <div
                                    class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl {{ $doc['is_required'] ? 'bg-amber-100 dark:bg-amber-900/50' : 'bg-gray-100 dark:bg-gray-800' }}">
                                    <x-heroicon-o-arrow-up-tray
                                        class="h-4 w-4 sm:h-5 sm:w-5 {{ $doc['is_required'] ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}" />
                                </div>

                                {{-- Content --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1 sm:gap-2">
                                        <h4
                                            class="truncate text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $doc['name'] }}
                                        </h4>
                                        @if($doc['is_required'])
                                        <span
                                            class="flex-shrink-0 rounded bg-amber-100 px-1 sm:px-1.5 py-0.5 text-[9px] sm:text-[10px] font-bold uppercase text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                                            Wajib
                                        </span>
                                        @endif
                                    </div>
                                    @if($doc['description'])
                                    <p class="mt-0.5 truncate text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                                        {{ $doc['description'] }}
                                    </p>
                                    @endif
                                </div>

                                {{-- Upload Button --}}
                                <a href=""
                                    class="flex-shrink-0 rounded-lg bg-amber-500 px-2.5 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-semibold text-white opacity-0 shadow-sm transition-all duration-200 group-hover:opacity-100 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-500">
                                    <span class="hidden sm:inline">Upload</span>
                                    <span class="inline sm:hidden">+</span>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @endif

                    @else
                    {{-- All Documents Uploaded --}}
                    <div class="flex flex-col items-center justify-center p-8 sm:p-12 text-center">
                        <div
                            class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-xl sm:rounded-2xl bg-emerald-100 dark:bg-emerald-900/30">
                            <x-heroicon-o-check-circle
                                class="h-7 w-7 sm:h-8 sm:w-8 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <p class="mt-3 sm:mt-4 text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Semua Dokumen Lengkap
                        </p>
                        <p class="mt-1 text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                            Semua dokumen wajib sudah diupload
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Uploaded Documents Section - Responsive --}}
                <div x-show="activeSection === 'uploaded'" x-cloak>
                    @php
                    $uploaded = $allDocumentsChecklist->where('is_uploaded', true);
                    @endphp

                    @if($uploaded->count() > 0)
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($uploaded as $doc)
                        @php
                        $statusConfig = [
                        'valid' => [
                        'bg' => 'bg-emerald-50/50 dark:bg-emerald-950/30',
                        'icon_bg' => 'bg-emerald-100 dark:bg-emerald-900/50',
                        'icon_color' => 'text-emerald-600 dark:text-emerald-400',
                        'badge_bg' => 'bg-emerald-100 dark:bg-emerald-900/50',
                        'badge_text' => 'text-emerald-700 dark:text-emerald-400',
                        'label' => 'Valid',
                        ],
                        'pending_review' => [
                        'bg' => 'bg-amber-50/30 dark:bg-amber-950/20',
                        'icon_bg' => 'bg-amber-100 dark:bg-amber-900/50',
                        'icon_color' => 'text-amber-600 dark:text-amber-400',
                        'badge_bg' => 'bg-amber-100 dark:bg-amber-900/50',
                        'badge_text' => 'text-amber-700 dark:text-amber-400',
                        'label' => 'Review',
                        ],
                        'expired' => [
                        'bg' => 'bg-red-50/30 dark:bg-red-950/20',
                        'icon_bg' => 'bg-red-100 dark:bg-red-900/50',
                        'icon_color' => 'text-red-600 dark:text-red-400',
                        'badge_bg' => 'bg-red-100 dark:bg-red-900/50',
                        'badge_text' => 'text-red-700 dark:text-red-400',
                        'label' => 'Expired',
                        ],
                        'rejected' => [
                        'bg' => 'bg-red-50/30 dark:bg-red-950/20',
                        'icon_bg' => 'bg-red-100 dark:bg-red-900/50',
                        'icon_color' => 'text-red-600 dark:text-red-400',
                        'badge_bg' => 'bg-red-100 dark:bg-red-900/50',
                        'badge_text' => 'text-red-700 dark:text-red-400',
                        'label' => 'Ditolak',
                        ],
                        ];
                        $config = $statusConfig[$doc['status']] ?? $statusConfig['pending_review'];
                        @endphp

                        <div
                            class="group flex items-center gap-2 sm:gap-4 px-4 py-3 sm:px-6 sm:py-4 {{ $config['bg'] }} transition-colors">
                            {{-- Icon --}}
                            <div
                                class="flex h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 items-center justify-center rounded-lg sm:rounded-xl {{ $config['icon_bg'] }}">
                                @if($doc['status'] === 'valid')
                                <x-heroicon-s-check-circle class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @elseif($doc['status'] === 'pending_review')
                                <x-heroicon-s-clock class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @elseif($doc['status'] === 'expired')
                                <x-heroicon-s-exclamation-circle
                                    class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @elseif($doc['status'] === 'rejected')
                                <x-heroicon-s-x-circle class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @else
                                <x-heroicon-s-document class="h-4 w-4 sm:h-5 sm:w-5 {{ $config['icon_color'] }}" />
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1 sm:gap-2">
                                    <h4
                                        class="truncate text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $doc['name'] }}
                                    </h4>
                                    @if($doc['type'] === 'requirement')
                                    <span
                                        class="flex-shrink-0 rounded bg-gray-200 px-1 sm:px-1.5 py-0.5 text-[9px] sm:text-[10px] font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        Req
                                    </span>
                                    @endif
                                </div>
                                <div
                                    class="mt-0.5 sm:mt-1 flex flex-wrap items-center gap-2 sm:gap-3 text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                                    @if($doc['uploaded_at'])
                                    <span class="flex items-center gap-0.5 sm:gap-1">
                                        <x-heroicon-o-clock class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                                        <span class="hidden sm:inline">{{ $doc['uploaded_at']->diffForHumans() }}</span>
                                        <span class="inline sm:hidden">{{ $doc['uploaded_at']->format('d/m') }}</span>
                                    </span>
                                    @endif
                                    @if($doc['uploaded_by'])
                                    <span class="flex items-center gap-0.5 sm:gap-1 truncate">
                                        <x-heroicon-o-user class="h-2.5 w-2.5 sm:h-3 sm:w-3 flex-shrink-0" />
                                        <span class="truncate hidden sm:inline">{{ $doc['uploaded_by']->name }}</span>
                                        <span class="inline sm:hidden">{{ Str::limit($doc['uploaded_by']->name, 10)
                                            }}</span>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Status & Actions --}}
                            <div class="flex flex-shrink-0 items-center gap-1 sm:gap-2">
                                <span
                                    class="rounded-md {{ $config['badge_bg'] }} px-1.5 py-0.5 sm:px-2 sm:py-1 text-[10px] sm:text-[11px] font-semibold {{ $config['badge_text'] }}">
                                    {{ $config['label'] }}
                                </span>

                                @if($doc['file_path'] && $doc['uploaded_document'])
                                <button wire:click="downloadDocument({{ $doc['uploaded_document']->id }})"
                                    class="rounded-lg bg-gray-100 p-1 sm:p-1.5 text-gray-600 opacity-0 transition-all duration-200 group-hover:opacity-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    {{-- No Documents Uploaded --}}
                    <div class="flex flex-col items-center justify-center p-8 sm:p-12 text-center">
                        <div
                            class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-xl sm:rounded-2xl bg-gray-100 dark:bg-gray-800">
                            <x-heroicon-o-document-text
                                class="h-7 w-7 sm:h-8 sm:w-8 text-gray-400 dark:text-gray-500" />
                        </div>
                        <p class="mt-3 sm:mt-4 text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Belum Ada Dokumen
                        </p>
                        <p class="mt-1 text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                            Upload dokumen untuk memulai
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer - Responsive --}}
            <div
                class="flex-shrink-0 border-t border-gray-200/60 bg-gray-50 px-4 py-2.5 sm:px-6 sm:py-3 dark:border-gray-700/50 dark:bg-gray-800/80">
                <div class="flex items-center justify-between gap-2">
                    {{-- Quick Stats --}}
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-[10px] sm:text-[11px]">
                        @php
                        $validCount = $allDocumentsChecklist->where('status', 'valid')->count();
                        $pendingCount = $allDocumentsChecklist->where('status', 'pending_review')->count();
                        $expiredCount = $allDocumentsChecklist->where('status', 'expired')->count();
                        $reqPending = $additionalRequirements->where('is_uploaded', false)->count();
                        @endphp
                        @if($validCount > 0)
                        <span class="flex items-center gap-1 sm:gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-emerald-500"></span>
                            <span class="hidden xs:inline">{{ $validCount }} valid</span>
                            <span class="inline xs:hidden">{{ $validCount }}</span>
                        </span>
                        @endif
                        @if($pendingCount > 0)
                        <span class="flex items-center gap-1 sm:gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-amber-500"></span>
                            <span class="hidden xs:inline">{{ $pendingCount }} review</span>
                            <span class="inline xs:hidden">{{ $pendingCount }}</span>
                        </span>
                        @endif
                        @if($reqPending > 0)
                        <span class="flex items-center gap-1 sm:gap-1.5 text-gray-600 dark:text-gray-300">
                            <span class="h-1.5 w-1.5 sm:h-2 sm:w-2 rounded-full bg-red-500"></span>
                            <span class="hidden xs:inline">{{ $reqPending }} diminta</span>
                            <span class="inline xs:hidden">{{ $reqPending }}</span>
                        </span>
                        @endif
                    </div>

                    {{-- View All Link --}}
                    <a href=""
                        class="inline-flex items-center gap-1 sm:gap-1.5 rounded-lg bg-primary-600 px-2.5 py-1 sm:px-3 sm:py-1.5 text-[10px] sm:text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-primary-700 hover:shadow dark:bg-primary-500 dark:hover:bg-primary-600">
                        <span class="hidden sm:inline">Kelola Dokumen</span>
                        <span class="inline sm:hidden">Kelola</span>
                        <x-heroicon-o-arrow-right class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                    </a>
                </div>
            </div>
        </div>

    </div>
    @endif
</div>