@props(['as' => 'h5'])

<{{ $as }} {{ $attributes->merge(['class' => 'modal-title']) }}>
    {{ $slot }}
</{{ $as }}>