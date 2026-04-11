<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Account suspended check (FINTECH CRITICAL)
        if ($user->status !== 'active') {
            abort(403, 'Account suspended');
        }

        // Email verification check
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $roleLevels = [
            'user' => 0,
            'admin' => 1,
            'super_admin' => 2,
        ];

        if (!isset($roleLevels[$role])) {
            abort(500, 'Invalid role middleware');
        }

        if ($user->role < $roleLevels[$role]) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}