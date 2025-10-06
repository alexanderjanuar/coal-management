<div class="p-3">
    <!-- Compact Header -->
    <div class="flex items-center space-x-3 mb-3">
        <img 
            src="{{ $user->avatar }}" 
            alt="{{ $user->name }}" 
            class="w-10 h-10 rounded-full object-cover ring-2 ring-primary-500"
        >
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                {{ $user->name }}
            </h3>
            <div class="flex items-center space-x-1 mt-0.5">
                <span class="text-xs px-1.5 py-0.5 rounded
                    {{ $user->status === 'active' 
                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200' 
                        : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' 
                    }}
                ">
                    {{ $user->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-2 gap-2 text-xs">
        @if($user->department)
        <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
            <p class="text-gray-500 dark:text-gray-400">Departemen</p>
            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $user->department }}</p>
        </div>
        @endif
        
        @if($user->position)
        <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
            <p class="text-gray-500 dark:text-gray-400">Jabatan</p>
            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $user->position }}</p>
        </div>
        @endif
        
        <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
            <p class="text-gray-500 dark:text-gray-400">Role</p>
            <p class="font-medium text-gray-900 dark:text-white truncate">
                {{ match($userRole) {
                    'super-admin' => 'Super Admin',
                    'direktur' => 'Direktur',
                    'project-manager' => 'PM',
                    'staff' => 'Staff',
                    default => ucfirst($userRole)
                } }}
            </p>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
            <p class="text-gray-500 dark:text-gray-400">Klien</p>
            <p class="font-medium text-gray-900 dark:text-white">{{ $userClients->count() }}</p>
        </div>
    </div>
</div>