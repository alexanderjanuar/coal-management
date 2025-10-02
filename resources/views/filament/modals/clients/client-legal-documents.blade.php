<div class="space-y-6">
    {{-- Stats Summary --}}
    <div class="grid grid-cols-4 gap-4">
        @php
        $stats = $client->getLegalDocumentsStats();
        @endphp

        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Total Dokumen SOP</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['total_documents'] }}</div>
        </div>

        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Sudah Upload</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['uploaded'] }}</div>
        </div>

        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Belum Upload</div>
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['not_uploaded'] }}</div>
        </div>

        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
            <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Dokumen Custom</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                {{ $uploadedDocuments->whereNull('sop_legal_document_id')->count() }}
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div>
        <div class="flex justify-between mb-2">
            <span class="text-sm font-medium">Progress Dokumen Wajib</span>
            <span class="text-sm font-medium">{{ $stats['completion_percentage'] }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
            <div class="bg-green-600 h-2.5 rounded-full transition-all"
                style="width: {{ $stats['completion_percentage'] }}%"></div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ tab: 'sop' }" class="space-y-4">
        {{-- Tab Headers --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button @click="tab = 'sop'"
                    :class="tab === 'sop' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Dokumen SOP
                </button>
                <button @click="tab = 'uploaded'"
                    :class="tab === 'uploaded' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Dokumen Terupload ({{ $uploadedDocuments->count() }})
                </button>
            </nav>
        </div>

        {{-- Tab: SOP Checklist --}}
        <div x-show="tab === 'sop'" class="space-y-4">
            @php
            $groupedChecklist = $checklist->groupBy('category');
            @endphp

            @foreach(['Dasar', 'PKP', 'Pendukung'] as $category)
            @if($groupedChecklist->has($category))
            <div>
                <h3 class="text-base font-semibold mb-3 flex items-center gap-2">
                    @if($category === 'Dasar')
                    <span class="text-blue-600 dark:text-blue-400">📄</span>
                    @elseif($category === 'PKP')
                    <span class="text-green-600 dark:text-green-400">✓</span>
                    @else
                    <span class="text-gray-600 dark:text-gray-400">📎</span>
                    @endif
                    {{ $category }}
                </h3>

                <div class="space-y-2">
                    @foreach($groupedChecklist[$category] as $item)
                    <div
                        class="flex items-start gap-3 p-3 rounded-lg border {{ $item['is_uploaded'] ? 'bg-green-50 dark:bg-green-900/10 border-green-200 dark:border-green-800' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700' }}">
                        {{-- Checkbox --}}
                        <div class="flex-shrink-0 mt-1">
                            @if($item['is_uploaded'])
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2" />
                            </svg>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium">{{ $item['name'] }}</span>
                                @if($item['is_required'])
                                <span
                                    class="px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    Wajib
                                </span>
                                @endif
                            </div>

                            @if($item['description'])
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item['description'] }}</p>
                            @endif

                            @if($item['is_uploaded'] && $item['uploaded_document'])
                            <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                <span>✓ {{ $item['uploaded_document']->original_filename }}</span>
                                <span>{{ $item['uploaded_at']->format('d M Y') }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Action Button --}}
                        <div class="flex-shrink-0">
                            @if($item['is_uploaded'])
                            <a href="{{ \Storage::disk('public')->url($item['file_path']) }}" target="_blank"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30">
                                Lihat File
                            </a>
                            @else
                            <button
                                onclick="Livewire.dispatch('openModal', { component: 'upload-sop-document-modal', arguments: { clientId: {{ $client->id }}, sopId: {{ $item['sop_id'] }} } })"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700">
                                Upload
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Tab: Uploaded Documents --}}
        <div x-show="tab === 'uploaded'" class="space-y-2">
            @forelse($uploadedDocuments as $doc)
            <div
                class="flex items-center gap-4 p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">{{ $doc->original_filename }}</div>
                    <div class="text-sm text-gray-500">
                        @if($doc->sopLegalDocument)
                        <span class="text-blue-600 dark:text-blue-400">{{ $doc->sopLegalDocument->name }}</span>
                        <span class="mx-1">•</span>
                        @else
                        <span class="text-purple-600 dark:text-purple-400">Dokumen Custom</span>
                        <span class="mx-1">•</span>
                        @endif
                        {{ $doc->created_at->format('d M Y') }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ \Storage::disk('public')->url($doc->file_path) }}" target="_blank"
                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-md dark:hover:bg-blue-900/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>

                    <button onclick="confirm('Hapus dokumen ini?') && $wire.call('deleteDocument', {{ $doc->id }})"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-md dark:hover:bg-red-900/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p>Belum ada dokumen yang diupload</p>
            </div>
            @endforelse
        </div>
    </div>
</div>