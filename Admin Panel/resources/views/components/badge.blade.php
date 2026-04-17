@props([
    'variant' => 'secondary',
])

@php
    $map = [
        'success' => 'badge rounded-pill badge-success-agri',
        'secondary' => 'badge rounded-pill badge-secondary-agri',
        'warning' => 'badge rounded-pill badge-warning-agri',
        'danger' => 'badge rounded-pill badge-danger-agri',
        'info' => 'badge rounded-pill badge-info-agri',
    ];
@endphp

<span {{ $attributes->merge(['class' => $map[$variant] ?? $map['secondary']]) }}>{{ $slot }}</span>
