@props([
    'width' => '100%',
    'height' => '1rem',
    'rounded' => true,
])

<div
    class="bg-secondary-custom placeholder-wave"
    style="width: {{ $width }}; height: {{ $height }}; {{ $rounded ? 'border-radius: 0.25rem;' : '' }}"
    {{ $attributes }}
>
    <div class="placeholder" style="width: 100%; height: 100%;"></div>
</div>