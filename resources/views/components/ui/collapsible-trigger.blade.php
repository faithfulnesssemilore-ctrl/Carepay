@props([
    'target' => '',
    'as' => 'button',
])

<{{ $as }}
    type="{{ $as === 'button' ? 'button' : '' }}"
    data-bs-toggle="collapse"
    data-bs-target="#{{ $target }}"
    aria-expanded="false"
    aria-controls="{{ $target }}"
    {{ $attributes->merge(['class' => 'btn btn-outline-primary']) }}
>
    {{ $slot }}
</{{ $as }}>