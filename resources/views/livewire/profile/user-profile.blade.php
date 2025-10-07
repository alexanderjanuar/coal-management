<div class="w-full">
    <div class="flex items-center space-x-3">
        <!-- Avatar -->
        <div class="relative flex-shrink-0">
            <img 
                src="{{ $user->avatar }}" 
                alt="{{ $user->name }}" 
                class="w-11 h-11 rounded-xl object-cover ring-2 ring-gray-200 dark:ring-gray-700"
            >
            <!-- Status Dot -->
            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 {{ $user->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }} border-2 border-white dark:border-gray-900 rounded-full"></span>
        </div>

        <!-- User Info -->
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                {{ $user->name }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                @if($user->position)
                    {{ $user->position }}
                @elseif($user->department)
                    {{ $user->department }}
                @else
                    {{ $user->email }}
                @endif
            </p>
        </div>
    </div>
</div>