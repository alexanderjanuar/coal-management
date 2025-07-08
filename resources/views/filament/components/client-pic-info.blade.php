{{-- File: resources/views/filament/components/client-pic-info.blade.php --}}
<div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-4 mb-6">
    <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                </svg>
            </div>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200">Person In Charge (PIC)</h3>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Name:</span>
                    <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $record->pic->name }}</span>
                </div>
                <div>
                    <span class="text-blue-700 dark:text-blue-300 font-medium">NIK:</span>
                    <span class="ml-2 text-blue-900 dark:text-blue-100 font-mono">{{ $record->pic->nik }}</span>
                </div>
                @if($record->pic->email)
                <div>
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Email:</span>
                    <span class="ml-2 text-blue-900 dark:text-blue-100">{{ $record->pic->email }}</span>
                </div>
                @endif
                <div>
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Status:</span>
                    @if($record->pic->status === 'active')
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            Active
                        </span>
                    @else
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>