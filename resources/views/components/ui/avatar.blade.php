@props([
    'src' => null,
    'alt' => '',
    'size' => 'md', // sm, md, lg
    'initials' => null,
])

@php
    $sizeClass = match($size) {
        'sm' => 'width: 32px; height: 32px;',
        'md' => 'width: 40px; height: 40px;',
        'lg' => 'width: 48px; height: 48px;',
        default => 'width: 40px; height: 40px;',
    };
@endphp

<div
    class="rounded-circle d-inline-flex align-items-center justify-content-center overflow-hidden bg-secondary-custom text-white"
    style="{{ $sizeClass }}; {{ $attributes->get('style') }}"
    {{ $attributes->except(['style']) }}
>
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt }}" class="w-100 h-100 object-fit-cover">
    @elseif($initials)
        <span class="fw-bold">{{ $initials }}</span>
    @else
        <span>?</span>
    @endif
</div>