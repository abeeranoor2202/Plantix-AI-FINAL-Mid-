@props([
    'checked' => false,
    'name' => 'toggle',
])

<label class="switch">
    <input type="checkbox" name="{{ $name }}" value="1" {{ $checked ? 'checked' : '' }} {{ $attributes }}>
    <span class="slider"></span>
</label>
