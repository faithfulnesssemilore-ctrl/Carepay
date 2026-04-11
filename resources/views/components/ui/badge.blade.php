@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark
])

<span {{ $attributes->merge(['class' => 'badge bg-' . $variant]) }}>
    {{ $slot }}
</span>