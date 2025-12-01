<div class="space-y-6" x-data="{ 
        showAllDocuments: false,
        showAllProjects: false,
        mounted: false 
    }" x-init="setTimeout(() => mounted = true, 100)">

    @if($clients->isEmpty())
    {{-- No Client Warning --}}
    <div class="rounded-lg border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-show="mounted" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="rounded-lg bg-gray-100 p-3 dark:bg-gray-700">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Akun Belum Terhubung
                </h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Akun Anda belum terhubung dengan data klien. Silakan hubungi administrator untuk menghubungkan akun
                    Anda ke data klien yang sesuai.
                </p>
                <div class="mt-4">
                    <a href="mailto:admin@example.com"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600">
                        <x-heroicon-o-envelope class="h-4 w-4" />
                        Hubungi Administrator
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Client Selector --}}
    @if($clients->count() > 1)
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-show="mounted" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <x-heroicon-o-building-office-2 class="h-5 w-5 text-gray-400" />
                <span>Pilih Perusahaan:</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($clients as $client)
                <button wire:click="selectClient({{ $client->id }})" class="rounded-lg px-4 py-2 text-sm font-medium transition-colors
                            {{ $selectedClientId === $client->id 
                                ? 'bg-primary-600 text-white hover:bg-primary-700' 
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600' 
                            }}">
                    {{ $client->name }}
                </button>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Welcome Message --}}
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-show="mounted" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                @if($selectedClient && $selectedClient->logo)
                <img src="{{ Storage::url($selectedClient->logo) }}" alt="{{ $selectedClient->name }}"
                    class="h-12 w-12 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-700">
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $selectedClient ? $selectedClient->name : 'Dashboard Overview' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Ringkasan aktivitas dan status perusahaan Anda
                    </p>
                </div>
            </div>
            <button wire:click="refresh"
                class="rounded-lg bg-gray-100 p-2 text-gray-600 transition-colors hover:bg-primary-50 hover:text-primary-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-primary-900/20 dark:hover:text-primary-400"
                title="Refresh Data">
                <x-heroicon-o-arrow-path class="h-5 w-5" />
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Active Projects Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
            x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-100"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Proyek Aktif
                    </p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $projectStats['active'] }}
                    </p>
                    <div class="mt-3 flex items-center gap-2">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full bg-primary-600 transition-all duration-500 dark:bg-primary-500"
                                style="width: {{ $projectStats['total'] > 0 ? round(($projectStats['active'] / $projectStats['total']) * 100) : 0 }}%">
                            </div>
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ $projectStats['total'] }}
                        </span>
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-heroicon-o-folder class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </div>
            </div>
        </div>

        {{-- Completed Projects Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
            x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-150"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Proyek Selesai
                    </p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $projectStats['completed'] }}
                    </p>
                    <div class="mt-3 flex items-center gap-2">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full bg-primary-600 transition-all duration-500 dark:bg-primary-500"
                                style="width: {{ $projectStats['total'] > 0 ? round(($projectStats['completed'] / $projectStats['total']) * 100) : 0 }}%">
                            </div>
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ $projectStats['pending'] }} pending
                        </span>
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-heroicon-o-check-circle class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </div>
            </div>
        </div>

        {{-- Tax Reports Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
            x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-200"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Laporan Dilaporkan
                    </p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $taxReportStats['reported'] }}
                    </p>
                    <div class="mt-3 flex items-center gap-2">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full bg-primary-600 transition-all duration-500 dark:bg-primary-500"
                                style="width: {{ $taxReportStats['completion_percentage'] }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ $taxReportStats['completion_percentage'] }}%
                        </span>
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-heroicon-o-document-chart-bar class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </div>
            </div>
        </div>

        {{-- Documents Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
            x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-250"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        Total Dokumen
                    </p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $documentStats['total'] }}
                    </p>
                    <div class="mt-3 flex items-center gap-3 text-xs">
                        <span class="text-gray-600 dark:text-gray-400">{{ $documentStats['valid'] }} valid</span>
                        @if($documentStats['expired'] > 0)
                        <span class="text-gray-600 dark:text-gray-400">{{ $documentStats['expired'] }} expired</span>
                        @endif
                    </div>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-heroicon-o-document-text class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Active Projects Section --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="border-b border-gray-200 p-5 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Proyek Aktif
                    </h3>
                    @if($activeProjects->count() > 3)
                    <button @click="showAllProjects = !showAllProjects"
                        class="text-sm font-medium text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        <span x-text="showAllProjects ? 'Sembunyikan' : 'Lihat Semua'"></span>
                    </button>
                    @endif
                </div>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($activeProjects as $index => $project)
                <div class="p-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    x-show="showAllProjects || {{ $index }} < 3" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                <x-heroicon-o-folder class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $project->name }}
                                    </h4>
                                    @if($project->description)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ Str::limit($project->description, 150) }}
                                    </p>
                                    @endif
                                </div>

                                @if($project->due_date)
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Target
                                    </p>
                                    <p class="mt-0.5 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $project->due_date->format('d M Y') }}
                                    </p>
                                    @php
                                    $daysUntilDue = now()->diffInDays($project->due_date, false);
                                    @endphp
                                    @if($daysUntilDue >= 0 && $daysUntilDue <= 7) <p
                                        class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        {{ $daysUntilDue }} hari lagi
                                        </p>
                                        @elseif($daysUntilDue < 0) <p
                                            class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                            Terlambat {{ abs($daysUntilDue) }} hari
                                            </p>
                                            @endif
                                </div>
                                @endif
                            </div>

                            <div class="mt-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-md bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 dark:bg-primary-900/20 dark:text-primary-400">
                                    <span
                                        class="inline-block h-1.5 w-1.5 rounded-full bg-primary-600 dark:bg-primary-400"></span>
                                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center">
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                        <x-heroicon-o-folder-open class="h-8 w-8 text-gray-400 dark:text-gray-600" />
                    </div>
                    <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                        Tidak ada proyek aktif
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Proyek akan muncul di sini saat ada pekerjaan baru
                    </p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Documents Section --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-350"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0">
            <div class="border-b border-gray-200 p-5 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        Dokumen Terbaru
                    </h3>
                    @if($recentDocuments->count() > 5)
                    <button @click="showAllDocuments = !showAllDocuments"
                        class="text-sm font-medium text-primary-600 transition-colors hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                        <span x-text="showAllDocuments ? 'Sembunyikan' : 'Lihat Semua'"></span>
                    </button>
                    @endif
                </div>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($recentDocuments as $index => $document)
                <div class="group p-5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                    x-show="showAllDocuments || {{ $index }} < 5" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                @if(str_contains($document->original_filename ?? '', '.pdf'))
                                <x-heroicon-o-document-text class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                                @elseif(str_contains($document->original_filename ?? '', '.doc'))
                                <x-heroicon-o-document class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                                @elseif(str_contains($document->original_filename ?? '', '.xls'))
                                <x-heroicon-o-table-cells class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                                @else
                                <x-heroicon-o-document class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                                @endif
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                                        {{ $document->original_filename ?? basename($document->file_path) }}
                                    </h4>

                                    @if($document->sopLegalDocument)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $document->sopLegalDocument->name }}
                                    </p>
                                    @endif

                                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $document->created_at->diffForHumans() }}</span>
                                        @if($document->user)
                                        <span>•</span>
                                        <span>{{ $document->user->name }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium
                                            {{ $document->status === 'valid' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                            {{ $document->status === 'expired' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                            {{ $document->status === 'pending' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                        ">
                                        {{ ucfirst($document->status) }}
                                    </span>

                                    <button wire:click="downloadDocument({{ $document->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-primary-50 px-2.5 py-1 text-xs font-medium text-primary-700 opacity-0 transition-all group-hover:opacity-100 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30">
                                        <x-heroicon-o-arrow-down-tray class="h-3.5 w-3.5" />
                                        Download
                                    </button>
                                </div>
                            </div>

                            @if($document->expired_at)
                            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                Berlaku hingga:
                                <span class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $document->expired_at->format('d M Y') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center">
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                        <x-heroicon-o-document-text class="h-8 w-8 text-gray-400 dark:text-gray-600" />
                    </div>
                    <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                        Belum ada dokumen
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Dokumen yang diupload akan muncul di sini
                    </p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Latest Tax Report Summary --}}
    @if($latestTaxReport)
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
        x-show="mounted" x-transition:enter="transition ease-out duration-300 delay-400"
        x-transition:enter-start="opacity-0 transform translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="border-b border-gray-200 p-5 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    Laporan Pajak Terbaru
                </h3>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Periode:</span>
                    <span
                        class="rounded-md bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-900 dark:bg-gray-700 dark:text-gray-100">
                        {{ $latestTaxReport->month }}
                    </span>
                </div>
            </div>
        </div>
        <div class="p-5">
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach($latestTaxReport->taxCalculationSummaries as $summary)
                <div
                    class="rounded-lg border border-gray-200 bg-gray-50 p-5 transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800/50">
                    <!-- Header -->
                    <div class="flex items-start justify-between border-b border-gray-200 pb-3 dark:border-gray-700">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {{ $summary->tax_type_name }}
                            </h4>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Laporan {{ $summary->tax_type_name }}
                            </p>
                        </div>
                        <span class="rounded-md px-2.5 py-1 text-xs font-semibold
                                {{ $summary->report_status === 'Sudah Lapor' 
                                    ? 'bg-primary-600 text-white dark:bg-primary-500' 
                                    : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' 
                                }}
                            ">
                            {{ $summary->report_status }}
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="mt-4 space-y-3">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Status:</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $summary->status_final }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Saldo:</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $summary->formatted_saldo_final }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @endif
</div>