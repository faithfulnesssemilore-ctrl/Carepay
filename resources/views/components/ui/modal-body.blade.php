@props([])

<div {{ $attributes->merge(['class' => 'modal-body']) }}>
    {{ $slot }}
</div>