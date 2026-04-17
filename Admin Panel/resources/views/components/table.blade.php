@props([
    'responsive' => true,
])

@if($responsive)
<div class="table-responsive">
@endif
<table {{ $attributes->merge(['class' => 'table agri-table mb-0']) }}>
    {{ $slot }}
</table>
@if($responsive)
</div>
@endif
