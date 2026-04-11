@props([
    'ratio' => 1, // e.g., 16/9, 4/3, 1 for square
])

<div
    style="aspect-ratio: {{ $ratio }};"
    {{ $attributes }}
>
    {{ $slot }}
</div>