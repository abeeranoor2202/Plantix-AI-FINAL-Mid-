@props([
    'responsive' => true,
])

<div class="card-agri panel-table-shell" style="padding: 0; overflow: hidden;">
    @if($responsive)
        <div class="table-responsive">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif
</div>
