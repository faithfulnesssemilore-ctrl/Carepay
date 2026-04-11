@props([
    'placement' => 'bottom-start', // Bootstrap uses data-bs-placement? Actually, Bootstrap uses data-bs-offset or data-bs-reference, but we'll keep it simple.
])

<div
    x-data="{ open: false }"
    @click.away="open = false"
    @keydown.escape.window="open = false"
    {{ $attributes->merge(['class' => 'dropdown']) }}
>
    {{ $slot }}
</div>