@props([
    'header' => null,
])

<div {{ $attributes->merge(['class' => 'card-agri']) }}>
    @if($header)
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
            {!! $header !!}
        </div>
    @endif
    <div class="card-body p-0">
        {{ $slot }}
    </div>
</div>
