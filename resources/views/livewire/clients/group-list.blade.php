<div class="space-y-5">
    {{-- ── Toolbar: cari + filter status + jumlah ── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
            </svg>
            <input type="search" wire:model.live.debounce.300ms="search"
                   placeholder="Cari grup, kontak, email..."
                   class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="statusFilter"
                    class="rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">Semua status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
            </select>

            <span class="shrink-0 text-xs font-medium tabular-nums text-gray-500 dark:text-gray-400">
                {{ $groups->count() }} grup
            </span>
        </div>
    </div>

    {{-- ── Daftar panel grup (kartu side-by-side) ── --}}
    @forelse($groups as $group)
        <x-group-panel
            :group="$group"
            :manageUrl="\App\Filament\Resources\ClientGroupResource::getUrl('view', ['record' => $group])"
            :showAddClient="true" />
    @empty
        <div class="flex flex-col items-center justify-center rounded-2xl bg-white px-6 py-16 text-center shadow-lg shadow-gray-900/[0.07] ring-1 ring-gray-950/5 dark:bg-gray-900 dark:shadow-none dark:ring-white/10">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/>
                </svg>
            </div>
            @if($hasFilters)
                <p class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Tidak ada grup yang cocok</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Coba ubah kata kunci atau filter status.</p>
                <button type="button" wire:click="clearFilters"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                    Reset filter
                </button>
            @else
                <p class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Belum ada grup client</p>
                <p class="mt-1 max-w-xs text-xs leading-relaxed text-gray-500 dark:text-gray-400">Buat grup untuk mengelompokkan client yang terafiliasi dalam satu grup usaha.</p>
            @endif
        </div>
    @endforelse

    {{-- ── Modal: Tambah Client ke grup ── --}}
    <x-filament::modal id="add-client-to-group" width="lg" icon="heroicon-o-user-plus">
        <x-slot name="heading">
            Tambah Client ke {{ $this->addGroup?->name ?? 'Grup' }}
        </x-slot>
        <x-slot name="description">
            Pilih client yang belum tergabung dalam grup mana pun.
        </x-slot>

        <div class="space-y-3">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/>
                </svg>
                <input type="search" wire:model.live.debounce.300ms="clientSearch"
                       placeholder="Cari nama atau NPWP..."
                       class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
            </div>

            <div class="max-h-72 divide-y divide-gray-100 overflow-y-auto rounded-lg border border-gray-200 dark:divide-gray-800 dark:border-gray-700">
                @forelse($this->availableClients as $ac)
                    <label class="flex cursor-pointer items-center gap-3 px-3 py-2.5 transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                        <input type="checkbox" value="{{ $ac->id }}" wire:model.live="newClientIds"
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800">
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ac->name }}</span>
                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ $ac->client_type }}{{ $ac->NPWP ? ' · ' . $ac->NPWP : '' }}</span>
                        </span>
                    </label>
                @empty
                    <p class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ $clientSearch !== '' ? 'Tidak ada client yang cocok.' : 'Semua client sudah tergabung dalam grup.' }}
                    </p>
                @endforelse
            </div>
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
            <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'add-client-to-group' })">
                Batal
            </x-filament::button>
            <x-filament::button wire:click="saveAddClient" wire:loading.attr="disabled" :disabled="empty($newClientIds)">
                Tambahkan{{ count($newClientIds) ? ' (' . count($newClientIds) . ')' : '' }}
            </x-filament::button>
        </div>
    </x-filament::modal>
</div>
