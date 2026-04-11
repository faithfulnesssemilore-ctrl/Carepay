@props([
    'active' => false,
    'href' => null,
])

<li class="breadcrumb-item {{ $active ? 'active' : '' }}" {{ $active ? 'aria-current="page"' : '' }}>
    @if($href && !$active)
        <a href="{{ $href }}">{{ $slot }}</a>
    @else
        {{ $slot }}
    @endif
</li>