@props([
    'src' => '',
    'alt' => '',
    'fallbackSrc' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODgiIGhlaWdodD0iODgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgc3Ryb2tlPSIjMDAwIiBzdHJva2UtbGluZWpvaW49InJvdW5kIiBvcGFjaXR5PSIuMyIgZmlsbD0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIzLjciPjxyZWN0IHg9IjE2IiB5PSIxNiIgd2lkdGg9IjU2IiBoZWlnaHQ9IjU2IiByeD0iNiIvPjxwYXRoIGQ9Im0xNiA1OCAxNi0xOCAzMiAzMiIvPjxjaXJjbGUgY3g9IjUzIiBjeT0iMzUiIHI9IjciLz48L3N2Zz4=',
])

<div
    x-data="{ error: false }"
    {{ $attributes->merge(['class' => 'd-inline-block']) }}
>
    <template x-if="!error">
        <img src="{{ $src }}" alt="{{ $alt }}" @error="error = true" {{ $attributes->except(['src', 'alt'])->merge(['class' => 'img-fluid']) }} />
    </template>
    <template x-if="error">
        <img src="{{ $fallbackSrc }}" alt="Fallback image" {{ $attributes->except(['src', 'alt'])->merge(['class' => 'img-fluid']) }} />
    </template>
</div>