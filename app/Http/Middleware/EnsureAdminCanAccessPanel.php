<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminCanAccessPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/login')) {
            return response()->view('auth.admin-login');
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            if (! auth()->check()) {
                return redirect()->route('admin.login');
            }
        }

        return $next($request);
    }
}
