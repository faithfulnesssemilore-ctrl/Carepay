@props([
    'name' => '',
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'value' => null,
])

<input
    type="range"
    name="{{ $name }}"
    class="form-range"
    min="{{ $min }}"
    max="{{ $max }}"
    step="{{ $step }}"
    value="{{ $value ?? $min }}"
    {{ $attributes }}
>