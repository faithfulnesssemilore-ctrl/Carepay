<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class PinService
{
    // Max wrong attempts before lockout
    const MAX_ATTEMPTS = 5;

    // Lockout time in minutes
    const LOCKOUT_MINUTES = 15;

    // Verify the user's PIN
    // Tracks failed attempts and locks the user out if needed
    public function verify(User $user, string $pin): bool
    {
        $cacheKey = "pin_attempts_{$user->id}";

        // Check if user is locked out
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            throw new Exception('Too many wrong PIN attempts. Please wait '.self::LOCKOUT_MINUTES.' minutes.');
        }

        // Check if PIN is correct
        if (! Hash::check($pin, $user->pin)) {
            // Wrong PIN — add to attempt count
            Cache::put($cacheKey, $attempts + 1, now()->addMinutes(self::LOCKOUT_MINUTES));
            $remaining = self::MAX_ATTEMPTS - ($attempts + 1);
            throw new Exception("Wrong PIN. {$remaining} attempt(s) remaining.");
        }

        // Correct PIN — clear the failed attempts
        Cache::forget($cacheKey);

        return true;
    }

    // Set or update a user's PIN
    public function setPin(User $user, string $newPin): void
    {
        if (strlen($newPin) !== 4 || ! ctype_digit($newPin)) {
            throw new Exception('PIN must be exactly 4 digits.');
        }

        $user->update(['pin' => Hash::make($newPin)]);
    }

    // Check how many attempts are left
    public function attemptsRemaining(User $user): int
    {
        $cacheKey = "pin_attempts_{$user->id}";
        $attempts = Cache::get($cacheKey, 0);

        return max(0, self::MAX_ATTEMPTS - $attempts);
    }
}
