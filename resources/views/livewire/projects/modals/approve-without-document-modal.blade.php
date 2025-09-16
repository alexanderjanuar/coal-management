<x-filament::modal id="approve-without-document-{{ $document->id }}" width="md">
    <x-slot name="heading">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center">
                <x-heroicon-m-document-check class="w-5 h-5 text-warning-600 dark:text-warning-400" />
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Approve Without Document
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $document->name }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <!-- Warning Notice -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                <div>
                    <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                        Perhatian
                    </h4>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                        Anda akan menyetujui dokumen ini tanpa ada file yang diupload. 
                        Pastikan untuk memberikan alasan yang jelas mengapa dokumen ini disetujui tanpa upload.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form wire:submit="submit">
            {{ $this->form }}
            
            <div class="flex items-center justify-end gap-3 mt-6">
                <x-filament::button 
                    type="button"
                    x-on:click="$dispatch('close-modal', { id: 'approve-without-document-{{ $document->id }}' })"
                    color="gray" 
                    size="sm">
                    Cancel
                </x-filament::button>
                
                <x-filament::button 
                    type="submit" 
                    color="warning" 
                    size="sm"
                    wire:loading.attr="disabled">
                    <div class="flex items-center gap-2">
                        <x-heroicon-m-check class="w-4 h-4" />
                        <span wire:loading.remove>Approve Without Document</span>
                        <span wire:loading>Processing...</span>
                    </div>
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament::modal>