@props(['label' => ''])

<li class="nav-item mt-3">
    <span class="nav-link small text-muted text-uppercase fw-bold" x-show="!$root.closest('.collapsed')">
        {{ $label }}
    </span>
</li>
{{ $slot }}