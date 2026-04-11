@props([])

<span {{ $attributes->merge(['class' => 'ms-auto text-muted-custom small']) }}>
    {{ $slot }}
</span>