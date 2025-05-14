@props(['filePath'])

@php
    // Handle file path, which could be an array from Filament's FileUpload component
    $path = null;
    
    if (is_array($filePath)) {
        // For create/edit operations in Filament, it comes as an array
        if (isset($filePath[0])) {
            $path = $filePath[0];
        }
    } else {
        // For display operations, it may come as a string
        $path = $filePath;
    }
    
    if (!$path) {
        return;
    }
    
    // Generate the full URL
    $fileUrl = $path;
    if (!str_starts_with($path, 'http')) {
        $fileUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
    
    // Get file extension to determine if it's an image or PDF
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $isPdf = strtolower($extension) === 'pdf';
    
    // Get file name for display
    $fileName = basename($path);
    
    // Get file size if available
    $fileSize = '';
    try {
        $sizeInBytes = \Illuminate\Support\Facades\Storage::disk('public')->size($path);
        $fileSize = $sizeInBytes < 1024 * 1024
            ? round($sizeInBytes / 1024, 2) . ' KB'
            : round($sizeInBytes / (1024 * 1024), 2) . ' MB';
    } catch (\Exception $e) {
        // Ignore any errors
    }
@endphp

<div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between bg-gray-50 p-3 border-b border-gray-200">
        <div class="flex items-center gap-2">
            @if($isPdf)
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V7.875L13.125 1.5H5.625zM7 9.5a.5.5 0 01.5-.5h3a.5.5 0 010 1h-3a.5.5 0 01-.5-.5zm0 3a.5.5 0 01.5-.5h9a.5.5 0 010 1h-9a.5.5 0 01-.5-.5zm0 3a.5.5 0 01.5-.5h9a.5.5 0 010 1h-9a.5.5 0 01-.5-.5z" clip-rule="evenodd" />
                    </svg>
                </div>
            @elseif($isImage)
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                    </svg>
                </div>
            @else
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V7.875L13.125 1.5H5.625zM7 9.5a.5.5 0 01.5-.5h3a.5.5 0 010 1h-3a.5.5 0 01-.5-.5zm0 3a.5.5 0 01.5-.5h9a.5.5 0 010 1h-9a.5.5 0 01-.5-.5zm0 3a.5.5 0 01.5-.5h9a.5.5 0 010 1h-9a.5.5 0 01-.5-.5z" clip-rule="evenodd" />
                    </svg>
                </div>
            @endif
            
            <div>
                <p class="font-medium text-sm text-gray-900 truncate max-w-sm">{{ $fileName }}</p>
                @if($fileSize)
                    <p class="text-xs text-gray-500">{{ $fileSize }}</p>
                @endif
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                    <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                </svg>
            </a>
            <a href="{{ $fileUrl }}" download class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary-50 text-primary-600 hover:bg-primary-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </a>
        </div>
    </div>

    <div class="relative overflow-hidden" style="height: 450px;">
        @if($isImage)
            <div class="flex items-center justify-center h-full p-4 bg-gray-50">
                <img src="{{ $fileUrl }}" alt="{{ $fileName }}" class="max-w-full max-h-full object-contain rounded shadow" />
            </div>
        @elseif($isPdf)
            <iframe src="{{ $fileUrl }}#toolbar=1&view=FitH" class="w-full h-full border-0" title="{{ $fileName }}" allow="fullscreen"></iframe>
        @else
            <div class="flex flex-col items-center justify-center h-full p-4 bg-gray-50 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-700 font-medium mb-2">Jenis file ini tidak dapat ditampilkan sebagai preview</p>
                <p class="text-gray-500 text-sm mb-4">File dapat diunduh untuk dilihat secara langsung</p>
                <a href="{{ $fileUrl }}" target="_blank" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md text-sm font-medium transition-colors">
                    Buka File
                </a>
            </div>
        @endif
    </div>
</div>