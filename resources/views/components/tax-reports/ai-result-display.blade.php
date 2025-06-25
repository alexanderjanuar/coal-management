<!-- resources/views/components/ai-result-display.blade.php -->
<div class="space-y-4">
    @if($status === 'processing')
        <div class="flex items-center justify-center py-8">
            <div class="text-center">
                <div class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-blue-700 dark:text-blue-300 font-medium">Sedang memproses dokumen dengan AI...</span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Mohon tunggu, ini mungkin memakan waktu beberapa detik</p>
            </div>
        </div>
    @elseif($status === 'completed' && $data)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-green-800 dark:text-green-200">
                    Ekstraksi Data Berhasil
                </h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Informasi Faktur</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor Faktur</label>
                                <p class="text-sm font-mono text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded">{{ $data['invoice_number'] }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Faktur</label>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ \Carbon\Carbon::parse($data['invoice_date'])->format('d M Y') }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Jenis Faktur</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $data['type'] === 'Faktur Keluaran' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' }}">
                                    {{ $data['type'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Informasi Perusahaan</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Nama Perusahaan</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $data['company_name'] }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">NPWP</label>
                                <p class="text-sm font-mono text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-700 px-2 py-1 rounded">{{ $data['npwp'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Detail Perpajakan</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">DPP (Dasar Pengenaan Pajak)</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format((int)$data['dpp'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tarif PPN</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $data['ppn_percentage'] }}%
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total PPN</span>
                                <span class="text-lg font-bold text-green-600 dark:text-green-400">Rp {{ number_format((int)$data['ppn'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Siap Diterapkan</p>
                                <p class="text-xs text-blue-600 dark:text-blue-300">Klik tombol "Terapkan Data AI ke Form" untuk mengisi form secara otomatis</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($status === 'error')
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">Terjadi Kesalahan</h3>
                    <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ $error ?? 'Kesalahan tidak diketahui' }}</p>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <div class="mx-auto h-12 w-12 text-gray-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Upload File Faktur</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Upload file dan klik "Proses dengan AI" untuk melihat hasil ekstraksi data</p>
        </div>
    @endif
</div>