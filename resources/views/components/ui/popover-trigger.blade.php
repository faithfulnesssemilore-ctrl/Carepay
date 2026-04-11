@props([
    'title' => '',
    'content' => '',
    'placement' => 'top', // top, bottom, left, right
    'trigger' => 'click', // click, hover, focus, manual
    'html' => false,
    'as' => 'button',
])

<{{ $as }}
    type="{{ $as === 'button' ? 'button' : '' }}"
    data-bs-toggle="popover"
    data-bs-title="{{ $title }}"
    data-bs-content="{{ $content }}"
    data-bs-placement="{{ $placement }}"
    data-bs-trigger="{{ $trigger }}"
    @if($html) data-bs-html="true" @endif
    {{ $attributes->merge(['class' => 'btn btn-outline-secondary']) }}
>
    {{ $slot }}
</{{ $as }}>