<div class="p-4 space-y-6">
    <!-- Header -->
    <div>
        <!-- Header Content -->
        <div class="flex items-center justify-between">
            <!-- Left Side: Title & Count -->
            <div class="flex items-center gap-6">
                <h2 class="text-xl font-semibold text-gray-900">Project Documents</h2>
                <div class="px-3 py-1 rounded-full bg-amber-50 border border-amber-100">
                    <div class="flex items-center gap-2">
                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                        <span class="text-sm font-medium text-amber-700">{{
                            $steps->pluck('requiredDocuments')->flatten()->count() }} Total</span>
                    </div>
                </div>
            </div>

            <!-- Right Side: Status Counts -->
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    <span class="text-sm text-gray-600 font-medium">{{
                        $steps->pluck('requiredDocuments')->flatten()->where('status', 'approved')->count() }}
                        Approved</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                    <span class="text-sm text-gray-600 font-medium">{{
                        $steps->pluck('requiredDocuments')->flatten()->where('status', 'pending_review')->count() }}
                        Pending</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                    <span class="text-sm text-gray-600 font-medium">{{
                        $steps->pluck('requiredDocuments')->flatten()->where('status', 'rejected')->count() }}
                        Rejected</span>
                </div>
            </div>
        </div>

        <!-- Divider - with gradient fade effect -->
        <div class="relative mt-6">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center">
                <div class="bg-white px-3">
                    <div class="h-5 w-5">
                        <x-heroicon-m-document class="w-5 h-5 text-gray-400" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline View -->
    <div class="relative space-y-4">
        <!-- Vertical Line -->
        <div class="absolute left-[17px] top-0 h-full w-0.5 bg-gradient-to-b from-amber-200 via-amber-100 to-amber-50">
        </div>

        @forelse($steps as $step)
        @if($step->requiredDocuments->isNotEmpty())
        @php
        $approvedCount = $step->requiredDocuments->where('status', 'approved')->count();
        $totalDocs = $step->requiredDocuments->count();
        $stepStatus = $approvedCount === $totalDocs ? 'completed' :
        ($approvedCount > 0 ? 'in_progress' : 'pending');
        @endphp

        <div class="relative group/step">
            <!-- Step Header -->
            <div class="ml-10 mb-3">
                <div class="flex items-center gap-3">
                    <!-- Step Number with Dynamic Color -->
                    <div class="absolute left-0 w-[35px] h-[35px] rounded-full flex items-center justify-center transition-all duration-300
                                {{ match($stepStatus) {
                                    'completed' => 'bg-green-500 border-0',
                                    'in_progress' => 'bg-amber-500 border-0',
                                    default => 'bg-white border-2 border-gray-300'
                                } }}">
                        @if($stepStatus === 'completed')
                        <x-heroicon-m-check class="w-5 h-5 text-white" />
                        @else
                        <span
                            class="text-sm font-medium {{ $stepStatus === 'in_progress' ? 'text-white' : 'text-gray-600' }}">
                            {{ $step->order }}
                        </span>
                        @endif
                    </div>

                    <h3 class="text-base font-medium text-gray-900">{{ $step->name }}</h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium 
                            {{ match($stepStatus) {
                                'completed' => 'bg-green-50 text-green-700',
                                'in_progress' => 'bg-amber-50 text-amber-700',
                                default => 'bg-gray-50 text-gray-600'
                            } }}">
                        {{ $step->requiredDocuments->count() }} documents
                    </span>
                </div>
            </div>

            <!-- Documents List -->
            <div class="ml-10 space-y-3">
                @foreach($step->requiredDocuments as $document)
                <div wire:click="viewDocument({{ $document->id }})"
                    x-on:click="$dispatch('open-modal', { id: 'preview-document-modal' })" class="group/doc bg-white rounded-lg border transition-all duration-300 hover:scale-[1.01] 
                            {{ match($document->status) {
                                'approved' => 'border-green-200 hover:border-green-300 hover:shadow-[0_0_15px_rgba(34,197,94,0.1)]',
                                'pending_review' => 'border-amber-200 hover:border-amber-300 hover:shadow-[0_0_15px_rgba(245,158,11,0.1)]',
                                'rejected' => 'border-red-200 hover:border-red-300 hover:shadow-[0_0_15px_rgba(239,68,68,0.1)]',
                                default => 'border-gray-200 hover:border-gray-300'
                            } }}">
                    <div class="p-4">
                        <!-- Document Header -->
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3 flex-1">
                                <!-- Document Icon -->
                                <div class="relative flex-shrink-0">
                                    <div class="w-10 h-10 rounded-lg transition-colors duration-300 flex items-center justify-center
                                                {{ match($document->status) {
                                                    'approved' => 'bg-green-50 group-hover/doc:bg-green-100',
                                                    'pending_review' => 'bg-amber-50 group-hover/doc:bg-amber-100',
                                                    'rejected' => 'bg-red-50 group-hover/doc:bg-red-100',
                                                    default => 'bg-gray-50 group-hover/doc:bg-gray-100'
                                                } }}">
                                        @if($document->submittedDocuments->count() > 0)
                                        <x-heroicon-o-document-text class="w-5 h-5 {{ match($document->status) {
                                                        'approved' => 'text-green-600',
                                                        'pending_review' => 'text-amber-600',
                                                        'rejected' => 'text-red-600',
                                                        default => 'text-gray-600'
                                                    } }}" />
                                        @else
                                        <x-heroicon-o-document-plus class="w-5 h-5 text-gray-400" />
                                        @endif
                                    </div>

                                    <!-- Status Dot -->
                                    <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full ring-2 ring-white
                                                {{ match($document->status) {
                                                    'approved' => 'bg-green-500',
                                                    'pending_review' => 'bg-amber-500 animate-pulse',
                                                    'rejected' => 'bg-red-500',
                                                    default => 'bg-gray-400'
                                                } }}">
                                    </span>
                                </div>

                                <!-- Document Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $document->name }}</h4>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium 
                                                    {{ match($document->status) {
                                                        'approved' => 'bg-green-50 text-green-700',
                                                        'pending_review' => 'bg-amber-50 text-amber-700',
                                                        'rejected' => 'bg-red-50 text-red-700',
                                                        default => 'bg-gray-50 text-gray-600'
                                                    } }}">
                                            {{ ucfirst(str_replace('_', ' ', $document->status ?? 'Not Submitted')) }}
                                        </span>
                                    </div>
                                    @if($document->description)
                                    <p class="mt-1 text-xs text-gray-500">{{ $document->description }}</p>
                                    @else
                                    <p class="mt-1 text-xs text-gray-400 italic">No description available</p>
                                    @endif
                                </div>

                                <!-- Action Button -->
                                <button type="button"
                                    x-on:click="$dispatch('open-modal', { id: 'preview-document-modal' })"
                                    class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-300
                                            {{ match($document->status) {
                                                'approved' => 'text-green-600 hover:bg-green-50',
                                                'pending_review' => 'text-amber-600 hover:bg-amber-50',
                                                'rejected' => 'text-red-600 hover:bg-red-50',
                                                default => 'text-gray-600 hover:bg-gray-50'
                                            } }}">
                                    View Details
                                    <x-heroicon-m-arrow-right
                                        class="w-3 h-3 transition-transform group-hover/doc:translate-x-0.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Document Footer -->
                        <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <x-heroicon-m-paper-clip class="w-4 h-4" />
                                {{ $document->submittedDocuments->count() }} submission(s)
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @empty
        <div class="text-center py-12">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto">
                <x-heroicon-o-document-text class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="mt-4 text-sm font-medium text-gray-900">No documents found</h3>
            <p class="mt-1 text-sm text-gray-500">No documents are required for this project yet.</p>
        </div>
        @endforelse
    </div>


    <x-filament::modal id="preview-document-modal" width="7xl">
        <div class="flex items-center justify-between w-full gap-4">
            <!-- Left side - Document info -->
            <div class="flex items-center gap-4 min-w-0">
                <!-- Icon -->
                <div
                    class="flex-shrink-0 w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center ring-1 ring-primary-100">
                    @if($fileType === 'pdf')
                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-600" />
                    @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                    <x-heroicon-o-photo class="w-6 h-6 text-primary-600" />
                    @else
                    <x-heroicon-o-document class="w-6 h-6 text-primary-600" />
                    @endif
                </div>

                <!-- Document details -->
                @if($previewingDocument)
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                        {{ basename($previewingDocument->file_path) }}
                    </h3>
                    <div class="flex items-center gap-3 text-sm text-gray-500">
                        <span>Uploaded {{ $previewingDocument->created_at->diffForHumans() }}</span>
                        @if($totalDocuments > 1)
                        <span class="flex items-center gap-1">
                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                            <span>{{ $currentIndex + 1 }} of {{ $totalDocuments }}</span>
                        </span>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Right side - Actions -->
            @if($previewingDocument)
            <div class="flex-shrink-0 flex items-center gap-3">
                <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" color="gray" size="sm">
                    <div class="flex items-center gap-2">
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                        <span>Download</span>
                    </div>
                </x-filament::button>
            </div>
            @endif
        </div>

        <div class="relative p-6">
            @if($previewUrl)
            <div class="rounded-xl overflow-hidden bg-gray-50 ring-1 ring-gray-200">
                @if($fileType === 'pdf')
                <div class="w-full h-[calc(100vh-16rem)] bg-gray-50">
                    <iframe src="{{ $previewUrl }}" class="w-full h-full rounded-lg" frameborder="0"></iframe>
                </div>
                @elseif(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']))
                <div class="relative aspect-video flex items-center justify-center bg-gray-50">
                    <img src="{{ $previewUrl }}" alt="Document Preview"
                        class="max-w-full max-h-[calc(100vh-16rem)] object-contain rounded-lg shadow-sm">
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-16">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Preview not available</h3>
                    <p class="text-sm text-gray-500 mb-4">This file type cannot be previewed directly in the browser</p>
                    <x-filament::button wire:click="downloadDocument({{ $previewingDocument->id }})" size="sm">
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4 mr-2" />
                        Download to View
                    </x-filament::button>
                </div>
                @endif
            </div>

            @if($totalDocuments > 1)
            <div
                class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-gray-900/80 rounded-full p-2 backdrop-blur-sm">
                <button wire:click="previousDocument" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                    <x-heroicon-m-chevron-left class="w-5 h-5 text-white" />
                </button>

                <div class="w-px h-5 bg-gray-600"></div>

                <button wire:click="nextDocument" class="p-2 hover:bg-white/20 rounded-full transition-colors">
                    <x-heroicon-m-chevron-right class="w-5 h-5 text-white" />
                </button>
            </div>
            @endif
            @else
            <div class="flex flex-col items-center justify-center py-16">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center animate-pulse">
                    <x-heroicon-o-document class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Loading preview...</h3>
            </div>
            @endif
        </div>
    </x-filament::modal>
</div>