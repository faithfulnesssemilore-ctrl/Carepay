@props([
    'modal' => '',
    'as' => 'button',
    'variant' => 'primary',
])

<{{ $as }}
    type="{{ $as === 'button' ? 'button' : '' }}"
    data-bs-toggle="modal"
    data-bs-target="#{{ $modal }}"
    {{ $attributes->merge(['class' => 'btn btn-' . $variant]) }}
>
    {{ $slot }}
</{{ $as }}>