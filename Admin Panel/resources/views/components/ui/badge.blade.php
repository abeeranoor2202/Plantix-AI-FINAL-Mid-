@props([
    'variant' => 'secondary',
])

@php
    $classes = match($variant) {
        'active', 'success' => 'badge rounded-pill bg-success',
        'inactive', 'secondary' => 'badge rounded-pill bg-secondary',
        'pending', 'warning' => 'badge rounded-pill bg-warning',
        'danger' => 'badge rounded-pill bg-danger',
        'info' => 'badge rounded-pill bg-info',
        default => 'badge rounded-pill bg-secondary',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
