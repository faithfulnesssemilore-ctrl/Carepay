@props([
    'variant' => 'default', // default, luxury
    'hover' => false, // lift, scale, none
])

@php
    $classes = ['card'];
    
    // Add custom variant classes
    if ($variant === 'luxury') {
        $classes[] = 'card-luxury';
    }
    
    // Add hover effects
    if ($hover === 'lift') {
        $classes[] = 'hover-lift';
    } elseif ($hover === 'scale') {
        $classes[] = 'hover-scale';
    }
    
    $class = implode(' ', $classes);
@endphp

<div {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</div>