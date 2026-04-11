@props([
    'href' => null,
    'active' => false,
    'dropdown' => false,
])

<li class="nav-item {{ $dropdown ? 'dropdown' : '' }}">
    @if($dropdown)
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            {{ $slot }}
        </a>
        <ul class="dropdown-menu">
            {{ $dropdownMenu ?? '' }}
        </ul>
    @else
        <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $href ?? '#' }}" {{ $active ? 'aria-current="page"' : '' }}>
            {{ $slot }}
        </a>
    @endif
</li>