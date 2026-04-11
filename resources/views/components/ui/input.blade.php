@props([
    'type' => 'text',
    'name' => '',
    'id' => null,
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
])

<input
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $id ?? $name }}"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => 'form-control']) }}
/>