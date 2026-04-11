@props([
    'striped' => false,
    'bordered' => false,
    'hover' => false,
    'responsive' => false,
])

@php
    $classes = 'table';
    if ($striped) $classes .= ' table-striped';
    if ($bordered) $classes .= ' table-bordered';
    if ($hover) $classes .= ' table-hover';
@endphp

<div class="{{ $responsive ? 'table-responsive' : '' }}">
    <table {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </table>
</div>