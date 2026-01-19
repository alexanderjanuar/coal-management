<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/50 rounded-xl flex items-center justify-center">
                <x-heroicon-o-user-group class="w-6 h-6 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tim Proyek</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ count($users) }} anggota</p>
            </div>
        </div>
    </div>

    <div class="divide-y divide-gray-50 dark:divide-gray-700">
        @foreach($users as $user)
        <div class="p-5 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all duration-200">
            <div class="flex items-start gap-4">
                <div class="relative flex-shrink-0 group">
                    <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}"
                        class="w-12 h-12 rounded-xl object-cover ring-2 ring-white dark:ring-gray-700 shadow-sm transition-transform group-hover:scale-105">
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</p>
                        </div>

                        @if(!auth()->user()->hasRole('staff'))
                        <x-filament::dropdown placement="bottom-end">
                            <x-slot name="trigger">
                                <button class="p-2 text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 rounded-lg transition-colors">
                                    <x-heroicon-m-ellipsis-horizontal class="w-5 h-5" />
                                </button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item
                                    x-on:click="$dispatch('open-modal', { id: 'confirm-remove-{{ $user['id'] }}' })"
                                    icon="heroicon-m-trash" color="danger">
                                    Hapus
                                </x-filament::dropdown.list.item>
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-4 mt-3">
                        <div class="inline-flex items-center gap-2">
                            <div class="p-1.5 rounded-full bg-gray-50 dark:bg-gray-700">
                                <x-heroicon-m-chat-bubble-left-right class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $user['comments_count'] }} komentar</span>
                        </div>

                        <div class="inline-flex items-center gap-2">
                            <div class="p-1.5 rounded-full bg-gray-50 dark:bg-gray-700">
                                <x-heroicon-m-document class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                            </div>
                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $user['documents_count'] }} dokumen</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-filament::modal id="confirm-remove-{{ $user['id'] }}">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hapus Anggota Tim</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus {{ $user['name'] }} dari proyek ini?
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button color="gray"
                        x-on:click="$dispatch('close-modal', { id: 'confirm-remove-{{ $user['id'] }}' })">
                        Batal
                    </x-filament::button>
                    <x-filament::button color="danger" wire:click="removeMember({{ $user['id'] }})">
                        Hapus
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>
        @endforeach
    </div>

    @if(!auth()->user()->hasRole('staff'))
    <div x-data="{ open: false }" class="border-t border-gray-100 dark:border-gray-700">
        <button @click="open = !open"
            class="w-full px-6 py-4 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center justify-center gap-2 transition-colors">
            <x-heroicon-m-plus-circle class="w-5 h-5" />
            <span>Tambah Anggota Tim</span>
        </button>

        <div x-show="open" x-transition class="border-t border-gray-100 dark:border-gray-700 p-6 bg-gray-50/30 dark:bg-gray-800/50">
            <div class="space-y-6">
                <div class="relative">
                    <x-filament::input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pengguna..." />
                </div>

                <div class="space-y-2 max-h-[400px] overflow-y-auto">
                    @forelse($availableUsers as $availableUser)
                    <div class="group p-4 bg-gray-50 dark:bg-gray-700/50 hover:bg-white dark:hover:bg-gray-700 rounded-xl transition-all duration-200 border border-gray-100 dark:border-gray-600">
                        <div class="flex items-center gap-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($availableUser->name) }}"
                                class="w-10 h-10 rounded-lg object-cover ring-1 ring-gray-200 dark:ring-gray-600">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $availableUser->name }}</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $availableUser->email }}</p>
                                    </div>

                                    <x-filament::button wire:click="addUserToProject({{ $availableUser->id }})"
                                        size="sm" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                        Tambah
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <x-heroicon-o-user-plus class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" />
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mt-4">Tidak ada pengguna ditemukan</h4>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(empty($users))
    <div class="p-12 text-center">
        <x-heroicon-o-user-group class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" />
        <h3 class="text-base font-medium text-gray-900 dark:text-white mt-4">Belum ada anggota tim</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Mulai bangun tim Anda dengan menambahkan anggota</p>
    </div>
    @endif
</div>
