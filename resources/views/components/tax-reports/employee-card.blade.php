<div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
    <!-- Header with gradient background -->
    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
        <div class="flex justify-between items-center">
            <h3 class="font-semibold text-lg text-white truncate">{{ $employee->name }}</h3>
            <span class="px-3 py-1 text-xs font-medium rounded-full {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $employee->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
            </span>
        </div>
    </div>
    
    <!-- Employee avatar and details -->
    <div class="p-6">
        <div class="flex">
            <!-- Avatar circle with initials -->
            <div class="flex-shrink-0 mr-4">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                    <span class="text-xl font-medium text-indigo-700">
                        {{ substr($employee->name, 0, 1) }}{{ isset(explode(' ', $employee->name)[1]) ? substr(explode(' ', $employee->name)[1], 0, 1) : '' }}
                    </span>
                </div>
            </div>
            
            <!-- Employee information -->
            <div class="flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">NPWP</p>
                        <p class="text-sm font-medium">{{ $employee->npwp ?? '-' }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</p>
                        <p class="text-sm font-medium">{{ $employee->position ?? '-' }}</p>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</p>
                        <p class="text-sm font-medium">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $employee->type === 'Karyawan Tetap' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $employee->type }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Gaji</p>
                        <p class="text-sm font-semibold text-emerald-600">Rp {{ number_format($employee->salary, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Optional additional info or actions -->
        <div class="mt-4 pt-3 border-t border-gray-100">
            <div class="flex justify-end space-x-2">
                <a href="#" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    Detail Karyawan
                </a>
                <span class="text-gray-300">|</span>
                <a href="#" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                    Riwayat Pajak
                </a>
            </div>
        </div>
    </div>
</div>