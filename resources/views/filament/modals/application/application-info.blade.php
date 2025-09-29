{{-- resources/views/filament/components/application-info.blade.php --}}
<div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
    <div class="flex items-start gap-3">
        @if($application->logo)
        <img src="{{ Storage::disk('public')->url($application->logo) }}" alt="{{ $application->name }}"
            class="w-12 h-12 rounded-lg">
        @endif

        <div class="flex-1">
            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $application->name }}</h4>

            @if($application->description)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $application->description }}</p>
            @endif

            <div class="flex gap-4 mt-2 text-xs text-gray-500">
                <span class="flex items-center gap-1">
                    <x-heroicon-o-tag class="w-3 h-3" />
                    {{ ucfirst($application->category) }}
                </span>

                @if($application->app_url)
                <a href="{{ $application->app_url }}" target="_blank"
                    class="flex items-center gap-1 text-blue-600 hover:text-blue-800">
                    <x-heroicon-o-link class="w-3 h-3" />
                    Website
                </a>
                @endif
            </div>

            @if($application->required_fields)
            <div class="mt-2 text-xs text-gray-600">
                <strong>Field yang diperlukan:</strong>
                {{ implode(', ', array_keys($application->required_fields)) }}
            </div>
            @endif
        </div>
    </div>
</div>