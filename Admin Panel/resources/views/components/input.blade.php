@props([
    'label' => null,
    'name' => null,
    'required' => false,
])

@if($label)
    <label class="agri-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
@endif
<input name="{{ $name }}" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'form-agri']) }}>
