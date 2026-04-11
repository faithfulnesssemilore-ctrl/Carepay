@props([
    'title' => '',
    'placement' => 'top',
    'as' => 'span', // often used on icons or text
])

<{{ $as }}
    data-bs-toggle="tooltip"
    data-bs-placement="{{ $placement }}"
    title="{{ $title }}"
    {{ $attributes }}
>
    {{ $slot }}
</{{ $as }}>