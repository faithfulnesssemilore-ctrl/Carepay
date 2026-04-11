@props([
    'expand' => 'lg',
    'variant' => 'dark',
    'bg' => 'dark-custom',
])

<nav class="navbar navbar-expand-{{ $expand }} navbar-{{ $variant }} bg-{{ $bg }}" {{ $attributes }}>
    <div class="container-fluid">
        {{ $slot }}
    </div>
</nav>