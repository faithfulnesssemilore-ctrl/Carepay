@props([
    'as' => 'button',
    'dismiss' => 'modal', // could be 'modal' or leave empty for custom
])

<{{ $as }}
    type="{{ $as === 'button' ? 'button' : '' }}"
    @if($dismiss) data-bs-dismiss="{{ $dismiss }}" @endif
    {{ $attributes->merge(['class' => 'btn-close']) }}
    aria-label="Close"
>
    @if($as !== 'button'){{ $slot }}@endif
</{{ $as }}>