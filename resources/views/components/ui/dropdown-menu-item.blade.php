@props([
    'disabled' => false,
    'active' => false,
])

@php
    $classes = 'dropdown-item';
    if ($active) $classes .= ' active';
    if ($disabled) $classes .= ' disabled';
@endphp

<a
    href="#"
    class="{{ $classes }}"
    {{ $disabled ? 'aria-disabled="true" tabindex="-1"' : '' }}
    {{ $attributes }}
>
    {{ $slot }}
</a>