@props([
    'sheet' => '', // id of the offcanvas to open
    'as' => 'button',
    'variant' => 'primary',
])

<{{ $as }}
    type="{{ $as === 'button' ? 'button' : '' }}"
    data-bs-toggle="offcanvas"
    data-bs-target="#{{ $sheet }}"
    aria-controls="{{ $sheet }}"
    {{ $attributes->merge(['class' => 'btn btn-' . $variant]) }}
>
    {{ $slot }}
</{{ $as }}>