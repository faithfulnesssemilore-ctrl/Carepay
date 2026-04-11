<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Mail;
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
   
    public function create(array $input): User
    {
        Validator::make($input, [
            'firstName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        $user = User::create([
            'first_name' => $input['firstName'],
            'last_name'  => $input['lastName'],
            'email'      => $input['email'],
            'phone'      => $input['phone'],
            'password'   => Hash::make($input['password']),
            'role'       => 'user',
        ]);

        // Generate and Send OTP immediately after registration
        $otp = (new Otp)->generate($user->email, 'numeric', 6, 300);
        
        Mail::raw("Your verification code is: {$otp->token}", function ($message) use ($user) {
            $message->to($user->email)->subject('Verify Your Email');
        });

        return $user;
    }
}
