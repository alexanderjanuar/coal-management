@php
    /** @var \App\Models\User $user */
    $name = $user->name ?? '';
    $words = preg_split('/\s+/', trim($name));
    if (count($words) >= 2) {
        $initials = strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1));
    } else {
        $initials = strtoupper(mb_substr($name, 0, 2));
    }
    $initials = $initials !== '' ? $initials : '?';

    $raw = $user->avatar_url ?? null;
    $src = null;
    if ($raw) {
        $src = \Illuminate\Support\Str::startsWith($raw, ['http://', 'https://']) ? $raw : asset($raw);
    }
@endphp

@if ($src)
    <span x-data="{ failed: false }" class="cu-avatar-inner">
        <img src="{{ $src }}"
             alt="{{ $name }}"
             x-show="!failed"
             x-on:error="failed = true"
             loading="lazy">
        <span x-show="failed" x-cloak class="cu-avatar-initials">{{ $initials }}</span>
    </span>
@else
    <span class="cu-avatar-initials">{{ $initials }}</span>
@endif
