@props([
    'variant' => 'primary',
    'icon' => null,
    'type' => 'button',
    'href' => null,
])

@php
    $classes = [
        'primary' => 'btn-agri btn-agri-primary',
        'outline' => 'btn-agri btn-agri-outline',
        'danger' => 'btn btn-danger',
        'icon' => 'btn btn-sm btn-light border shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center',
    ];

    $className = $classes[$variant] ?? $classes['primary'];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $className]) }}>
        @if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $className]) }}>
        @if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $slot }}
    </button>
@endif
