<div class="space-y-6">
    {{-- Header Section --}}
    <div
        class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 p-6 dark:from-gray-800 dark:to-gray-900">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Kewajiban Pajak & Deadline
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Monitor kepatuhan pelaporan pajak untuk {{ $client->name }}
            </p>
        </div>

        <a href="{{ route('filament.admin.resources.tax-reports.view', ['record' => $client->id]) }}" wire:navigate>
            <x-filament::button icon="heroicon-o-plus" size="lg">
                Tambah Kewajiban
            </x-filament::button>
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        @php
        $totalObligations = count($obligations);
        $completedObligations = collect($obligations)->where('status', 'Selesai')->count();
        $pendingObligations = collect($obligations)->where('status', 'Pending')->count();
        $tinggiPriority = collect($obligations)->where('priority', 'Tinggi')->count();

        $stats = [
        ['label' => 'Total Kewajiban', 'value' => $totalObligations, 'icon' => 'heroicon-o-document-text'],
        ['label' => 'Selesai', 'value' => $completedObligations, 'icon' => 'heroicon-o-check-circle', 'color' =>
        'text-green-600 dark:text-green-400'],
        ['label' => 'Pending', 'value' => $pendingObligations, 'icon' => 'heroicon-o-clock', 'color' => 'text-yellow-600
        dark:text-yellow-400'],
        ['label' => 'Prioritas Tinggi', 'value' => $tinggiPriority, 'icon' => 'heroicon-o-exclamation-circle', 'color'
        => 'text-red-600 dark:text-red-400'],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="rounded-xl bg-white p-5 shadow-sm dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ $stat['label'] }}
                    </p>
                    <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $stat['value'] }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                    <x-filament::icon :icon="$stat['icon']"
                        class="h-6 w-6 {{ $stat['color'] ?? 'text-gray-600 dark:text-gray-400' }}" />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Obligations Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Jenis
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Periode
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Deadline
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Prioritas
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($obligations as $obligation)
                    <tr
                        class="group transition-all duration-200 hover:bg-gradient-to-r hover:from-gray-50 hover:to-transparent dark:hover:from-gray-700/30 dark:hover:to-transparent hover:shadow-sm">
                        {{-- Jenis --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-gray-900 to-gray-700 text-white shadow-md transition-transform group-hover:scale-110 dark:from-white dark:to-gray-100 dark:text-gray-900">
                                    <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $obligation['jenis'] }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        @if(str_contains($obligation['jenis'], 'PPN'))
                                        Pajak Pertambahan Nilai
                                        @else
                                        Pajak Penghasilan Pasal 21
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Periode --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            <div
                                class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-900/50">
                                <x-filament::icon icon="heroicon-o-calendar-days"
                                    class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $obligation['periode'] }}
                                </p>
                            </div>
                        </td>

                        {{-- Deadline --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                            $now = \Carbon\Carbon::now();
                            $deadline = \Carbon\Carbon::parse($obligation['deadline']);
                            $diff = $now->diffInDays($deadline, false);

                            $deadlineConfig = [
                            'overdue' => [
                            'textClass' => 'text-red-700 dark:text-red-400',
                            'bgClass' => 'bg-red-50 dark:bg-red-900/20',
                            'icon' => 'heroicon-o-exclamation-triangle',
                            'iconClass' => 'text-red-600 dark:text-red-400'
                            ],
                            'today' => [
                            'textClass' => 'text-orange-700 dark:text-orange-400',
                            'bgClass' => 'bg-orange-50 dark:bg-orange-900/20',
                            'icon' => 'heroicon-o-bell-alert',
                            'iconClass' => 'text-orange-600 dark:text-orange-400'
                            ],
                            'urgent' => [
                            'textClass' => 'text-yellow-700 dark:text-yellow-400',
                            'bgClass' => 'bg-yellow-50 dark:bg-yellow-900/20',
                            'icon' => 'heroicon-o-clock',
                            'iconClass' => 'text-yellow-600 dark:text-yellow-400'
                            ],
                            'normal' => [
                            'textClass' => 'text-gray-700 dark:text-gray-300',
                            'bgClass' => 'bg-gray-50 dark:bg-gray-900/20',
                            'icon' => 'heroicon-o-calendar',
                            'iconClass' => 'text-gray-600 dark:text-gray-400'
                            ]
                            ];

                            if ($diff < 0) { $deadlineType='overdue' ; $countdownText='Terlambat ' . abs($diff)
                                . ' hari' ; } elseif ($diff==0) { $deadlineType='today' ; $countdownText='HARI INI!' ; }
                                elseif ($diff <=7) { $deadlineType='urgent' ; $countdownText=$diff . ' hari lagi' ; }
                                else { $deadlineType='normal' ; $countdownText=$diff . ' hari lagi' ; }
                                $dlConfig=$deadlineConfig[$deadlineType]; @endphp <div class="flex items-start gap-2">
                                <div
                                    class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg {{ $dlConfig['bgClass'] }}">
                                    <x-filament::icon :icon="$dlConfig['icon']"
                                        class="h-4 w-4 {{ $dlConfig['iconClass'] }}" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold {{ $dlConfig['textClass'] }}">
                                        {{ $deadline->format('d M Y') }}
                                    </p>
                                    <p class="text-xs font-medium {{ $dlConfig['textClass'] }}">
                                        @if($diff < 0) <span class="inline-flex items-center gap-1">
                                            <span class="relative flex h-2 w-2">
                                                <span
                                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                                                <span
                                                    class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                                            </span>
                                            {{ $countdownText }}
                                            </span>
                                            @elseif($diff == 0)
                                            <span class="inline-flex items-center gap-1 animate-pulse font-bold">
                                                {{ $countdownText }}
                                            </span>
                                            @else
                                            {{ $countdownText }}
                                            @endif
                                    </p>
                                </div>
        </div>
        </td>

        {{-- Prioritas --}}
        <td class="whitespace-nowrap px-6 py-4">
            @php
            $priorityConfig = [
            'Tinggi' => [
            'icon' => 'heroicon-o-exclamation-triangle',
            'class' => 'bg-red-100 text-red-700 ring-2 ring-red-200 dark:bg-red-900/40 dark:text-red-300
            dark:ring-red-800',
            'tooltip' => 'Peredaran Bruto ≥ Rp 10 Miliar - Perlu perhatian khusus'
            ],
            'Sedang' => [
            'icon' => 'heroicon-o-exclamation-circle',
            'class' => 'bg-yellow-100 text-yellow-700 ring-2 ring-yellow-200 dark:bg-yellow-900/40 dark:text-yellow-300
            dark:ring-yellow-800',
            'tooltip' => 'Peredaran Bruto ≥ Rp 1 Miliar - Prioritas menengah'
            ],
            'Rendah' => [
            'icon' => 'heroicon-o-information-circle',
            'class' => 'bg-gray-100 text-gray-700 ring-2 ring-gray-200 dark:bg-gray-700 dark:text-gray-300
            dark:ring-gray-600',
            'tooltip' => 'Peredaran Bruto < Rp 1 Miliar - Prioritas standar' ] ];
                $config=$priorityConfig[$obligation['priority']] ?? $priorityConfig['Rendah']; @endphp <div
                x-data="{ tooltip: false }" class="relative inline-block">
                <span @mouseenter="tooltip = true" @mouseleave="tooltip = false"
                    class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold shadow-sm transition-all hover:shadow-md {{ $config['class'] }}">
                    <x-filament::icon :icon="$config['icon']" class="h-4 w-4" />
                    {{ $obligation['priority'] }}
                </span>

                {{-- Tooltip --}}
                <div x-show="tooltip" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    class="absolute bottom-full left-1/2 z-50 mb-2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-100 dark:text-gray-900"
                    style="display: none;">
                    {{ $config['tooltip'] }}
                    <div
                        class="absolute left-1/2 top-full h-2 w-2 -translate-x-1/2 rotate-45 transform bg-gray-900 dark:bg-gray-100">
                    </div>
                </div>
    </div>
    </td>

    {{-- Status --}}
    <td class="whitespace-nowrap px-6 py-4">
        @php
        $statusConfig = [
        'Selesai' => [
        'icon' => 'heroicon-o-check-circle',
        'class' => 'bg-green-100 text-green-700 ring-2 ring-green-200 dark:bg-green-900/40 dark:text-green-300
        dark:ring-green-800',
        'tooltip' => 'Laporan telah disampaikan ke DJP',
        'pulse' => false
        ],
        'Pending' => [
        'icon' => 'heroicon-o-clock',
        'class' => 'bg-orange-100 text-orange-700 ring-2 ring-orange-200 dark:bg-orange-900/40 dark:text-orange-300
        dark:ring-orange-800',
        'tooltip' => 'Menunggu pelaporan - Segera laporkan sebelum deadline',
        'pulse' => true
        ]
        ];
        $statusConf = $statusConfig[$obligation['status']] ?? $statusConfig['Pending'];
        @endphp

        <div x-data="{ statusTooltip: false }" class="relative inline-block">
            <span @mouseenter="statusTooltip = true" @mouseleave="statusTooltip = false"
                class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold shadow-sm transition-all hover:shadow-md {{ $statusConf['class'] }}">
                @if($statusConf['pulse'])
                <span class="relative flex h-3 w-3">
                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-orange-500"></span>
                </span>
                @else
                <x-filament::icon :icon="$statusConf['icon']" class="h-4 w-4" />
                @endif
                {{ $obligation['status'] }}
            </span>

            {{-- Tooltip --}}
            <div x-show="statusTooltip" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="absolute bottom-full left-1/2 z-50 mb-2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900 px-3 py-2 text-xs text-white shadow-lg dark:bg-gray-100 dark:text-gray-900"
                style="display: none;">
                {{ $statusConf['tooltip'] }}
                <div
                    class="absolute left-1/2 top-full h-2 w-2 -translate-x-1/2 rotate-45 transform bg-gray-900 dark:bg-gray-100">
                </div>
            </div>
        </div>
    </td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="px-6 py-16 text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                <x-filament::icon icon="heroicon-o-document-text" class="h-10 w-10 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                Belum ada kewajiban pajak
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Tambahkan laporan pajak untuk mulai melacak kewajiban compliance.
            </p>
            <div class="mt-8">
                <a href="{{ route('filament.admin.resources.tax-reports.create') }}" wire:navigate>
                    <x-filament::button icon="heroicon-o-plus" size="lg">
                        Tambah Kewajiban Pertama
                    </x-filament::button>
                </a>
            </div>
        </td>
    </tr>
    @endforelse
    </tbody>
    </table>
</div>
</div>
</div>