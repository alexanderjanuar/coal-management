<div class="space-y-6">
    {{-- Header Section --}}
    <div class="rounded-2xl bg-gradient-to-r from-gray-50 to-gray-100 p-6 dark:from-gray-800 dark:to-gray-900">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Proyek & Layanan
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola proyek untuk {{ $client->name }}
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        @php
        $statsConfig = [
        ['label' => 'Total Proyek', 'value' => $stats['total'] ?? 0, 'icon' => 'heroicon-o-folder-open', 'color' =>
        'text-blue-600 dark:text-blue-400'],
        ['label' => 'Sedang Berjalan', 'value' => $stats['in_progress'] ?? 0, 'icon' => 'heroicon-o-arrow-path', 'color'
        => 'text-yellow-600 dark:text-yellow-400'],
        ['label' => 'Selesai', 'value' => $stats['completed'] ?? 0, 'icon' => 'heroicon-o-check-circle', 'color' =>
        'text-green-600 dark:text-green-400'],
        ['label' => 'Urgent', 'value' => $stats['urgent'] ?? 0, 'icon' => 'heroicon-o-exclamation-triangle', 'color' =>
        'text-red-600 dark:text-red-400'],
        ];
        @endphp

        @foreach($statsConfig as $stat)
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
                    <x-filament::icon :icon="$stat['icon']" class="h-6 w-6 {{ $stat['color'] }}" />
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Projects Table --}}
    <div class="overflow-hidden rounded-2xl bg-white shadow-sm dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Nama Proyek
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Tipe & Prioritas
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Deadline
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Status
                        </th>
                        <th scope="col"
                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            PIC
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse($projects as $project)
                    <tr wire:navigate
                        href="{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}"
                        class="group cursor-pointer transition-all duration-200 hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent dark:hover:from-blue-900/20 dark:hover:to-transparent hover:shadow-sm"
                        onclick="window.location.href='{{ route('filament.admin.resources.projects.view', ['record' => $project->id]) }}'">
                        {{-- Nama Proyek --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-md transition-transform group-hover:scale-110">
                                    <x-filament::icon icon="heroicon-o-folder-open" class="h-6 w-6" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white truncate"
                                        title="{{ $project->name }}">
                                        {{ $project->name }}
                                    </p>
                                    @if($project->description)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                        title="{{ $project->description }}">
                                        {{ $project->description }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Tipe & Prioritas --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex flex-col gap-2">
                                {{-- Type Badge - Simplified --}}
                                @php
                                $typeConfig = [
                                'single' => ['class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'label' => 'Single'],
                                'monthly' => ['class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700
                                dark:text-gray-300', 'label' => 'Monthly'],
                                'yearly' => ['class' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'label' => 'Yearly']
                                ];
                                $typeConf = $typeConfig[$project->type] ?? $typeConfig['single'];
                                @endphp

                                <span
                                    class="inline-flex w-fit items-center rounded-md px-2 py-1 text-xs font-medium {{ $typeConf['class'] }}">
                                    {{ $typeConf['label'] }}
                                </span>

                                {{-- Priority Badge - Only show if Urgent --}}
                                @if($project->priority === 'urgent')
                                <span
                                    class="inline-flex w-fit items-center gap-1 rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Urgent
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- Deadline --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            @php
                            $now = \Carbon\Carbon::now();
                            $dueDate = \Carbon\Carbon::parse($project->due_date);
                            $diff = $now->diffInDays($dueDate, false);

                            if ($diff < 0) { $deadlineClass='text-red-600 dark:text-red-400' ;
                                $deadlineIcon='heroicon-o-exclamation-triangle' ;
                                $deadlineBg='bg-red-50 dark:bg-red-900/20' ; } elseif ($diff <=7) {
                                $deadlineClass='text-yellow-600 dark:text-yellow-400' ; $deadlineIcon='heroicon-o-clock'
                                ; $deadlineBg='bg-yellow-50 dark:bg-yellow-900/20' ; } else {
                                $deadlineClass='text-gray-600 dark:text-gray-400' ; $deadlineIcon='heroicon-o-calendar'
                                ; $deadlineBg='bg-gray-50 dark:bg-gray-900/50' ; } @endphp <div
                                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 {{ $deadlineBg }}">
                                <x-filament::icon :icon="$deadlineIcon" class="h-4 w-4 {{ $deadlineClass }}" />
                                <div>
                                    <p class="text-xs font-semibold {{ $deadlineClass }}">
                                        {{ $dueDate->format('d M Y') }}
                                    </p>
                                    <p class="text-xs {{ $deadlineClass }}">
                                        @if($diff < 0) Terlambat {{ abs($diff) }} hari @elseif($diff==0) Hari ini! @else
                                            {{ $diff }} hari lagi @endif </p>
                                </div>
        </div>
        </td>

        {{-- Status --}}
        <td class="whitespace-nowrap px-6 py-4">
            @php
            $statusConfig = [
            'draft' => ['class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'label' => 'Draft'],
            'analysis' => ['class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'label' =>
            'Analysis'],
            'in_progress' => ['class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300', 'label' =>
            'In Progress', 'pulse' => true],
            'review' => ['class' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'label' =>
            'Review'],
            'completed' => ['class' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300', 'label'
            => 'Completed'],
            'completed (Not Payed Yet)' => ['class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40
            dark:text-yellow-300', 'label' => 'Not Paid'],
            'canceled' => ['class' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'label' =>
            'Canceled']
            ];
            $statusConf = $statusConfig[$project->status] ?? $statusConfig['draft'];
            @endphp

            <span
                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium {{ $statusConf['class'] }}">
                @if(isset($statusConf['pulse']) && $statusConf['pulse'])
                <span class="relative flex h-2 w-2">
                    <span
                        class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                </span>
                @endif
                {{ $statusConf['label'] }}
            </span>
        </td>

        {{-- PIC --}}
        <td class="whitespace-nowrap px-6 py-4">
            @if($project->pic)
            <div class="inline-flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-900/50">
                <x-filament::icon icon="heroicon-o-user-circle" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $project->pic->name }}
                </span>
            </div>
            @else
            <span class="text-sm italic text-gray-400 dark:text-gray-500">-</span>
            @endif
        </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="px-6 py-16 text-center">
                <div
                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <x-filament::icon icon="heroicon-o-folder-open"
                        class="h-10 w-10 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="mt-6 text-xl font-semibold text-gray-900 dark:text-white">
                    Belum ada proyek
                </h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Proyek untuk klien ini akan muncul di sini.
                </p>
            </td>
        </tr>
        @endforelse
        </tbody>
        </table>
    </div>
</div>
</div>