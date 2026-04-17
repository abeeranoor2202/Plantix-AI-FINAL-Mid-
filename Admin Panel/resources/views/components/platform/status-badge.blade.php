@props([
    'domain',
    'status',
    'size' => 'sm',
])

@php
    $meta = \App\Support\StatusPresenter::present((string) $domain, (string) $status);
    $fontSize = $size === 'md' ? '13px' : '12px';
    $padding = $size === 'md' ? '7px 14px' : '6px 12px';
@endphp

<span class="badge rounded-pill fw-medium"
      style="background: {{ $meta['background'] }}; color: {{ $meta['color'] }}; padding: {{ $padding }}; font-size: {{ $fontSize }}; width: fit-content;">
    {{ $meta['label'] }}
</span>
