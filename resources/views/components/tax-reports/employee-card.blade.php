<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
    <!-- Header with subtle blue accent -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h3 class="font-semibold text-lg text-gray-900 mb-1">{{ $employee->name }}</h3>
                <p class="text-sm text-blue-600">{{ $employee->position ?? 'Staff' }}</p>
            </div>
            
            <div class="flex flex-col items-end space-y-2">
                <!-- Status badge -->
                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $employee->status === 'active' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                    {{ $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                </span>
                
                <!-- Tax Status -->
                @if(isset($taxStatus))
                <span class="px-3 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-700 border border-indigo-200">
                    {{ $taxStatus }}
                </span>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Content area -->
    <div class="p-6">
        <div class="flex space-x-5">
            <!-- Avatar with subtle color -->
            <div class="flex-shrink-0">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center border border-blue-200">
                    <span class="text-lg font-semibold text-blue-700">
                        {{ substr($employee->name, 0, 1) }}{{ isset(explode(' ', $employee->name)[1]) ? substr(explode(' ', $employee->name)[1], 0, 1) : '' }}
                    </span>
                </div>
            </div>
            
            <!-- Employee information -->
            <div class="flex-1 space-y-4">
                <!-- Basic Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide">NPWP</label>
                        <p class="text-sm font-medium text-gray-900">{{ $employee->npwp ?? 'Belum diisi' }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide">Tipe Karyawan</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium {{ $employee->type === 'Karyawan Tetap' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $employee->type }}
                        </span>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide">Gaji Bulanan</label>
                        <p class="text-sm font-semibold text-emerald-600">Rp {{ number_format($employee->salary ?? 0, 0, ',', '.') }}</p>
                    </div>
                    
                    @if(isset($taxStatus))
                    <div class="space-y-1">
                        <label class="text-xs font-medium text-slate-500 uppercase tracking-wide">Status Pajak</label>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-indigo-700">{{ $taxStatus }}</span>
                            <span class="text-xs text-gray-500">
                                ({{ $employee->marital_status === 'single' ? 'Belum Menikah' : 'Menikah' }}, {{ $employee->marital_status === 'single' ? $employee->tk : $employee->k }} tanggungan)
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- TER Information with subtle accent -->
                @if(isset($taxStatus))
                <div class="bg-gradient-to-r from-slate-50 to-blue-50 rounded-lg p-4 border border-slate-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-xs font-medium text-slate-600 uppercase tracking-wide">Kategori TER</label>
                            <p class="text-sm font-semibold text-blue-700 mt-1">
                                Kategori {{ 
                                    $employee->marital_status === 'single' ? 
                                        (in_array($employee->tk, [0, 1]) ? 'A' : 'B') : 
                                        ($employee->k == 0 ? 'A' : (in_array($employee->k, [1, 2]) ? 'B' : 'C'))
                                }}
                            </p>
                        </div>
                        <div class="text-right">
                            <label class="text-xs font-medium text-slate-600 uppercase tracking-wide">Berlaku untuk</label>
                            <p class="text-sm text-slate-600 mt-1">
                                {{ 
                                    $employee->marital_status === 'single' ? 
                                        (in_array($employee->tk, [0, 1]) ? 'TK/0, TK/1, K/0' : 'TK/2, TK/3, K/1, K/2') : 
                                        ($employee->k == 0 ? 'TK/0, TK/1, K/0' : (in_array($employee->k, [1, 2]) ? 'TK/2, TK/3, K/1, K/2' : 'K/3'))
                                }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Footer actions with subtle accent -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 text-xs text-slate-500">
                    <span class="bg-slate-100 px-2 py-1 rounded">ID: {{ $employee->id }}</span>
                    <span>•</span>
                    <span>Dibuat: {{ \Carbon\Carbon::parse($employee->created_at)->format('M Y') }}</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <button class="text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors hover:bg-blue-50 px-3 py-1 rounded">
                        Detail Karyawan
                    </button>
                    <button class="text-sm text-indigo-600 hover:text-indigo-700 font-medium transition-colors hover:bg-indigo-50 px-3 py-1 rounded">
                        Riwayat Pajak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>