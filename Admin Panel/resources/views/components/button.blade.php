@props([
    'variant' => 'primary',
    'icon' => null,
    'type' => 'button',
    'href' => null,
    'loadingText' => 'Processing...',
])

@php
    $classes = [
        'primary' => 'btn-agri btn-agri-primary',
        'secondary' => 'btn-agri btn-agri-outline',
        'outline' => 'btn-agri btn-agri-outline',
        'danger' => 'btn-agri btn-agri-danger',
        'icon' => 'btn btn-sm btn-light border shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center',
    ];

    $className = $classes[$variant] ?? $classes['primary'];
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $className]) }}>
        @if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $className . ' platform-submit-btn']) }} data-loading-text="{{ $loadingText }}">
        <span class="btn-content">@if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $slot }}</span>
    </button>
@endif
