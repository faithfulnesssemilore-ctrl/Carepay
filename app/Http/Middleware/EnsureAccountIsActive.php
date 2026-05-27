<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->status !== 'active') {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Account suspended. Contact support.');
        }

        return $next($request);
    }
}
