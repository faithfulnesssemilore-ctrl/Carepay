<?php

namespace App\Http\Middleware;

use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('Idempotency-Key');
        // if the request does not have an idempotency key, we can proceed with the request as normal
        if (! $idempotencyKey) {
            return $next($request);
        }
        // Check if the idempotency key already exists in the database
        $existingTransaction = Transaction::where('idempotency_key', $idempotencyKey)->first();
        if ($existingTransaction) {
            // If a transaction with the same idempotency key exists, return the existing transaction response
            return response()->json([
                'message' => 'Duplicate transaction detected',
                'transaction' => $existingTransaction,
            ], 409); // 409 Conflict status code

        }

        return $next($request);
    }
}
