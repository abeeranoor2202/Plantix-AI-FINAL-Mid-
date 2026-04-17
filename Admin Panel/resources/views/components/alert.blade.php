@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $map = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];

    $iconMap = [
        'success' => 'fas fa-circle-check',
        'danger' => 'fas fa-triangle-exclamation',
        'warning' => 'fas fa-triangle-exclamation',
        'info' => 'fas fa-circle-info',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'alert ' . ($map[$variant] ?? 'alert-info') . ' d-flex align-items-start gap-3']) }} role="alert">
    <div style="width: 1.75rem; flex: 0 0 auto; text-align: center; font-size: 1rem; line-height: 1.75rem;">
        <i class="{{ $iconMap[$variant] ?? $iconMap['info'] }}"></i>
    </div>
    <div style="flex: 1; min-width: 0;">
        @if($title)
            <div style="font-weight: 700; margin-bottom: 4px;">{{ $title }}</div>
        @endif
        <div>{{ $slot }}</div>
    </div>
</div>
