@props([
    'active' => false,
    'interval' => null,
    'image' => null,
    'alt' => '',
])

<div class="carousel-item {{ $active ? 'active' : '' }}" @if($interval) data-bs-interval="{{ $interval }}" @endif>
    @if($image)
        <img src="{{ $image }}" class="d-block w-100" alt="{{ $alt }}">
    @else
        {{ $slot }}
    @endif
</div>