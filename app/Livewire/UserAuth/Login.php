<?php

namespace App\Livewire\UserAuth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email;
    public $password;
    public $remember = false;

    public $isLoading = false;
    public $errorMessage = null;

    public function login()
    {
        $this->isLoading = true;
        $this->errorMessage = null;

        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt([
            'email' => $this->email,
            'password' => $this->password
        ], $this->remember)) {

            $this->errorMessage = 'Invalid email or password';
            $this->isLoading = false;
            return;
        }

        session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.user-auth.login')
            ->layout('components.layouts.app');
    }
}