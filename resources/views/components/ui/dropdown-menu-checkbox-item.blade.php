@props([
    'checked' => false,
    'value' => '',
    'disabled' => false,
])

@php
    $id = 'checkbox-' . uniqid();
@endphp

<div
    x-data="{ checked: @json($checked) }"
    class="dropdown-item d-flex align-items-center gap-2"
    :class="{ 'active': checked }"
    @click="checked = !checked; $dispatch('input', { value: '{{ $value }}', checked })"
    role="menuitemcheckbox"
    :aria-checked="checked"
    tabindex="0"
    {{ $disabled ? 'aria-disabled="true"' : '' }}
>
    <div class="form-check" @click.stop>
        <input
            type="checkbox"
            class="form-check-input"
            :checked="checked"
            value="{{ $value }}"
            x-model="checked"
            {{ $disabled ? 'disabled' : '' }}
            id="{{ $id }}"
        >
    </div>
    <label class="form-check-label grow" for="{{ $id }}">
        {{ $slot }}
    </label>
    {{ $shortcut ?? '' }}
</div>