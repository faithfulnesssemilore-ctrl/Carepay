@props([
    'name' => '',
    'id' => null,
    'checked' => false,
    'disabled' => false,
    'label' => null,
])

<div class="form-check form-switch">
    <input
        type="checkbox"
        role="switch"
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        class="form-check-input"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
    @if($label)
        <label class="form-check-label" for="{{ $id ?? $name }}">
            {{ $label }}
        </label>
    @endif
</div>