<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-100 dark:bg-gray-900 font-sans antialiased flex">

    {{-- Sidebar --}}
    <aside class="w-56 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col shrink-0 min-h-screen">

        {{-- Logo --}}
        <div class="p-5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">A</div>
                <div>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">Admin Panel</p>
                    <p class="text-xs text-gray-400">{{ config('app.name') }}</p>
                </div>
            </div>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 p-4 space-y-1">
            @php
                $links = [
                    ['route' => 'admin.dashboard',    'icon' => '📊', 'label' => 'Dashboard'],
                    ['route' => 'admin.users',        'icon' => '👥', 'label' => 'Users'],
                    ['route' => 'admin.transactions', 'icon' => '💸', 'label' => 'Transactions'],
                    ['route' => 'admin.kyc',          'icon' => '🪪', 'label' => 'KYC'],
                ];
            @endphp

            @foreach ($links as $link)
                <a
                    href="{{ route($link['route']) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                        {{ request()->routeIs($link['route'])
                            ? 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    <span>{{ $link['icon'] }}</span>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Bottom: back to app + logout --}}
        <div class="p-4 border-t border-gray-100 dark:border-gray-700 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                🏠 User App
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition text-left">
                    🚪 Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content area --}}
    <div class="flex-1 overflow-y-auto min-h-screen">

        {{-- Top bar --}}
        <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Logged in as <span class="font-semibold text-gray-700 dark:text-gray-200">{{ auth()->user()->first_name }}</span>
            </p>
            <span class="text-xs bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-3 py-1 rounded-full font-medium">
                Admin
            </span>
        </header>

        {{-- Page content --}}
        <main>
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
