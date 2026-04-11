@props([
    'active' => false,
    'disabled' => false,
    'href' => null,
])

<li class="page-item {{ $active ? 'active' : '' }} {{ $disabled ? 'disabled' : '' }}">
    <a class="page-link" href="{{ $href ?? '#' }}" {{ $disabled ? 'tabindex="-1" aria-disabled="true"' : '' }}>
        {{ $slot }}
    </a>
</li>