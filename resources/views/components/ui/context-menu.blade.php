@props([
    'id' => 'context-' . uniqid(),
])

<div
    x-data="{ show: false, x: 0, y: 0 }"
    @contextmenu.prevent="show = true; x = $event.pageX; y = $event.pageY"
    @click.away="show = false"
    @keydown.escape.window="show = false"
    {{ $attributes }}
>
    {{ $slot }}

    <div
        x-show="show"
        x-cloak
        class="dropdown-menu d-block position-fixed"
        :style="{ top: y + 'px', left: x + 'px' }"
        x-transition
    >
        {{ $menu ?? '' }}
    </div>
</div>