@props([
    'collapsed' => false, // initial state
    'width' => '280px',
    'collapsedWidth' => '80px',
])

<div
    x-data="{
        collapsed: @json($collapsed),
        mobileOpen: false,
        toggle() { this.collapsed = !this.collapsed; },
        closeMobile() { this.mobileOpen = false; }
    }"
    @resize.window="if (window.innerWidth >= 768) mobileOpen = false"
    class="d-flex"
    style="min-height: 100vh;"
    {{ $attributes }}
>
    <!-- Desktop sidebar -->
    <div
        class="bg-dark-custom text-white shadow-lg"
        :class="{ 'collapsed': collapsed }"
        style="transition: width 0.3s ease; width: {{ $width }}; {{ $collapsedWidth ? 'overflow: hidden;' : '' }}"
        :style="collapsed ? 'width: {{ $collapsedWidth }};' : ''"
        x-show="!mobileOpen"
        x-cloak
    >
        <div class="p-3 d-flex align-items-center justify-content-between">
            <h4 class="m-0" x-show="!collapsed">CarePay</h4>
            <button @click="toggle" class="btn btn-sm btn-outline-light">
                <i class="bi" :class="collapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
            </button>
        </div>

        <div class="sidebar-content overflow-auto" style="max-height: calc(100vh - 70px);">
            {{ $slot }}
        </div>
    </div>

    <!-- Mobile offcanvas trigger -->
    <button
        class="btn btn-primary d-md-none position-fixed bottom-0 end-0 m-3 rounded-circle"
        style="z-index: 1050; width: 50px; height: 50px;"
        @click="mobileOpen = true"
    >
        <i class="bi bi-list"></i>
    </button>

    <!-- Mobile offcanvas -->
    <div
        x-show="mobileOpen"
        x-cloak
        class="offcanvas offcanvas-start show d-md-none"
        tabindex="-1"
        style="width: {{ $width }};"
        @click.away="mobileOpen = false"
    >
        <div class="offcanvas-header bg-dark-custom text-white">
            <h5 class="offcanvas-title">CarePay</h5>
            <button type="button" class="btn-close btn-close-white" @click="mobileOpen = false"></button>
        </div>
        <div class="offcanvas-body bg-dark-custom text-white p-0">
            {{ $slot }}
        </div>
    </div>
</div>