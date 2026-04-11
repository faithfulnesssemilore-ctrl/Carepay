@props([
    'name' => '',
    'value' => '',
    'checked' => false,
    'disabled' => false,
    'label' => null,
    'id' => null,
])

@php
    $id = $id ?? 'radio-' . uniqid();
@endphp

<div class="form-check">
    <input
        type="radio"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ $value }}"
        class="form-check-input"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
    @if($label)
        <label class="form-check-label" for="{{ $id }}">
            {{ $label }}
        </label>
    @endif
</div>