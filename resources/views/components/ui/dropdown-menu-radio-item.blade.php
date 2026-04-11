@props([
    'value' => '',
    'disabled' => false,
])

@php
    $id = 'radio-' . uniqid();
@endphp

<div
    x-data="{}"
    class="dropdown-item d-flex align-items-center gap-2"
    :class="{ 'active': selected === @json($value) }"
    @click="selected = @json($value); $dispatch('input', @json($value))"
    role="menuitemradio"
    :aria-checked="selected === @json($value)"
    tabindex="0"
    {{ $disabled ? 'aria-disabled="true"' : '' }}
>
    <div class="form-check" @click.stop>
        <input
            type="radio"
            class="form-check-input"
            name="radio-group"
            :checked="selected === @json($value)"
            value="{{ $value }}"
            x-model="selected"
            {{ $disabled ? 'disabled' : '' }}
            id="{{ $id }}"
        >
    </div>
    <label class="form-check-label grow" for="{{ $id }}">
        {{ $slot }}
    </label>
    {{ $shortcut ?? '' }}
</div>