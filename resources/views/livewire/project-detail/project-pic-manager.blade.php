<div class="space-y-6">
    <style>
        /* Custom scrollbar for webkit browsers */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        /* Dark mode scrollbar */
        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #1f2937;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        /* Avatar ring animation */
        .avatar-ring {
            transition: all 0.2s ease-out;
        }

        .user-selection-item:hover .avatar-ring {
            ring-width: 3px;
            ring-color: rgb(245, 158, 11);
        }
    </style>
    <!-- Header -->
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-info-100 dark:bg-info-900 flex items-center justify-center">
            <x-heroicon-o-user class="w-6 h-6 text-info-600 dark:text-info-400" />
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                Penanggung Jawab (PIC)
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Kelola orang yang bertanggung jawab untuk proyek ini
            </p>
        </div>
    </div>

    <!-- Current PIC -->
    @if($project->pic)
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($project->pic->name) }}&background=f59e0b&color=fff"
                    alt="{{ $project->pic->name }}"
                    class="w-12 h-12 rounded-full object-cover ring-2 ring-white dark:ring-gray-800">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">
                        {{ $project->pic->name }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $project->pic->email }}
                    </p>
                    @if($project->pic->userClients->count() > 0)
                    <div class="flex items-center gap-1 mt-1">
                        <x-heroicon-m-building-office-2 class="w-3 h-3 text-gray-400" />
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Ditugaskan ke {{ $project->pic->userClients->count() }} klien
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            @if(!auth()->user()->hasRole(['staff', 'client']))
            <div class="flex items-center gap-2">
                <button wire:click="openChangePicModal"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900 hover:bg-amber-100 dark:hover:bg-amber-800 rounded-lg transition-colors">
                    <x-heroicon-m-arrow-path class="w-4 h-4" />
                    Ubah
                </button>
                <button wire:click="removePic" wire:confirm="Apakah Anda yakin ingin menghapus PIC dari proyek ini?"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900 hover:bg-red-100 dark:hover:bg-red-800 rounded-lg transition-colors">
                    <x-heroicon-m-x-mark class="w-4 h-4" />
                    Hapus
                </button>
            </div>
            @endif
        </div>
    </div>
    @else
    <!-- No PIC Assigned -->
    <div class="text-center py-8">
        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
            <x-heroicon-o-user-plus class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            Belum Ada PIC yang Ditugaskan
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            Proyek ini belum memiliki Penanggung Jawab yang ditugaskan.
        </p>

        @if(!auth()->user()->hasRole(['staff', 'client']))
        <button wire:click="openChangePicModal"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition-colors">
            <x-heroicon-m-user-plus class="w-4 h-4" />
            Tugaskan PIC
        </button>
        @endif
    </div>
    @endif

    <!-- PIC Responsibilities -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">
            Tanggung Jawab PIC
        </h4>
        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <li class="flex items-start gap-2">
                <x-heroicon-m-check-circle class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                <span>Akuntabilitas keseluruhan proyek dan pemantauan kemajuan</span>
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-m-check-circle class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                <span>Koordinasi anggota tim dan penugasan tugas</span>
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-m-check-circle class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                <span>Meninjau dan menyetujui dokumen yang disubmit</span>
            </li>
            <li class="flex items-start gap-2">
                <x-heroicon-m-check-circle class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                <span>Mengkomunikasikan status proyek kepada stakeholder</span>
            </li>
        </ul>
    </div>

    <!-- Change PIC Modal -->
    @if($showChangePicModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Backdrop -->
            <div wire:click="closeChangePicModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity">
            </div>

            <!-- Modal panel -->
            <div
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-900 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900 flex items-center justify-center">
                        <x-heroicon-o-arrow-path class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $project->pic ? 'Ubah PIC' : 'Tugaskan PIC' }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Pilih pengguna dari {{ $project->client->name }} untuk menjadi Penanggung Jawab
                        </p>
                    </div>
                </div>

                <!-- Current PIC (if exists) -->
                @if($project->pic)
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($project->pic->name) }}&background=6b7280&color=fff"
                            alt="{{ $project->pic->name }}"
                            class="w-10 h-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-800">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                PIC Saat Ini: {{ $project->pic->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $project->pic->email }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- User Selection -->
                <div class="space-y-3">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                        Pengguna yang Tersedia untuk {{ $project->client->name }}
                    </h3>

                    @if($this->availableUsers->count() > 0)
                    <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                        @foreach($this->availableUsers as $user)
                        <button wire:click="assignPic({{ $user->id }})"
                            @class([ 'w-full flex items-center gap-3 p-3 text-left rounded-lg transition-all duration-200'
                            , 'bg-info-50 dark:bg-info-900 border-2 border-info-200 dark:border-info-700'=>
                            $project->pic?->id === $user->id,
                            'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border-2 border-gray-200
                            dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' => $project->pic?->id
                            !== $user->id
                            ])
                            >
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f59e0b&color=fff"
                                alt="{{ $user->name }}"
                                class="w-10 h-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 avatar-ring">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $user->name }}
                                    @if($project->pic?->id === $user->id)
                                    <span
                                        class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-info-700 dark:text-info-300 bg-info-100 dark:bg-info-800 rounded-full">
                                        <x-heroicon-m-check-circle class="w-3 h-3" />
                                        PIC Saat Ini
                                    </span>
                                    @endif
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->email }}
                                </p>

                                @if($user->roles->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                    @foreach($user->roles as $role)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                                        {{ $role->name }}
                                    </span>
                                    @endforeach
                                </div>
                                @endif
                            </div>

                            @if($project->pic?->id !== $user->id)
                            <div class="flex items-center">
                                <x-heroicon-m-arrow-right class="w-5 h-5 text-gray-400" />
                            </div>
                            @endif
                        </button>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <div
                            class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mx-auto mb-4">
                            <x-heroicon-o-user-group class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Tidak Ada Pengguna yang Tersedia
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Belum ada pengguna yang ditugaskan ke {{ $project->client->name }}.
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                    <button wire:click="closeChangePicModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>