@props([
    'value' => '',
])

<a
    href="#"
    class="list-group-item list-group-item-action"
    x-show="!query || '{{ $value }}'.toLowerCase().includes(query.toLowerCase())"
    @click="$wire.call('runCommand', '{{ $value }}'); open = false"
>
    {{ $slot }}
</a>