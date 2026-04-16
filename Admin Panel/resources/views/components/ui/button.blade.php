@props([
    'variant' => 'primary',
    'size' => 'md',
    'circle' => false,
    'icon' => null,
    'type' => 'button',
    'title' => null,
    'href' => null,
])

@php
    $base = 'btn-agri';

    $variantClass = match($variant) {
        'primary' => 'btn-agri-primary',
        'outline' => 'btn-agri-outline',
        'danger-soft' => 'panel-action-btn panel-action-danger',
        'info-soft' => 'panel-action-btn panel-action-info',
        'success-soft' => 'panel-action-btn panel-action-success',
        default => 'btn-agri-outline',
    };

    $sizeClass = match($size) {
        'sm' => 'panel-btn-sm',
        'lg' => 'panel-btn-lg',
        default => 'panel-btn-md',
    };

    $shapeClass = $circle ? 'panel-btn-circle' : '';

    $classes = trim("{$base} {$variantClass} {$sizeClass} {$shapeClass}");
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classes }}" @if($title) title="{{ $title }}" @endif>
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }}" @if($title) title="{{ $title }}" @endif>
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif
