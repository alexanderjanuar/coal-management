{{-- Project Chaining: chain info card (kalau sudah ada child) atau CTA → form wizard --}}
<div>
    @php
        $hasChild = $this->hasChild;
        $isEligible = $this->isEligible;
        $child = $hasChild ? $this->child : null;
    @endphp

    @if ($hasChild && $child)
        {{-- ============ CHAIN INFO CARD ============ --}}
        @php
            $childUrl = \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $child]);
            $transferredCount = $this->transferredFileCount;

            $statusLabel = $child->statusRecord?->label ?? ucfirst(str_replace('_', ' ', $child->status));
            $statusColor = match ($child->statusRecord?->category) {
                'not_started' => 'gray',
                'active'      => 'info',
                'done'        => 'success',
                'closed'      => 'danger',
                default       => 'gray',
            };
        @endphp

        <div class="mt-6">
            <x-filament::section icon="heroicon-o-arrow-right-circle" icon-color="success">
                <x-slot name="heading">Proyek Lanjutan</x-slot>
                <x-slot name="description">
                    Proyek ini sudah dilanjutkan. Satu proyek hanya boleh punya satu lanjutan.
                </x-slot>

                <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-6 items-start">
                    <div class="space-y-4 min-w-0">
                        <div class="space-y-1.5">
                            <a href="{{ $childUrl }}"
                               class="block text-base font-semibold text-gray-950 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                {{ $child->name }}
                            </a>
                            <div class="flex items-center gap-2 flex-wrap">
                                <x-filament::badge :color="$statusColor" size="sm">
                                    {{ $statusLabel }}
                                </x-filament::badge>
                                @if($child->priority && $child->priority !== 'normal')
                                    <x-filament::badge
                                        :color="$child->priority === 'urgent' ? 'danger' : 'gray'"
                                        size="sm">
                                        {{ ucfirst($child->priority) }}
                                    </x-filament::badge>
                                @endif
                            </div>
                        </div>

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Klien</dt>
                                <dd class="mt-0.5 text-gray-900 dark:text-gray-100">{{ $child->client?->name ?? '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Tenggat</dt>
                                <dd class="mt-0.5 text-gray-900 dark:text-gray-100">
                                    {{ $child->due_date ? \Carbon\Carbon::parse($child->due_date)->translatedFormat('d M Y') : '—' }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Dibuat</dt>
                                <dd class="mt-0.5 text-gray-900 dark:text-gray-100">
                                    {{ $child->created_at->translatedFormat('d M Y, H:i') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">PIC</dt>
                                <dd class="mt-0.5 text-gray-900 dark:text-gray-100">{{ $child->pic?->name ?? '—' }}</dd>
                            </div>
                        </dl>

                        @if($transferredCount > 0)
                            <div class="flex items-center gap-2 px-3 py-2 rounded-md bg-success-50 dark:bg-success-950/40 text-success-700 dark:text-success-300 text-sm border border-success-200 dark:border-success-900">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>
                                    <strong>{{ $transferredCount }}</strong>
                                    file dioper dari proyek ini ke proyek lanjutan
                                </span>
                            </div>
                        @else
                            <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                Tidak ada file yang dioper saat pembuatan.
                            </div>
                        @endif
                    </div>

                    <div class="lg:pt-1">
                        <x-filament::button
                            tag="a"
                            :href="$childUrl"
                            color="primary"
                            icon="heroicon-o-arrow-top-right-on-square"
                            icon-position="after">
                            Buka Proyek Lanjutan
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        </div>

    @elseif ($isEligible)
        {{-- ============ CTA + WIZARD (toggled by Alpine) ============ --}}
        <div class="mt-6" x-data="{ show: false }">
            {{-- Default state: compact CTA --}}
            <div x-show="!show"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/30">
                <div class="flex items-start sm:items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Lanjutkan proyek ini ke proyek baru
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Salin SOP yang berbeda dan oper file dari proyek ini ke proyek lanjutan.
                        </p>
                    </div>
                </div>
                <x-filament::button
                    @click="show = true"
                    color="primary"
                    icon="heroicon-o-plus"
                    class="flex-shrink-0">
                    Buat Proyek Lanjutan
                </x-filament::button>
            </div>

            {{-- Expanded state: wizard form --}}
            <div x-show="show"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <x-filament::section icon="heroicon-o-square-2-stack" icon-color="primary">
                    <x-slot name="heading">Buat Proyek Lanjutan</x-slot>
                    <x-slot name="description">
                        Ikuti 3 langkah singkat. Anda bisa mundur ke langkah sebelumnya kapan saja.
                    </x-slot>

                    <x-slot name="headerEnd">
                        <button type="button"
                                @click="show = false"
                                class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Tutup
                        </button>
                    </x-slot>

                    <form wire:submit="create">
                        {{ $this->form }}
                    </form>
                </x-filament::section>
            </div>
        </div>
    @endif
</div>
