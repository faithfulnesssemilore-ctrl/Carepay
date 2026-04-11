@props([])

<ul {{ $attributes->merge(['class' => 'nav flex-column']) }}>
    {{ $slot }}
</ul>