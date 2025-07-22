<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 rounded-xl p-6 border border-blue-200 dark:border-gray-600 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid-pattern" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid-pattern)" />
            </svg>
        </div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Faktur Pajak Asli</h1>
                        <p class="text-gray-600 dark:text-gray-300">{{ $originalInvoice->invoice_number }}</p>
                    </div>
                </div>
                
                <!-- Status Badge -->
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Faktur Asli
                    </span>
                    @if($originalInvoice->hasRevisions())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ $originalInvoice->revisions()->count() }} Revisi
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column - Invoice Information -->
        <div class="space-y-6">
            <!-- Basic Information -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-5 border border-blue-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Informasi Dasar
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Nomor Faktur</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $originalInvoice->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Faktur</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($originalInvoice->invoice_date)->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-blue-200 dark:border-gray-600">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Jenis Faktur</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $originalInvoice->type === 'Faktur Keluaran' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' }}">
                            {{ $originalInvoice->type }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tipe Client</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $originalInvoice->client_type ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-5 border border-green-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Informasi Perusahaan
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-start py-2 border-b border-green-200 dark:border-gray-600">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Nama Perusahaan</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white text-right max-w-xs">{{ $originalInvoice->company_name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-green-200 dark:border-gray-600">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">NPWP</span>
                        <span class="text-sm font-mono text-gray-900 dark:text-white">{{ $originalInvoice->npwp }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Subject PPN</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $originalInvoice->has_ppn ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                            {{ $originalInvoice->has_ppn ? 'Ya' : 'Tidak' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="grid grid-cols-1 gap-4">
                <!-- PPN Rate -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-4 border border-purple-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">Tarif PPN</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pajak Pertambahan Nilai</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ ($originalInvoice->ppn_percentage ?? '11') === '11' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' }}">
                            {{ $originalInvoice->ppn_percentage ?? '11' }}%
                        </span>
                    </div>
                </div>

                <!-- DPP -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-4 border border-blue-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">DPP</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dasar Pengenaan Pajak</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($originalInvoice->dpp, 0, ',', '.') }}</p>
                            @if(($originalInvoice->ppn_percentage ?? '11') === '12' && ($originalInvoice->dpp_nilai_lainnya ?? 0) > 0)
                                <p class="text-xs text-gray-500 dark:text-gray-400">DPP Nilai Lainnya: Rp {{ number_format($originalInvoice->dpp_nilai_lainnya, 0, ',', '.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- PPN -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-4 border border-green-200 dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">PPN</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pajak Pertambahan Nilai</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($originalInvoice->ppn, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Summary -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-5 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold">Total Nilai Faktur</h3>
                        <p class="text-indigo-100">DPP + PPN</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold">Rp {{ number_format($originalInvoice->dpp + $originalInvoice->ppn, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Revision History -->
            @if($originalInvoice->hasRevisions())
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-5 border border-yellow-200 dark:border-yellow-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Revisi
                </h3>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    <p>Faktur ini telah direvisi sebanyak <strong>{{ $originalInvoice->revisions()->count() }}</strong> kali.</p>
                    <p class="mt-1">Revisi terakhir: <strong>{{ $originalInvoice->revisions()->latest()->first()->created_at->format('d M Y H:i') }}</strong></p>
                </div>
            </div>
            @endif

            <!-- Notes Section -->
            @if($originalInvoice->notes)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-5 border border-yellow-200 dark:border-yellow-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Catatan
                </h3>
                <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300">
                    {!! $originalInvoice->notes !!}
                </div>
            </div>
            @endif

            <!-- Created By Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Informasi Sistem
                </h4>
                <div class="grid grid-cols-1 gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Dibuat oleh</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @if($originalInvoice->created_by)
                                    @php
                                        $user = \App\Models\User::find($originalInvoice->created_by);
                                    @endphp
                                    {{ $user ? $user->name : 'User #' . $originalInvoice->created_by }}
                                @else
                                    Sistem
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Tanggal dibuat</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $originalInvoice->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Document Preview -->
        <div class="space-y-6">
            <!-- Invoice File Preview -->
            @if($originalInvoice->file_path)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Berkas Faktur
                </h3>
                <div class="rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                    <iframe 
                        src="{{ asset('storage/' . $originalInvoice->file_path) }}" 
                        class="w-full h-96 border-0"
                        title="Preview Faktur"
                        loading="lazy">
                        <p class="p-4 text-center text-gray-500 dark:text-gray-400">
                            Browser Anda tidak mendukung preview dokumen. 
                            <a href="{{ asset('storage/' . $originalInvoice->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                Klik di sini untuk membuka dokumen
                            </a>
                        </p>
                    </iframe>
                </div>
                <div class="mt-3 flex justify-between items-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ basename($originalInvoice->file_path) }}</p>
                    <a href="{{ asset('storage/' . $originalInvoice->file_path) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Buka di Tab Baru
                    </a>
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Berkas Faktur
                </h3>
                <div class="p-8 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-500 dark:text-gray-400">Tidak ada berkas faktur</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Berkas faktur belum diunggah</p>
                </div>
            </div>
            @endif

            <!-- Bukti Setor Preview -->
            @if($originalInvoice->bukti_setor)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Bukti Setor
                </h3>
                <div class="rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
                    <iframe 
                        src="{{ asset('storage/' . $originalInvoice->bukti_setor) }}" 
                        class="w-full h-96 border-0"
                        title="Preview Bukti Setor"
                        loading="lazy">
                        <p class="p-4 text-center text-gray-500 dark:text-gray-400">
                            Browser Anda tidak mendukung preview dokumen. 
                            <a href="{{ asset('storage/' . $originalInvoice->bukti_setor) }}" target="_blank" class="text-green-600 hover:text-green-800 dark:text-green-400">
                                Klik di sini untuk membuka dokumen
                            </a>
                        </p>
                    </iframe>
                </div>
                <div class="mt-3 flex justify-between items-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ basename($originalInvoice->bukti_setor) }}</p>
                    <a href="{{ asset('storage/' . $originalInvoice->bukti_setor) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Buka di Tab Baru
                    </a>
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Bukti Setor
                </h3>
                <div class="p-8 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-4 text-lg font-medium text-gray-500 dark:text-gray-400">Belum ada bukti setor</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Bukti setor pajak belum diunggah</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div><div class="space-y-6" x-data="{ activeSection: 'basic' }">
    <!-- Header Section -->
    <div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 rounded-xl p-6 border border-blue-200 dark:border-gray-600 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid-pattern" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100" height="100" fill="url(#grid-pattern)" />
            </svg>
        </div>
        
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Faktur Pajak Asli</h1>
                        <p class="text-gray-600 dark:text-gray-300">{{ $originalInvoice->invoice_number }}</p>
                    </div>
                </div>
                
                <!-- Status Badge -->
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Faktur Asli
                    </span>
                    @if($originalInvoice->hasRevisions())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ $originalInvoice->revisions()->count() }} Revisi
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <!-- Created By Information -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Informasi Sistem
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Dibuat oleh</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        @if($originalInvoice->created_by)
                            @php
                                $user = \App\Models\User::find($originalInvoice->created_by);
                            @endphp
                            {{ $user ? $user->name : 'User #' . $originalInvoice->created_by }}
                        @else
                            Sistem
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">Tanggal dibuat</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $originalInvoice->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>