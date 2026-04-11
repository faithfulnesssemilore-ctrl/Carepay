@props([
    'value' => 0,
    'max' => 100,
    'height' => null,
    'animated' => false,
    'striped' => false,
    'variant' => 'primary',
])

<div class="progress" @if($height) style="height: {{ $height }};" @endif>
    <div
        class="progress-bar
            {{ $striped ? 'progress-bar-striped' : '' }}
            {{ $animated ? 'progress-bar-animated' : '' }}
            bg-{{ $variant }}"
        role="progressbar"
        style="width: {{ ($value / $max) * 100 }}%;"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="{{ $max }}"
    >
        {{ $slot }}
    </div>
</div>