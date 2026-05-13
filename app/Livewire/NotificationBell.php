<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    // How many unread notifications
    public int $unreadCount = 0;

    // The list to show in the dropdown
    public $notifications = [];

    // Whether the dropdown is open
    public bool $open = false;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = Auth::user();

        // Get last 10 notifications
        $this->notifications = $user->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'      => $n->id,
                'message' => $n->data['message'] ?? 'Notification',
                'type'    => $n->data['type'] ?? 'general',
                'read'    => !is_null($n->read_at),
                'time'    => $n->created_at->diffForHumans(),
            ])
            ->toArray();

        // Count unread ones
        $this->unreadCount = $user->unreadNotifications()->count();
    }

    // Mark all as read when user opens the dropdown
    public function toggleDropdown(): void
    {
        $this->open = !$this->open;

        if ($this->open) {
            // Mark all as read
            Auth::user()->unreadNotifications()->update(['read_at' => now()]);
            $this->unreadCount = 0;

            // Reload so they show as read
            $this->loadNotifications();
        }
    }

    // Close the dropdown
    public function close(): void
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
