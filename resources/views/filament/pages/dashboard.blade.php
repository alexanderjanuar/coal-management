<x-filament-panels::page class="w-full">
    <style>
        /* CSS Tambahan untuk Welcome Card */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 30px rgba(59, 130, 246, 0.5); }
        }

        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-glow { animation: glow 2s ease-in-out infinite; }

        .welcome-card {
            position: relative;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f3e8ff 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dark .welcome-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #374151 100%);
        }

        .welcome-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .dark .welcome-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }

        /* Avatar hover effects */
        .avatar-container { transition: all 0.3s ease; }
        .avatar-container:hover { transform: scale(1.05); }

        /* Button hover effects */
        .action-button {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .action-button:hover::before { left: 100%; }

        /* Stats animation */
        .stat-item { transition: all 0.2s ease; }
        .stat-item:hover { transform: translateY(-2px); }

        /* Responsive improvements */
        @media (max-width: 640px) {
            .welcome-card {
                margin: 0 -1rem;
                border-radius: 0;
            }
        }
    </style>

    {{-- Stats Overview --}}
    @livewire('widget.projects-stats-overview')

    {{-- Greeting Card --}}
    @include('components.dashboard.greeting-card')

    {{-- First Row: PIC Chart and Overdue Projects --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div>
            @livewire('widget.person-in-charge-project-chart')
        </div>
        <div>
            @livewire('dashboard.overdue-projects')
        </div>
    </div>

    {{-- Second Row: Recent Activity Table (Full Width) --}}
    <div class="mb-4">
        @livewire('widget.recent-activity-table')
    </div>

    {{-- Third Row: Project Report Chart and Properties Chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
        <div class="lg:col-span-4">
            @livewire('widget.project-report-chart')
        </div>
        <div class="lg:col-span-2">
            @livewire('widget.project-properties-chart')
        </div>
    </div>

    {{-- Project Modal --}}
    <x-filament::modal id="project-stats-modal" width="7xl">
        <x-slot name="header">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <x-heroicon-o-folder class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $modalTitle }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Daftar proyek berdasarkan status
                    </p>
                </div>
            </div>
        </x-slot>

        <div class="max-h-96 overflow-y-auto custom-scrollbar">
            @if(count($modalData) > 0)
            <div class="space-y-3">
                @foreach($modalData as $project)
                <a href="{{ $project['url'] }}" target="_blank"
                    class="block p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h4 class="font-medium text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                    {{ $project['name'] }}
                                </h4>
                                <x-filament::badge :color="match($project['status']) {
                                        'completed' => 'success',
                                        'in_progress' => 'info',
                                        'draft' => 'gray',
                                        'canceled' => 'danger',
                                        default => 'warning'
                                    }">
                                    {{ ucwords(str_replace('_', ' ', $project['status'])) }}
                                </x-filament::badge>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-m-building-office-2 class="w-4 h-4" />
                                    <span>{{ $project['client_name'] }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-heroicon-m-user class="w-4 h-4" />
                                    <span>{{ $project['pic_name'] }}</span>
                                </div>
                                @if($project['due_date'])
                                <div class="flex items-center gap-1">
                                    <x-heroicon-m-calendar class="w-4 h-4" />
                                    <span>{{ $project['due_date'] }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 ml-4">
                            <x-filament::badge :color="match($project['priority']) {
                                    'urgent' => 'danger',
                                    'normal' => 'warning', 
                                    'low' => 'success',
                                    default => 'gray'
                                }">
                                {{ ucwords($project['priority']) }}
                            </x-filament::badge>
                            <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4 text-gray-400" />
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            @if(count($modalData) >= 50)
            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                <p class="text-sm text-blue-700 dark:text-blue-300 text-center">
                    Menampilkan 50 data teratas. Gunakan filter di halaman proyek untuk melihat lebih banyak.
                </p>
            </div>
            @endif
            @else
            <div class="text-center py-12">
                <x-heroicon-o-folder class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Tidak Ada Data
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada proyek dengan status ini.
                </p>
            </div>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex justify-end">
                <x-filament::button color="gray" wire:click="closeModal">
                    Tutup
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- Document Modal --}}
    <x-filament::modal id="document-stats-modal" width="7xl">
        <x-slot name="header">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-100 to-orange-200 dark:from-amber-900 dark:to-orange-800 flex items-center justify-center shadow-sm">
                        <x-heroicon-o-document-text class="w-7 h-7 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $modalTitle ?? 'Modal Dokumen' }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Daftar lengkap dokumen berdasarkan status yang dipilih
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <x-heroicon-m-document-duplicate class="w-4 h-4" />
                    <span>{{ is_array($modalData) ? count($modalData) : 0 }} item</span>
                </div>
            </div>
        </x-slot>

        <div class="max-h-[70vh] overflow-y-auto custom-scrollbar">
            @if(is_array($modalData) && count($modalData) > 0)
            <div class="space-y-4">
                @foreach($modalData as $index => $document)
                @if(is_array($document))
                <div class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <h4 class="font-semibold text-lg text-gray-900 dark:text-white mb-3">
                        {{ $document['name'] ?? 'Dokumen #' . ($index + 1) }}
                    </h4>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Status:</span>
                            <span class="ml-2 font-medium">{{ $document['status'] ?? 'Unknown' }}</span>
                        </div>
                        @if(isset($document['project_name']))
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Proyek:</span>
                            <span class="ml-2 font-medium">{{ $document['project_name'] }}</span>
                        </div>
                        @endif
                        @if(isset($document['client_name']))
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Klien:</span>
                            <span class="ml-2 font-medium">{{ $document['client_name'] }}</span>
                        </div>
                        @endif
                        @if(isset($document['created_at']))
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Dibuat:</span>
                            <span class="ml-2 font-medium">{{ $document['created_at'] }}</span>
                        </div>
                        @endif
                    </div>

                    @if(isset($document['url']) && $document['url'] !== '#')
                    <div class="mt-4">
                        <a href="{{ $document['url'] }}" target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            <span>Lihat Detail</span>
                            <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4" />
                        </a>
                    </div>
                    @endif
                </div>
                @else
                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <p class="text-red-600 dark:text-red-400">Invalid document data at index {{ $index }}</p>
                </div>
                @endif
                @endforeach
            </div>
            @else
            <div class="text-center py-16">
                <div class="w-24 h-24 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-o-document-text class="w-12 h-12 text-gray-400" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    Tidak Ada Data Dokumen
                </h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                    {{ is_array($modalData) ? 'Tidak ada dokumen dengan status yang dipilih saat ini.' : 'Data dokumen tidak valid.' }}
                </p>
            </div>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Klik tombol "Lihat Detail" untuk melihat dokumen lengkap
                </p>
                <x-filament::button color="gray" wire:click="closeModal" size="lg">
                    <x-heroicon-m-x-mark class="w-4 h-4 mr-2" />
                    Tutup
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

</x-filament-panels::page>