@props([
    'align' => 'start', // start or end
])

<div
    class="dropdown-menu {{ $align === 'end' ? 'dropdown-menu-end' : '' }}"
    {{ $attributes }}
>
    {{ $slot }}
</div>