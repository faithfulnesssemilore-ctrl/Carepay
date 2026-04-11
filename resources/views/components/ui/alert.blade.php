@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark
    'dismissible' => false,
])

<div
    {{ $attributes->merge(['class' => 'alert alert-' . $variant . ($dismissible ? ' alert-dismissible' : '')]) }}
    role="alert"
>
    {{ $slot }}
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>