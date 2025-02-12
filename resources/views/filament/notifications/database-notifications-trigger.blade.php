@php
$isMobile = preg_match('/(android|iphone|ipad|mobile)/i', request()->header('User-Agent'));
@endphp

@if(!$isMobile)
<button type="button"
    class="relative inline-flex items-center gap-2 px-4 py-2 {{ $unreadNotificationsCount > 0 ? 'bg-amber-500 text-white' : 'bg-white text-gray-700 border border-gray-200' }} rounded-lg transition-colors duration-200 hover:bg-opacity-90 active:scale-95">
    <!-- Bell Icon -->
    <svg xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 {{ $unreadNotificationsCount > 0 ? 'text-white' : 'text-gray-500' }}" fill="none"
        viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>
    <!-- Text -->
    <span class="text-sm font-medium {{ $unreadNotificationsCount > 0 ? 'text-white' : 'text-gray-500' }}">
        Notifications
        @if($unreadNotificationsCount > 0)
        <span
            class="inline-flex items-center justify-center h-5 w-5 text-xs font-bold text-white bg-red-500 rounded-full ml-2">
            {{ $unreadNotificationsCount }}
        </span>
        @endif
    </span>
</button>
@endif