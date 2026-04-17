@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'value' => null,
])

@php
    $resolvedError = $error ?? ($name ? $errors->first($name) : null);
    $inputType = $attributes->get('type', 'text');
    $resolvedValue = in_array($inputType, ['password', 'file'], true) ? null : old($name, $value);
@endphp

@if($label)
    <label class="agri-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
@endif
<input name="{{ $name }}" value="{{ $resolvedValue }}" {{ $required ? 'required' : '' }} {{ $attributes->merge(['class' => 'form-agri' . ($resolvedError ? ' is-invalid' : '')]) }}>
@if($resolvedError)
    <small class="text-danger d-block mt-1">{{ $resolvedError }}</small>
@elseif($help)
    <small class="text-muted d-block mt-1">{{ $help }}</small>
@endif
