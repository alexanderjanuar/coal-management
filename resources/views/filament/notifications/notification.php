<x-filament-notifications::notification :notification="$notification"
    class="flex w-80 rounded-lg transition duration-200" x-transition:enter-start="opacity-0"
    x-transition:leave-end="opacity-0">
    <div class="bg-red-500 w-100">
        12312

        <span x-on:click="close">
            Close
        </span>
    </div>
</x-filament-notifications::notification>