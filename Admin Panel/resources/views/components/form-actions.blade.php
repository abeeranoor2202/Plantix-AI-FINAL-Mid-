@props([
    'submitLabel' => 'Save',
    'cancelHref' => null,
    'cancelLabel' => 'Cancel',
])

<div class="platform-form-actions" style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-top: 16px;">
    @if($cancelHref)
        <a href="{{ $cancelHref }}" class="btn-agri btn-agri-outline">{{ $cancelLabel }}</a>
    @endif
    <button type="submit" class="btn-agri btn-agri-primary">{{ $submitLabel }}</button>
</div>
