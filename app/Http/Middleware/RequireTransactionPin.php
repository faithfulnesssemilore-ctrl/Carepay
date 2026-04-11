<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTransactionPin
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('pin_verified')) {
            return redirect()->route('pin.verify');
        }

        $verifiedAt = session('pin_verified_at');

        if (!$verifiedAt || now()->diffInMinutes($verifiedAt) > 1) {//this means the pin verification is valid for 1 minutes, after that the user will be required to verify their pin again before making another transaction. You can adjust this time as needed.
            session()->forget(['pin_verified', 'pin_verified_at']);
            return redirect()->route('pin.verify');
        }

        return $next($request);
    }
}