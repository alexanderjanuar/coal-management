{{-- resources/views/filament/modals/document-preview.blade.php --}}
<div class="h-full">
    @php
        $extension = strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION));
        $fileUrl = Storage::url($record->file_path);
    @endphp

    <!-- Header with file info -->
    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-lg text-gray-900 dark:text-white truncate">
                    {{ $record->original_filename ?? basename($record->file_path) }}
                </h3>
                <div class="flex items-center gap-4 mt-2 text-sm text-gray-600 dark:text-gray-300">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ $record->user->name }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $record->created_at->format('M d, Y \a\t H:i') }}
                    </span>
                    @if(Storage::disk('public')->exists($record->file_path))
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            {{ number_format(Storage::disk('public')->size($record->file_path) / 1024, 2) }} KB
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Action buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ $fileUrl }}" 
                   target="_blank" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Open in New Tab
                </a>
                
                <a href="{{ $fileUrl }}" 
                   download="{{ $record->original_filename ?? basename($record->file_path) }}"
                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    </div>

    <!-- Preview container -->
    <div class="border rounded-lg overflow-hidden bg-white dark:bg-gray-900" style="height: calc(100vh - 200px); min-height: 600px;">
        @if($extension === 'pdf')
            <iframe src="{{ $fileUrl }}" 
                    class="w-full h-full border-0" 
                    style="min-height: 600px;"
                    title="PDF Preview">
                <div class="flex items-center justify-center h-32 bg-gray-100 dark:bg-gray-800">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Your browser does not support PDF preview</p>
                        <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 hover:text-blue-500 text-sm underline">
                            Click here to open the PDF
                        </a>
                    </div>
                </div>
            </iframe>
            
        @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
            <div class="h-full flex items-center justify-center bg-gray-50 dark:bg-gray-800 p-4">
                <img src="{{ $fileUrl }}" 
                     alt="Document preview" 
                     class="max-w-full max-h-full object-contain rounded-lg shadow-lg"
                     style="max-height: calc(100vh - 250px);">
            </div>
            
        @else
            <div class="flex items-center justify-center h-full bg-gray-50 dark:bg-gray-800">
                <div class="text-center max-w-md mx-auto p-8">
                    <!-- File type icon -->
                    <div class="mx-auto mb-4">
                        @if(in_array($extension, ['doc', 'docx']))
                            <svg class="mx-auto h-16 w-16 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                            </svg>
                        @elseif(in_array($extension, ['xls', 'xlsx']))
                            <svg class="mx-auto h-16 w-16 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                            </svg>
                        @else
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        @endif
                    </div>
                    
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        {{ strtoupper($extension) }} Document
                    </h3>
                    
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Preview not available for this file type
                    </p>
                    
                    <div class="space-y-2">
                        <a href="{{ $fileUrl }}" 
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Open in New Tab
                        </a>
                        
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            File will open in your default application
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer info -->
    <div class="mt-4 text-xs text-gray-500 dark:text-gray-400 text-center border-t pt-3">
        <div class="flex items-center justify-center gap-4">
            <span>File Type: {{ strtoupper($extension) }}</span>
            @if(Storage::disk('public')->exists($record->file_path))
                <span>•</span>
                <span>Size: {{ number_format(Storage::disk('public')->size($record->file_path) / 1024, 2) }} KB</span>
            @endif
            <span>•</span>
            <span>Path: {{ $record->file_path }}</span>
        </div>
    </div>
</div>

<style>
    /* Custom scrollbar for the modal */
    .fi-modal-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .fi-modal-content::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .fi-modal-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .fi-modal-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    /* Dark mode scrollbar */
    .dark .fi-modal-content::-webkit-scrollbar-track {
        background: #374151;
    }
    
    .dark .fi-modal-content::-webkit-scrollbar-thumb {
        background: #6b7280;
    }
    
    .dark .fi-modal-content::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>