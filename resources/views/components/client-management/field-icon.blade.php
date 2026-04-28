@props(['name' => 'document'])

@php
    $paths = [
        'building' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 9h.01M15 9h.01',
        'tag' => 'M7 7h.01M3 11l8.586-8.586a2 2 0 012.828 0L21 9l-8.586 8.586a2 2 0 01-2.828 0L3 11z',
        'briefcase' => 'M10 6h4a2 2 0 012 2v1h3a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2h3V8a2 2 0 012-2z M8 9h8',
        'chart' => 'M4 19V5m0 14h16M8 16V9m4 7V7m4 9v-4',
        'calendar' => 'M8 7V3m8 4V3M5 11h14M5 7h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z',
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'phone' => 'M3 5a2 2 0 012-2h2.28a1 1 0 01.95.68l1.1 3.29a1 1 0 01-.27 1.05L7.9 9.18a12.02 12.02 0 005.92 5.92l1.16-1.16a1 1 0 011.05-.27l3.29 1.1a1 1 0 01.68.95V18a2 2 0 01-2 2h-1C9.82 20 4 14.18 4 7V5z',
        'device' => 'M8 2h8a2 2 0 012 2v16a2 2 0 01-2 2H8a2 2 0 01-2-2V4a2 2 0 012-2z M11 18h2',
        'globe' => 'M12 21a9 9 0 100-18 9 9 0 000 18z M3.6 9h16.8M3.6 15h16.8M12 3a14 14 0 010 18M12 3a14 14 0 000 18',
        'map' => 'M12 11a3 3 0 100-6 3 3 0 000 6z M19.5 10.5c0 7-7.5 10.5-7.5 10.5S4.5 17.5 4.5 10.5a7.5 7.5 0 1115 0z',
        'city' => 'M3 21h18M5 21V9l5-3v15M14 21V5l5 4v12M8 12h.01M8 16h.01M17 12h.01M17 16h.01',
        'hash' => 'M7 8h12M5 16h12M8 20l3-16M13 20l3-16',
        'receipt' => 'M7 3h10a2 2 0 012 2v16l-3-2-3 2-3-2-3 2V5a2 2 0 012-2z M9 8h6M9 12h6M9 16h4',
        'key' => 'M15 7a4 4 0 11-2.83 6.83L9 17H6v-3H3v-3h3.17A4 4 0 0115 7z',
        'user' => 'M12 12a4 4 0 100-8 4 4 0 000 8z M4 21a8 8 0 1116 0',
        'percent' => 'M19 5L5 19M7 7h.01M17 17h.01',
        'link' => 'M10 13a5 5 0 007.54.54l2-2a5 5 0 00-7.07-7.07l-1.15 1.15M14 11a5 5 0 00-7.54-.54l-2 2a5 5 0 007.07 7.07l1.15-1.15',
        'note' => 'M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2z M14 3v6h6M9 13h6M9 17h4',
        'document' => 'M9 12h6m-6 4h6M8 4h8a2 2 0 012 2v12a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2z',
    ];

    $path = $paths[$name] ?? $paths['document'];
@endphp

<svg {{ $attributes->merge(['class' => 'h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
</svg>
