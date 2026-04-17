@props([
    'name',
    'label' => null,
    'required' => false,
    'placeholder' => null,
    'error' => null,
    'help' => null,
])

@php
    $fieldId = $attributes->get('id', $name);
    $fieldError = $error ?? $errors->first($name);
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $fieldId }}" class="agri-label mb-2">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $fieldId }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->class(['form-control form-agri', 'is-invalid' => $fieldError])->except(['id']) }}
    >
        @if($placeholder)
            <option value="" disabled>{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if($fieldError)
        <div class="invalid-feedback d-block">{{ $fieldError }}</div>
    @elseif($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>
