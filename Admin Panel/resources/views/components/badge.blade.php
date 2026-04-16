@props([
    'variant' => 'success',
])

@php
    $map = [
        'success' => 'badge rounded-pill bg-success',
        'secondary' => 'badge rounded-pill bg-secondary',
        'warning' => 'badge rounded-pill bg-warning',
        'danger' => 'badge rounded-pill bg-danger',
        'info' => 'badge rounded-pill bg-info',
    ];
@endphp

<span {{ $attributes->merge(['class' => $map[$variant] ?? $map['secondary']]) }}>{{ $slot }}</span>
