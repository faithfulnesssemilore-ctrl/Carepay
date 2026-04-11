@props([
    'value' => null,
])

<div
    x-data="{ selected: @json($value) }"
    {{ $attributes }}
>
    {{ $slot }}
</div>