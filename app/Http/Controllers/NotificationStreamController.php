<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationStreamController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $lastNotificationId = request()->query('last_id', 0);

        return response()->stream(function () use ($user, $lastNotificationId) {
            while (true) {
                // Get new notifications since last check
                $notifications = $user->unreadNotifications()
                    ->where('id', '>', $lastNotificationId)
                    ->latest()
                    ->get();

                if ($notifications->count() > 0) {
                    echo 'data: '.json_encode([
                        'unread_count' => $user->unreadNotifications()->count(),
                        'notifications' => $notifications->map(fn ($n) => [
                            'id' => $n->id,
                            'message' => $n->data['message'] ?? 'New notification',
                            'type' => $n->data['type'] ?? 'transaction',
                            'created_at' => $n->created_at->diffForHumans(),
                        ]),
                    ])."\n\n";
                    flush();
                    $lastNotificationId = $notifications->first()->id;
                }

                sleep(1);
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
