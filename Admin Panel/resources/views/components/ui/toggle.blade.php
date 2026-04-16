@props([
    'checked' => false,
    'name' => 'enabled',
])

<label class="switch">
    <input type="checkbox" name="{{ $name }}" {{ $checked ? 'checked' : '' }} {{ $attributes }}>
    <span class="slider"></span>
</label>
