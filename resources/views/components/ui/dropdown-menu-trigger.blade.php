@props([
    'as' => 'button',
    'variant' => 'secondary', // or use btn-outline, etc.
])

<{{ $as }}
    type="{{ $as === 'button' ? 'button' : '' }}"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    {{ $attributes->merge(['class' => 'btn btn-' . $variant . ' dropdown-toggle']) }}
>
    {{ $slot }}
</{{ $as }}>