@props([
    'defaultValue' => null, // id of default active tab
    'id' => 'tabs-' . uniqid(),
])

<div
    x-data="{ activeTab: '{{ $defaultValue }}' }"
    {{ $attributes->merge(['class' => 'tabs-wrapper']) }}
>
    {{ $slot }}
</div>