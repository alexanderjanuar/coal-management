{{-- resources/views/livewire/user-detail/user-submitted-documents.blade.php --}}
<div>
    {{-- Statistics Widget --}}
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            {{-- Total Documents --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-document-text class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Dokumen</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $user->submittedDocuments->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approved Documents --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Disetujui</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ $user->submittedDocuments->where('status', 'approved')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Documents --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Menunggu Review</p>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                {{ $user->submittedDocuments->where('status', 'pending_review')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rejected Documents --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-x-circle class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ditolak</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ $user->submittedDocuments->where('status', 'rejected')->count() }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Info Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
            {{-- Recent Activity --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h3>
                        <x-heroicon-o-clock class="w-4 h-4 text-gray-400" />
                    </div>
                    @php
                        $recentDocument = $user->submittedDocuments->sortByDesc('created_at')->first();
                    @endphp
                    @if($recentDocument)
                        <div class="space-y-2">
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Dokumen terakhir diunggah:
                            </p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ pathinfo(basename($recentDocument->file_path), PATHINFO_FILENAME) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $recentDocument->created_at->diffForHumans() }}
                            </p>
                            <x-filament::badge 
                                size="xs"
                                :color="match($recentDocument->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'pending_review' => 'warning',
                                    default => 'secondary'
                                }"
                            >
                                {{ match($recentDocument->status) {
                                    'uploaded' => 'Diunggah',
                                    'pending_review' => 'Menunggu Review',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default => ucfirst($recentDocument->status)
                                } }}
                            </x-filament::badge>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Belum ada dokumen yang diunggah
                        </p>
                    @endif
                </div>
            </div>

            {{-- File Types Distribution --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Jenis File</h3>
                        <x-heroicon-o-folder class="w-4 h-4 text-gray-400" />
                    </div>
                    @php
                        $fileTypes = $user->submittedDocuments->map(function($doc) {
                            return strtoupper(pathinfo(basename($doc->file_path), PATHINFO_EXTENSION));
                        })->countBy()->take(3);
                    @endphp
                    <div class="space-y-2">
                        @forelse($fileTypes as $type => $count)
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2">
                                    <x-filament::badge size="xs" color="gray">{{ $type }}</x-filament::badge>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Belum ada file
                            </p>
                        @endforelse
                        @if($fileTypes->count() > 3)
                            <p class="text-xs text-gray-400">
                                dan {{ $user->submittedDocuments->count() - $fileTypes->sum() }} lainnya
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Approval Rate --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Tingkat Persetujuan</h3>
                        <x-heroicon-o-chart-bar class="w-4 h-4 text-gray-400" />
                    </div>
                    @php
                        $totalDocs = $user->submittedDocuments->count();
                        $approvedDocs = $user->submittedDocuments->where('status', 'approved')->count();
                        $approvalRate = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100, 1) : 0;
                        
                        $rateColor = match(true) {
                            $approvalRate >= 80 => 'success',
                            $approvalRate >= 60 => 'warning',
                            $approvalRate >= 40 => 'danger',
                            default => 'gray'
                        };
                    @endphp
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-{{ $rateColor == 'success' ? 'green' : ($rateColor == 'warning' ? 'yellow' : ($rateColor == 'danger' ? 'red' : 'gray')) }}-600 dark:text-{{ $rateColor == 'success' ? 'green' : ($rateColor == 'warning' ? 'yellow' : ($rateColor == 'danger' ? 'red' : 'gray')) }}-400">
                                {{ $approvalRate }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-{{ $rateColor == 'success' ? 'green' : ($rateColor == 'warning' ? 'yellow' : ($rateColor == 'danger' ? 'red' : 'gray')) }}-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $approvalRate }}%">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $approvedDocs }} dari {{ $totalDocs }} dokumen disetujui
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Header with User Info --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-lg p-4 border border-blue-200 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Dokumen yang Dikirim
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Semua dokumen yang telah dikirim oleh <span class="font-medium">{{ $user->name }}</span>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                        Email: {{ $user->email }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                            <x-heroicon-o-user class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $user->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Bergabung {{ $user->created_at->format('M Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    {{ $this->table }}
</div>