<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public $notifications = [];

    public bool $open = false;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = Auth::user();
        $this->notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? 'Notification',
                'type' => $n->data['type'] ?? 'general',
                'read' => ! is_null($n->read_at),
                'time' => $n->created_at->diffForHumans(),
            ])
            ->toArray();

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    #[On('notification-received')]
    public function notificationReceived($data): void
    {
        $this->unreadCount = $data['unread_count'] ?? 0;
        $this->loadNotifications();
        $this->dispatch('show-notification-toast', data: $data);
    }

    public function toggleDropdown(): void
    {
        $this->open = ! $this->open;

        if ($this->open) {
            Auth::user()->unreadNotifications()->update(['read_at' => now()]);
            $this->unreadCount = 0;
            $this->loadNotifications();
        }
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
