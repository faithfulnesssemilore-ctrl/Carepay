<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'CarePay' }}</title>
    @vite(['resources/css/app.css', 'resources/css/bootstrap.css', 'resources/css/custom.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @livewireStyles
</head>
<body style="background:#0a0a0f;color:white;min-height:100vh;overflow-x:hidden;">

{{-- desktop and tablet sidebar --}}
<div class="d-none d-md-flex app-desktop-shell" style="min-height:100vh;">

    {{-- sidebar --}}
    <div class="d-flex flex-column app-sidebar"
         style="background:#0f0f1a;border-right:1px solid rgba(168,85,247,0.12);
                position:fixed;top:0;left:0;height:100vh;z-index:100;padding:24px 16px;overflow-y:auto;">

        {{-- logo --}}
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none mb-5">
            <div class="icon-container icon-container-sm gradient-bg-primary">
                <x-lucide-wallet style="width:16px;height:16px;color:white;" />
            </div>
            <span class="gradient-text fw-bold fs-5">CarePay</span>
        </a>

        {{-- nav links --}}
        <nav class="d-flex flex-column gap-1 flex-fill">
            @php
                $navItems = [
                    ['route' => 'dashboard',    'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
                    ['route' => 'wallet',        'icon' => 'wallet',           'label' => 'Wallet'],
                    ['route' => 'send-money',    'icon' => 'send',             'label' => 'Send Money'],
                    ['route' => 'add-money',     'icon' => 'plus-circle',      'label' => 'Add Money'],
                    ['route' => 'transactions',  'icon' => 'list',             'label' => 'Transactions'],
                    ['route' => 'bill-payment',  'icon' => 'receipt',          'label' => 'Pay Bills'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="d-flex align-items-center gap-3 text-decoration-none px-3 py-2 rounded-xl"
                   style="transition:all 0.2s;
                          background:{{ $active ? 'rgba(168,85,247,0.15)' : 'transparent' }};
                          color:{{ $active ? '#a855f7' : 'rgba(255,255,255,0.5)' }};">
                    <x-dynamic-component :component="'lucide-' . $item['icon']"
                        style="width:18px;height:18px;flex-shrink:0;" />
                    <span style="font-size:14px;font-weight:{{ $active ? '600' : '400' }};">
                        {{ $item['label'] }}
                    </span>
                    @if($active)
                    <div style="width:3px;height:16px;background:#a855f7;border-radius:2px;margin-left:auto;"></div>
                    @endif
                </a>
            @endforeach

            {{-- divider --}}
            <div style="height:1px;background:rgba(255,255,255,0.06);margin:12px 0;"></div>

            <a href="{{ route('profile') }}"
               class="d-flex align-items-center gap-3 text-decoration-none px-3 py-2 rounded-xl"
               style="color:{{ request()->routeIs('profile') ? '#a855f7' : 'rgba(255,255,255,0.5)' }};
                      background:{{ request()->routeIs('profile') ? 'rgba(168,85,247,0.15)' : 'transparent' }};">
                <x-lucide-user style="width:18px;height:18px;" />
                <span style="font-size:14px;">Profile</span>
            </a>

            <a href="{{ route('settings') }}"
               class="d-flex align-items-center gap-3 text-decoration-none px-3 py-2 rounded-xl"
               style="color:{{ request()->routeIs('settings') ? '#a855f7' : 'rgba(255,255,255,0.5)' }};
                      background:{{ request()->routeIs('settings') ? 'rgba(168,85,247,0.15)' : 'transparent' }};">
                <x-lucide-settings style="width:18px;height:18px;" />
                <span style="font-size:14px;">Settings</span>
            </a>
        </nav>

        {{-- user info + logout at bottom --}}
        <div style="border-top:1px solid rgba(255,255,255,0.06);padding-top:16px;">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle gradient-bg-primary d-flex align-items-center justify-content-center fw-bold text-white"
                     style="width:34px;height:34px;font-size:13px;flex-shrink:0;">
                    {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
                </div>
                <div style="overflow:hidden;">
                    <div style="font-size:13px;font-weight:600;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ Auth::user()->first_name ?? 'User' }} {{ Auth::user()->last_name ?? '' }}
                    </div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ Auth::user()->email ?? '' }}
                    </div>
                </div>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit"
                        class="d-flex align-items-center gap-2 w-100 border-0 bg-transparent px-3 py-2 rounded-xl text-start"
                        style="color:rgba(239,68,68,0.7);font-size:13px;transition:all 0.2s;"
                        onmouseover="this.style.background='rgba(239,68,68,0.1)'"
                        onmouseout="this.style.background='transparent'">
                    <x-lucide-log-out style="width:16px;height:16px;" />
                    Logout
                </button>
            </form>
        </div>
    </div>

    {{-- main content area --}}
    <div class="app-main-content">

        {{-- top bar --}}
        <div class="app-topbar" style="background:rgba(10,10,15,0.8);backdrop-filter:blur(10px);
                    border-bottom:1px solid rgba(168,85,247,0.1);position:sticky;top:0;z-index:50;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div style="font-size:18px;font-weight:700;color:white;">
                        @yield('page-title', ucfirst(str_replace('-', ' ', request()->segment(1) ?: 'Dashboard')))
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="border-0 bg-transparent p-1" style="color:rgba(255,255,255,0.5);">
                        <x-lucide-bell style="width:20px;height:20px;" />
                    </button>
                    <a href="{{ route('profile') }}" class="text-decoration-none">
                        <div class="rounded-circle gradient-bg-primary d-flex align-items-center justify-content-center fw-bold text-white"
                             style="width:34px;height:34px;font-size:13px;">
                            {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
                        </div>
                    </a>
                </div>
            </div>
        </div>

        {{-- page content --}}
        <div class="app-main-content-inner" style="padding:32px;">
            {{ $slot }}
        </div>
    </div>
</div>

{{-- mobile layout --}}
<div class="d-md-none app-mobile-shell">

    {{-- mobile top header --}}
    <div style="background:rgba(10,10,15,0.95);backdrop-filter:blur(10px);
                border-bottom:1px solid rgba(168,85,247,0.1);
                padding:12px 16px;position:sticky;top:0;z-index:100;">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="icon-container icon-container-sm gradient-bg-primary">
                    <x-lucide-wallet style="width:14px;height:14px;color:white;" />
                </div>
                <span class="gradient-text fw-bold" style="font-size:16px;">CarePay</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button class="border-0 bg-transparent p-1" style="color:rgba(255,255,255,0.5);">
                    <x-lucide-bell style="width:18px;height:18px;" />
                </button>
                <a href="{{ route('profile') }}" class="text-decoration-none">
                    <div class="rounded-circle gradient-bg-primary d-flex align-items-center justify-content-center fw-bold text-white"
                         style="width:30px;height:30px;font-size:11px;">
                        {{ strtoupper(substr(Auth::user()->first_name ?? 'U', 0, 1)) }}
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- mobile page content --}}
    <div style="padding:16px;padding-bottom:calc(72px + env(safe-area-inset-bottom));">
        {{ $slot }}
    </div>

    {{-- mobile bottom nav --}}
    <nav style="position:fixed;bottom:0;left:0;right:0;z-index:200;
                background:rgba(10,10,15,0.97);backdrop-filter:blur(20px);
                border-top:1px solid rgba(168,85,247,0.15);
                display:flex;height:calc(64px + env(safe-area-inset-bottom));
                padding-bottom:env(safe-area-inset-bottom);">
        @foreach([
            ['route' => 'dashboard',   'icon' => 'layout-dashboard', 'label' => 'Home'],
            ['route' => 'send-money',  'icon' => 'send',             'label' => 'Send'],
            ['route' => 'add-money',   'icon' => 'plus-circle',      'label' => 'Add'],
            ['route' => 'transactions','icon' => 'list',             'label' => 'History'],
            ['route' => 'profile',     'icon' => 'user',             'label' => 'Profile'],
        ] as $item)
            @php $active = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex-fill d-flex flex-column align-items-center justify-content-center text-decoration-none"
               style="color:{{ $active ? '#a855f7' : '#555' }};">
                <x-dynamic-component :component="'lucide-' . $item['icon']"
                    style="width:20px;height:20px;margin-bottom:2px;" />
                <span style="font-size:9px;font-weight:{{ $active ? '600' : '400' }};">
                    {{ $item['label'] }}
                </span>
            </a>
        @endforeach
    </nav>
</div>

{{-- toast container --}}
<div id="toast-container"
     style="position:fixed;top:20px;right:20px;z-index:9999;
            display:flex;flex-direction:column;gap:8px;max-width:300px;">
</div>

@livewireScripts
@stack('scripts')

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (e) => {
        const colors = {success:'#22c55e',error:'#ef4444',info:'#a855f7',warning:'#f59e0b'};
        const el = document.createElement('div');
        el.style.cssText = `background:#1a1a24;border:1px solid ${colors[e.type] || '#a855f7'};
            border-radius:10px;padding:12px 16px;color:white;font-size:13px;
            box-shadow:0 8px 24px rgba(0,0,0,0.4);display:flex;align-items:center;gap:10px;
            animation:slideIn 0.2s ease;`;
        el.textContent = e.message;
        document.getElementById('toast-container').appendChild(el);
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.3s';
            setTimeout(() => el.remove(), 300);
        }, 4000);
    });
});
</script>

<style>
@keyframes slideIn {
    from { transform:translateX(100%);opacity:0; }
    to   { transform:translateX(0);opacity:1; }
}
</style>

</body>
</html>
