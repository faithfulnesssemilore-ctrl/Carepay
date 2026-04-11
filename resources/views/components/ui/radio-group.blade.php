@props([
    'name' => '',
])

<div {{ $attributes->merge(['class' => 'd-flex flex-column gap-2']) }}>
    {{ $slot }}
</div>