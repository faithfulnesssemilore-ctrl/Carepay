@props(['for' => ''])

<label
    for="{{ $for }}"
    {{ $attributes->merge(['class' => 'form-label']) }}
>
    {{ $slot }}
</label>