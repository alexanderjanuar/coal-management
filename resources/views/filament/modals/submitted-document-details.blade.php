{{-- resources/views/filament/modals/submitted-document-details.blade.php --}}
<div class="space-y-6">
    {{-- Document Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-filament::section>
                <x-slot name="heading">
                    Informasi Dokumen
                </x-slot>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Nama Dokumen:</span>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $record->requiredDocument->name }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi:</span>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->requiredDocument->description ?: 'Tidak ada deskripsi' }}
                        </p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Wajib:</span>
                        <x-filament::badge 
                            :color="$record->requiredDocument->is_required ? 'success' : 'gray'"
                        >
                            {{ $record->requiredDocument->is_required ? 'Ya' : 'Tidak' }}
                        </x-filament::badge>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</span>
                        <x-filament::badge 
                            :color="match($record->status) {
                                'uploaded' => 'secondary',
                                'pending_review' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray'
                            }"
                        >
                            {{ match($record->status) {
                                'uploaded' => 'Diunggah',
                                'pending_review' => 'Menunggu Review',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => ucfirst($record->status)
                            } }}
                        </x-filament::badge>
                    </div>
                </div>
            </x-filament::section>
        </div>
        
        <div>
            <x-filament::section>
                <x-slot name="heading">
                    Informasi Proyek
                </x-slot>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Klien:</span>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->requiredDocument->projectStep->project->client->name }}
                        </p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Proyek:</span>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->requiredDocument->projectStep->project->name }}
                        </p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahap Proyek:</span>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $record->requiredDocument->projectStep->name }}
                        </p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Prioritas Proyek:</span>
                        <x-filament::badge 
                            :color="match($record->requiredDocument->projectStep->project->priority) {
                                'urgent' => 'danger',
                                'normal' => 'primary',
                                'low' => 'gray',
                                default => 'gray'
                            }"
                        >
                            {{ match($record->requiredDocument->projectStep->project->priority) {
                                'urgent' => 'Mendesak',
                                'normal' => 'Normal',
                                'low' => 'Rendah',
                                default => ucfirst($record->requiredDocument->projectStep->project->priority)
                            } }}
                        </x-filament::badge>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>
    
    {{-- Submission Details --}}
    <x-filament::section>
        <x-slot name="heading">
            Detail Pengiriman
        </x-slot>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dikirim oleh:</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $record->user->name }}</p>
            </div>
            
            <div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Waktu dikirim:</span>
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $record->created_at->format('d M Y H:i') }}
                </p>
            </div>
            
            <div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Terakhir diperbarui:</span>
                <p class="text-sm text-gray-900 dark:text-white">
                    {{ $record->updated_at->format('d M Y H:i') }}
                </p>
            </div>
            
            <div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Path file:</span>
                <p class="text-sm text-gray-900 dark:text-white font-mono break-all">
                    {{ $record->file_path }}
                </p>
            </div>
        </div>
    </x-filament::section>
    
    {{-- Notes and Rejection Reason --}}
    @if($record->notes || $record->rejection_reason)
        <x-filament::section>
            <x-slot name="heading">
                Informasi Tambahan
            </x-slot>
            
            @if($record->notes)
                <div class="mb-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan:</span>
                    <p class="text-sm text-gray-900 dark:text-white mt-1 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        {{ $record->notes }}
                    </p>
                </div>
            @endif
            
            @if($record->rejection_reason)
                <div>
                    <span class="text-sm font-medium text-red-700 dark:text-red-300">Alasan Penolakan:</span>
                    <p class="text-sm text-red-900 dark:text-red-100 mt-1 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        {{ $record->rejection_reason }}
                    </p>
                </div>
            @endif
        </x-filament::section>
    @endif
    
    {{-- Action Buttons --}}
    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
        <a 
            href="" 
            target="_blank"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-2" />
            Unduh File
        </a>
    </div>
</div>