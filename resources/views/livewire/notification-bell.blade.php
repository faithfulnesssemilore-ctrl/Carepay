<div class="relative" x-data @click.outside="$wire.close()">

    {{-- Bell button --}}
    <button
        wire:click="toggleDropdown"
        class="relative p-2 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm hover:border-purple-400 transition"
    >
        🔔
        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    @if ($open)
        <div class="absolute right-0 top-12 w-80 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-xl z-50 overflow-hidden">

            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <p class="font-semibold text-sm text-gray-800 dark:text-white">Notifications</p>
            </div>

            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700">
                @forelse ($notifications as $notif)
                    <div class="px-4 py-3 {{ !$notif['read'] ? 'bg-purple-50 dark:bg-purple-900/20' : '' }}">
                        <div class="flex items-start gap-3">
                            {{-- Icon based on type --}}
                            <span class="text-lg shrink-0 mt-0.5">
                                @if (str_contains($notif['type'], 'deposit'))   💰
                                @elseif (str_contains($notif['type'], 'sent'))  📤
                                @elseif (str_contains($notif['type'], 'received')) 📥
                                @else 🔔
                                @endif
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-700 dark:text-gray-200 leading-snug">
                                    {{ $notif['message'] }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notif['time'] }}</p>
                            </div>
                            @if (!$notif['read'])
                                <div class="w-2 h-2 bg-purple-500 rounded-full shrink-0 mt-1.5"></div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-gray-400 text-sm">
                        <p class="text-3xl mb-2">📭</p>
                        No notifications yet
                    </div>
                @endforelse
            </div>

        </div>
    @endif

</div>
