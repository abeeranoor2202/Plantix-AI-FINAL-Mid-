@props([
    'action' => '',
    'method' => 'GET',
    'placeholder' => 'Search...',
    'name' => 'q',
    'value' => '',
])

<form action="{{ $action }}" method="{{ $method }}" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
    <div class="input-group" style="min-width:220px;">
        <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify"></i></span>
        <input
            type="search"
            name="{{ $name }}"
            value="{{ $value }}"
            class="form-control border-start-0"
            placeholder="{{ $placeholder }}"
            maxlength="100"
        >
    </div>
    <button type="submit" class="btn-agri btn-agri-primary">Search</button>
</form>
