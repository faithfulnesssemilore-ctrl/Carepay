@props([
    'value' => '',
    'label' => null,
])

<button
    type="button"
    class="btn btn-outline-primary"
    :class="{ 'active': (type === 'radio' ? value === @json($value) : (Array.isArray(value) && value.includes(@json($value)))) }"
    @click="toggle(@json($value))"
>
    {{ $label ?? $slot }}
</button>