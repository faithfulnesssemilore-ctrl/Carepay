<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EnsureAccountIsActive
{
    private const INACTIVITY_SECONDS = 300; // 5 minutes

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            try {
                $status = $user->getAttribute('status');
            } catch (MissingAttributeException $e) {
                $status = null;
            }

            if ($status !== null && $status !== 'active') {
                Auth::logout();

                return redirect()->route('login')
                    ->with('error', 'Account suspended. Contact support.');
            }

            $lastActivity = session('last_activity');

            if ($lastActivity instanceof Carbon) {
                $lastActivity = $lastActivity->timestamp;
            }

            if ($lastActivity && (time() - (int) $lastActivity) > self::INACTIVITY_SECONDS) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'You have been logged out due to inactivity. Please sign in again.');
            }

            $request->session()->put('last_activity', now());
        }

        return $next($request);
    }
}
