<?php

namespace App\Livewire\Security;

use app\Models\User;
use Livewire\Component;

class VerifyPin extends Component
{
    public $pin;

    public function verify()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // track attempts
        $attempts = session()->get('pin_attempts', 0);
        // lockout after 5 attempts
        if ($attempts >= 5) {
            $this->addError('pin', 'Too many attempts. Try again later.');

            return;
        }
        // check pin
        if (! \Illuminate\Support\Facades\Hash::check($this->pin, $user->pin)) {
            session()->put('pin_attempts', $attempts + 1);
            $this->addError('pin', 'Invalid PIN');

            return;
        }

        // reset attempts
        session()->forget('pin_attempts');

        // set verification timestamp
        session([
            'pin_verified' => true,
            'pin_verified_at' => now(),
        ]);

        return redirect()->intended('/dashboard');
        // here i can also set a session variable to indicate that the pin has been verified, and then check for that in the middleware before allowing access to the transaction routes. This way, the user will only have to verify their pin once per session, and not every time they want to make a transaction.
        /*  session()->put('pin_verified', true);
session()->regenerate();
 session()->put('pin_verified', true);
session()->regenerate();*/

    }

    public function render()
    {
        return view('livewire.security.verify-pin');
    }
}
