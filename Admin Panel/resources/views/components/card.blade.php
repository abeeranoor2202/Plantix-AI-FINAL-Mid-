@props([
    'header' => null,
    'bodyClass' => 'p-0',
])

<div {{ $attributes->merge(['class' => 'card-agri']) }}>
    @if($header)
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4">
            {!! $header !!}
        </div>
    @endif
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>
