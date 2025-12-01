<div x-data="{ 
    mounted: false,
    selectedProject: null,
    init() {
        setTimeout(() => {
            this.mounted = true;
            @if($this->projects->isNotEmpty())
            this.selectedProject = {{ $this->projects->first()->id }};
            @endif
        }, 100);
    },
    selectProject(id) {
        this.selectedProject = id;
        $wire.call('selectProject', id);
    }
}" class="h-full">
    <div class="grid h-full gap-8 md:gap-10 lg:grid-cols-12 xl:gap-12" x-show="mounted"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">

        {{-- Left Sidebar - Projects List --}}
        <div class="lg:col-span-5 xl:col-span-4">
            <div class="sticky top-6 space-y-4">
                {{-- Statistics Summary --}}
                <div
                    class="rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 p-4 shadow-sm dark:border-gray-700 dark:from-gray-800 dark:to-gray-800/50">
                    <h2 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Ringkasan Proyek
                    </h2>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <div
                            class="rounded-lg border border-gray-200 bg-white p-2 text-center sm:p-3 dark:border-gray-600 dark:bg-gray-700">
                            <p class="text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-100">{{
                                $this->projects->count() }}</p>
                            <p class="mt-0.5 text-xs text-gray-600 sm:mt-1 dark:text-gray-400">Total</p>
                        </div>
                        <div
                            class="rounded-lg border border-primary-200 bg-primary-50 p-2 text-center sm:p-3 dark:border-primary-800 dark:bg-primary-900/30">
                            <p class="text-xl font-bold text-primary-600 sm:text-2xl dark:text-primary-400">{{
                                $this->activeProjectsCount }}</p>
                            <p class="mt-0.5 text-xs text-primary-600 sm:mt-1 dark:text-primary-400">Aktif</p>
                        </div>
                        <div
                            class="rounded-lg border border-green-200 bg-green-50 p-2 text-center sm:p-3 dark:border-green-800 dark:bg-green-900/30">
                            <p class="text-xl font-bold text-green-600 sm:text-2xl dark:text-green-400">{{
                                $this->completedProjectsCount }}</p>
                            <p class="mt-0.5 text-xs text-green-600 sm:mt-1 dark:text-green-400">Selesai</p>
                        </div>
                    </div>
                </div>

                {{-- Projects List - No Container --}}
                <div class="space-y-3">
                    <h2 class="px-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Daftar Proyek
                    </h2>
                    <div class="max-h-[calc(100vh-280px)] space-y-2 overflow-y-auto pr-1 lg:max-h-[calc(100vh-300px)]">
                        @forelse($this->projects as $project)
                        <button wire:key="list-{{ $project->id }}" @click="selectProject({{ $project->id }})"
                            class="group relative w-full overflow-hidden rounded-xl border text-left transition-all duration-200"
                            :class="{ 
                                'border-primary-400 bg-primary-50 shadow-md ring-2 ring-primary-200 dark:border-primary-500 dark:bg-primary-900/30 dark:ring-primary-700': selectedProject === {{ $project->id }},
                                'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600': selectedProject !== {{ $project->id }}
                            }">
                            {{-- Status Indicator Bar --}}
                            @php
                            $statusColors = [
                            'completed' => 'bg-green-500',
                            'in_progress' => 'bg-blue-500',
                            'review' => 'bg-yellow-500',
                            'draft' => 'bg-gray-400',
                            'analysis' => 'bg-purple-500',
                            'canceled' => 'bg-red-500',
                            ];
                            $statusColor = $statusColors[$project->status] ?? 'bg-gray-400';
                            @endphp
                            <div class="absolute left-0 top-0 h-full w-1 {{ $statusColor }}"></div>

                            <div class="p-4 pl-5">
                                <div class="flex items-start gap-3">
                                    {{-- Status Icon --}}
                                    <div class="mt-0.5 flex-shrink-0">
                                        @if($project->status === 'completed')
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/40">
                                            <x-heroicon-s-check-circle
                                                class="h-6 w-6 text-green-600 dark:text-green-400" />
                                        </div>
                                        @elseif($project->status === 'in_progress')
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/40">
                                            <x-heroicon-s-arrow-path class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        @elseif($project->status === 'review')
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900/40">
                                            <x-heroicon-s-eye class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                                        </div>
                                        @elseif($project->status === 'analysis')
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/40">
                                            <x-heroicon-s-beaker class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                                        </div>
                                        @else
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-600">
                                            <x-heroicon-o-clock class="h-6 w-6 text-gray-600 dark:text-gray-300" />
                                        </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        {{-- Project Name --}}
                                        <h3 class="truncate font-semibold text-gray-900 transition-colors dark:text-gray-100"
                                            :class="{ 'text-primary-700 dark:text-primary-300': selectedProject === {{ $project->id }} }">
                                            {{ $project->name }}
                                        </h3>

                                        {{-- Client Name --}}
                                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                            <x-heroicon-o-building-office-2 class="inline h-3.5 w-3.5" />
                                            {{ $project->client->name }}
                                        </p>

                                        {{-- Status Badge & Due Date --}}
                                        <div class="mt-2 flex items-center gap-2">
                                            @php
                                            $statusBadges = [
                                            'completed' => ['text' => 'Selesai', 'class' => 'bg-green-100 text-green-700
                                            dark:bg-green-900/40 dark:text-green-300'],
                                            'in_progress' => ['text' => 'Dikerjakan', 'class' => 'bg-blue-100
                                            text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                                            'review' => ['text' => 'Review', 'class' => 'bg-yellow-100 text-yellow-700
                                            dark:bg-yellow-900/40 dark:text-yellow-300'],
                                            'draft' => ['text' => 'Draft', 'class' => 'bg-gray-100 text-gray-700
                                            dark:bg-gray-600 dark:text-gray-200'],
                                            'analysis' => ['text' => 'Analisis', 'class' => 'bg-purple-100
                                            text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'],
                                            'canceled' => ['text' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-700
                                            dark:bg-red-900/40 dark:text-red-300'],
                                            ];
                                            $badge = $statusBadges[$project->status] ?? ['text' =>
                                            ucfirst($project->status), 'class' => 'bg-gray-100 text-gray-700
                                            dark:bg-gray-600 dark:text-gray-200'];
                                            @endphp
                                            <span
                                                class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $badge['class'] }}">
                                                {{ $badge['text'] }}
                                            </span>

                                            {{-- Due Date --}}
                                            @if($project->due_date)
                                            <span
                                                class="ml-auto flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                                <x-heroicon-o-calendar class="h-3.5 w-3.5" />
                                                {{ $project->due_date->format('d M') }}
                                            </span>
                                            @endif
                                        </div>

                                        {{-- Priority Indicator --}}
                                        @if(in_array($project->priority, ['urgent', 'high']))
                                        <div
                                            class="mt-2 flex items-center gap-1 text-xs font-medium {{ $project->priority === 'urgent' ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }}">
                                            @if($project->priority === 'urgent')
                                            <x-heroicon-s-fire class="h-3.5 w-3.5" />
                                            Mendesak
                                            @else
                                            <x-heroicon-s-exclamation-triangle class="h-3.5 w-3.5" />
                                            Prioritas Tinggi
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Hover Effect --}}
                            <div class="pointer-events-none absolute inset-0 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                                :class="{ 'opacity-0': selectedProject === {{ $project->id }} }"
                                style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.03) 0%, transparent 50%);">
                            </div>
                        </button>
                        @empty
                        <div
                            class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center dark:border-gray-600 dark:bg-gray-700/30">
                            <x-heroicon-o-folder-open class="mx-auto h-12 w-12 text-gray-400" />
                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada proyek</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Proyek Anda akan muncul di sini</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Panel - Clean Project Detail --}}
        <div class="lg:col-span-7 xl:col-span-8">
            @forelse($this->projects as $project)
            <div wire:key="detail-{{ $project->id }}" x-show="selectedProject === {{ $project->id }}"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0" class="space-y-6">

                {{-- Clean Project Header --}}
                <div>
                    <div class="flex items-center gap-3">
                        @if($project->status === 'completed')
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <x-heroicon-s-check-circle class="h-4 w-4" />
                            Selesai
                        </span>
                        @elseif($project->status === 'in_progress')
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-blue-600 dark:bg-blue-400"></span>
                            Sedang Dikerjakan
                        </span>
                        @elseif($project->status === 'review')
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                            <x-heroicon-s-eye class="h-4 w-4" />
                            Review
                        </span>
                        @else
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <x-heroicon-o-clock class="h-4 w-4" />
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                        @endif

                        @php
                        $priorityConfig = [
                        'urgent' => ['bg' => 'bg-red-100 dark:bg-red-900/30', 'text' => 'text-red-700
                        dark:text-red-400', 'icon' => 'heroicon-s-fire'],
                        'high' => ['bg' => 'bg-orange-100 dark:bg-orange-900/30', 'text' => 'text-orange-700
                        dark:text-orange-400', 'icon' => 'heroicon-s-exclamation-triangle'],
                        'normal' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-700
                        dark:text-gray-300', 'icon' => 'heroicon-s-minus-circle'],
                        'low' => ['bg' => 'bg-gray-100 dark:bg-gray-700', 'text' => 'text-gray-600 dark:text-gray-400',
                        'icon' => 'heroicon-s-minus'],
                        ];
                        $config = $priorityConfig[$project->priority] ?? $priorityConfig['normal'];
                        @endphp
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $config['bg'] }} {{ $config['text'] }}">
                            <x-dynamic-component :component="$config['icon']" class="h-3.5 w-3.5" />
                            {{ ucfirst($project->priority) }}
                        </span>
                    </div>

                    <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl dark:text-gray-100">
                        {{ $project->name }}
                    </h1>

                    <div
                        class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-500 sm:gap-4 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-o-building-office-2 class="h-4 w-4" />
                            {{ $project->client->name }}
                        </span>
                        @if($project->due_date)
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-o-calendar class="h-4 w-4" />
                            Target: {{ $project->due_date->format('d M Y') }}
                        </span>
                        @endif
                        @if($project->pic)
                        <span class="flex items-center gap-1.5">
                            <x-heroicon-o-user class="h-4 w-4" />
                            {{ $project->pic->name }}
                        </span>
                        @endif
                    </div>

                    @if($project->client_description || $project->description)
                    <div class="mt-6">
                        <p class="text-base leading-relaxed text-gray-700 dark:text-gray-300">
                            {{ $project->client_description ?? $project->description }}
                        </p>
                    </div>
                    @endif
                </div>

                {{-- Deliverables Section - Clean Design --}}
                @if($project->deliverable_files && count($project->deliverable_files) > 0)
                <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">File Hasil Proyek</h3>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{
                                count($project->deliverable_files) }} file tersedia</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($project->deliverable_files as $index => $file)
                        @php
                        $fileName = 'Unknown File';
                        $fileExtension = '';
                        if (is_string($file)) {
                        $fileName = basename($file);
                        $fileExtension = strtoupper(pathinfo($file, PATHINFO_EXTENSION));
                        } elseif (is_array($file) && isset($file['name'])) {
                        $fileName = $file['name'];
                        $fileExtension = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
                        } elseif (is_array($file) && isset($file['path'])) {
                        $fileName = basename($file['path']);
                        $fileExtension = strtoupper(pathinfo($file['path'], PATHINFO_EXTENSION));
                        }
                        @endphp
                        <button wire:click="downloadDeliverable({{ $project->id }}, {{ $index }})"
                            class="group flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-left transition-all hover:border-primary-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-600">
                            <div
                                class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                                {{ $fileExtension ?: 'FILE' }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $fileName }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Klik untuk unduh</p>
                            </div>
                            <x-heroicon-o-arrow-down-tray
                                class="h-5 w-5 flex-shrink-0 text-gray-400 transition group-hover:text-primary-600 dark:text-gray-500 dark:group-hover:text-primary-400" />
                        </button>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Result Notes - Enhanced Design --}}
                @if($project->result_notes)
                <div class="border-t border-gray-200 pt-6 dark:border-gray-700">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30">
                            <x-heroicon-o-document-text class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Catatan Hasil</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Ringkasan dan catatan penting dari
                                proyek</p>
                        </div>
                    </div>

                    <div
                        class="relative overflow-hidden rounded-xl border border-gray-200 bg-gradient-to-br from-white to-gray-50 dark:border-gray-700 dark:from-gray-800 dark:to-gray-800/50">
                        {{-- Decorative Corner --}}
                        <div class="absolute right-0 top-0 h-32 w-32 opacity-10">
                            <svg viewBox="0 0 100 100" class="text-primary-500">
                                <circle cx="50" cy="50" r="50" fill="currentColor" />
                            </svg>
                        </div>

                        {{-- Content --}}
                        <div class="relative p-5 sm:p-6">
                            <div class="prose prose-sm max-w-none dark:prose-invert">
                                <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300"
                                    style="white-space: pre-wrap;">{{ $project->result_notes }}</p>
                            </div>
                        </div>

                        {{-- Bottom Accent --}}
                        <div class="h-1 bg-gradient-to-r from-primary-500 via-primary-400 to-primary-300"></div>
                    </div>
                </div>
                @endif

                {{-- Empty State --}}
                @if((!$project->deliverable_files || count($project->deliverable_files) === 0) &&
                !$project->result_notes)
                <div class="border-t border-gray-200 pt-12 text-center dark:border-gray-700">
                    <x-heroicon-o-document-arrow-down class="mx-auto h-12 w-12 text-gray-400" />
                    <p class="mt-3 font-medium text-gray-900 dark:text-gray-100">Belum Ada File Hasil</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">File hasil proyek akan tersedia ketika
                        proyek telah selesai</p>
                </div>
                @endif
            </div>
            @empty
            <div class="flex h-full items-center justify-center">
                <div class="text-center">
                    <x-heroicon-o-folder-open class="mx-auto h-16 w-16 text-gray-400" />
                    <p class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Pilih Proyek</p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Pilih proyek dari daftar di sebelah kiri
                        untuk melihat detailnya</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>