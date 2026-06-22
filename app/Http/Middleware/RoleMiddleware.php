<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Account suspended check (FINTECH CRITICAL)
        if ($user->status !== 'active') {
            abort(403, 'Account suspended');
        }

        // Email verification check
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        // Role levels: 0=user, 1=admin, 2=super_admin
        $roleLevels = [
            'user' => 0,
            'admin' => 1,
            'super_admin' => 2,
        ];

        if (! isset($roleLevels[$role])) {
            abort(500, 'Invalid role middleware');
        }

        // $user->role is stored as integer (0, 1, or 2)
        $requiredLevel = $roleLevels[$role];
        $userLevel = (int) $user->role;

        if ($userLevel < $requiredLevel) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
