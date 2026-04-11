@props([])

<p {{ $attributes->merge(['class' => 'card-text text-muted-custom']) }}>
    {{ $slot }}
</p>