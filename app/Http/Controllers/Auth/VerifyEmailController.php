<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Verify the user's email using the signed URL hash
     */
    public function verify(Request $request, $user, $hash): RedirectResponse
    {
        // Find the user
        $user = User::findOrFail($user);

        // Verify the hash matches
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return redirect()->route('login')
                ->with('error', 'Invalid verification link');
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('dashboard')
            ->with('success', 'Email verified successfully');
    }

    /**
     * Resend the verification email
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard')
                ->with('info', 'Email already verified');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Verification email resent');
    }
}
