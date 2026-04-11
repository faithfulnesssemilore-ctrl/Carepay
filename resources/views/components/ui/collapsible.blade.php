@props([
    'id' => 'collapse-' . uniqid(),
    'show' => false,
])

<div class="collapse {{ $show ? 'show' : '' }}" id="{{ $id }}">
    {{ $slot }}
</div>