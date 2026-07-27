<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationStreamController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        $lastNotificationId = (int) request()->query('last_id', 0);

        return response()->stream(function () use ($user, $lastNotificationId) {
            $heartbeat = 0;

            while (! connection_aborted()) {
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
                    $lastNotificationId = (int) $notifications->first()->id;
                } elseif ($heartbeat >= 15) {
                    echo "data: {\"type\":\"heartbeat\"}\n\n";
                    flush();
                    $heartbeat = 0;
                }

                $heartbeat++;
                usleep(1000000);
            }
        }, 200, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Content-Type' => 'text/event-stream',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
