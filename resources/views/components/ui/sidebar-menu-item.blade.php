@props([
    'href' => null,
    'active' => false,
    'icon' => null,
    'label' => '',
    'submenu' => false,
    'submenuItems' => [], // array of ['label' => '', 'href' => '']
])

<li class="nav-item {{ $submenu ? 'dropdown' : '' }}" x-data="{ open: false }">
    @if($submenu)
        <a
            href="#"
            class="nav-link d-flex align-items-center"
            @click.prevent="open = !open"
            :class="{ 'active': open }"
        >
            @if($icon)<span class="me-2">{!! $icon !!}</span>@endif
            <span x-show="!$root.closest('.collapsed')">{{ $label }}</span>
            <i class="bi ms-auto" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'" x-show="!$root.closest('.collapsed')"></i>
        </a>
        <ul x-show="open" x-collapse class="nav flex-column ms-3">
            @foreach($submenuItems as $item)
                <li class="nav-item">
                    <a href="{{ $item['href'] }}" class="nav-link small">{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>
    @else
        <a
            href="{{ $href ?? '#' }}"
            class="nav-link d-flex align-items-center"
            :class="{ 'active': {{ $active ? 'true' : 'false' }} }"
        >
            @if($icon)<span class="me-2">{!! $icon !!}</span>@endif
            <span x-show="!$root.closest('.collapsed')">{{ $label }}</span>
        </a>
    @endif
</li>