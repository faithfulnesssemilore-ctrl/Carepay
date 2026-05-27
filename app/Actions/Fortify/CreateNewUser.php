<?php

namespace App\Actions\Fortify;

use App\Jobs\CreateVirtualAccountJob;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'firstName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        $user = User::create([
            'first_name' => $input['firstName'],
            'last_name' => $input['lastName'] ?? null,
            'email' => $input['email'],
            'phone' => $input['phone'] ?? null,
            'password' => Hash::make($input['password']),
            'role' => 0,
        ]);

        $otp = (new Otp)->generate($user->email, 'numeric', 6, 300);

        Mail::send('emails.verification', ['token' => $otp->token, 'user' => $user], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Verify your email address');
        });

        CreateVirtualAccountJob::dispatch($user);

        return $user;
    }
}
