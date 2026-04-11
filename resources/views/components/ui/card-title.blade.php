@props(['as' => 'h4'])

<{{ $as }} {{ $attributes->merge(['class' => 'card-title']) }}>
    {{ $slot }}
</{{ $as }}>
