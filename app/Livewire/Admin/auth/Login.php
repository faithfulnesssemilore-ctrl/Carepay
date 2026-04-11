<?php
namespace App\Livewire\UserAuth;

use App\Models\User;
use Livewire\Component;

class Login extends Component
{

    public $email;
    public $password;
     public function login()
    {
        $credentials = [
            'email' => $this->email,
            'password' => $this->password
        ];

        // Attempt to log the user in
        if (auth()->attempt($credentials)) {
            // Redirect to the intended page or a default page
            return redirect()->intended('/dashboard');
        }

        // If login fails, show an error message
        $this->addError('email', 'These credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.user-auth.login');
    }
}
