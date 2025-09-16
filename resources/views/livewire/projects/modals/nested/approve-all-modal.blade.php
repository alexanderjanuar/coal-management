<x-slot name="header">
    <div class="flex items-center gap-3">
        <div
            class="flex-shrink-0 w-10 h-10 rounded-full bg-green-50 dark:bg-green-900 flex items-center justify-center">
            <x-heroicon-o-check-badge class="w-5 h-5 text-green-500 dark:text-green-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
            Setujui Semua Dokumen
        </h3>
    </div>
</x-slot>

<div class="space-y-3">
    <p class="text-sm text-gray-500 dark:text-gray-400">
        Tindakan ini akan menyetujui semua dokumen terlepas dari status mereka saat ini. Apakah Anda yakin ingin
        melanjutkan?
    </p>
    <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-700/50 rounded-lg p-3">
        <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
            Catatan: Tindakan ini tidak dapat dibatalkan.
        </p>
    </div>
</div>

<x-slot name="footer">
    <div class="flex justify-end gap-2">
        <x-filament::button x-on:click="$dispatch('close-modal', { id: 'confirm-approve-all' })" color="gray" size="sm">
            Batal
        </x-filament::button>

        <x-filament::button wire:click="approveAllDocuments" color="success" size="sm">
            <div class="flex items-center gap-1">
                <x-heroicon-m-check-badge class="w-4 h-4" />
                <span>Setujui Semua</span>
            </div>
        </x-filament::button>
    </div>
</x-slot>