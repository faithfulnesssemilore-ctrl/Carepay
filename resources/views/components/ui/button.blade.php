@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark, link
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'as' => 'button', // button, a, span, etc.
])

@php
    // Map size to Bootstrap classes
    $sizeClass = match($size) {
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => '',
    };
    
    // Build class string
    $class = 'btn btn-' . $variant . ' ' . $sizeClass;
@endphp

<{{ $as }}
    type="{{ $as === 'button' ? $type : '' }}"
    {{ $attributes->merge(['class' => $class]) }}
>
    {{ $slot }}
</{{ $as }}>