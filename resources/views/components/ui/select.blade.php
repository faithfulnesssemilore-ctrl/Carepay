@props([
    'name' => '',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
])

<select
    name="{{ $name }}"
    id="{{ $id ?? $name }}"
    {{ $attributes->merge(['class' => 'form-select']) }}
>
    @if($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach($options as $value => $label)
        <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>