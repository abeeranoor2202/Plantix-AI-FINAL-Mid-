@props([
    'icon',
    'label',
    'route',
    'active' => false,
    'badge' => null,
])

<a class="nav-link-agri {{ $active ? 'active' : '' }}" href="{{ $route }}">
    <span class="sidebar-item-icon-box" aria-hidden="true">
        <i class="mdi {{ $icon }} admin-side-nav-icon"></i>
    </span>
    <span class="sidebar-item-label">{{ $label }}</span>
    @if(!empty($badge))
        <span class="badge rounded-pill bg-danger sidebar-item-badge">{{ $badge }}</span>
    @endif
</a>
