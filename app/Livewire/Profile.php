<?php

namespace App\Livewire;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Profile extends Component
{
    // User properties
    public $firstName = '';

    public $lastName = '';

    public $email = '';

    public $phone = '';

    public $profilePicture = '';

    // UI properties
    public $successMessage = '';

    public $errorMessage = '';

    public $isProcessing = false;

    public $isEditing = false;

    public function mount()
    {
        $this->loadUserData();
    }

    /**
     * Load user profile data
     */
    public function loadUserData()
    {
        try {
            $user = Auth::user();
            $this->firstName = $user->first_name ?? '';
            $this->lastName = $user->last_name ?? '';
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->profilePicture = $user->profile_picture ?? '';
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to load profile data';
        }
    }

    /**
     * Toggle edit mode
     */
    public function toggleEdit()
    {
        $this->isEditing = ! $this->isEditing;
    }

    /**
     * Update user profile
     */
    public function updateProfile(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        $request = request();
        $name = trim((string) $request->input('name', $this->firstName.' '.$this->lastName));
        $firstName = trim((string) $request->input('firstName', $request->input('first_name', $this->firstName)));
        $lastName = trim((string) $request->input('lastName', $request->input('last_name', $this->lastName)));

        if ($firstName === '' && $lastName === '') {
            $parts = preg_split('/\s+/', $name, 2) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $email = (string) $request->input('email', $this->email);
        $phone = (string) $request->input('phone', $this->phone);

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;

        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $user->forceFill([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
            ])->save();

            $this->firstName = $firstName;
            $this->lastName = $lastName;
            $this->email = $email;
            $this->phone = $phone;
            $this->successMessage = 'Profile updated successfully!';
            $this->isEditing = false;

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update profile: '.$e->getMessage();
        }

        $this->isProcessing = false;

        return redirect()->route('profile');
    }

    public function deleteAccount(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        $password = request('password');

        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'userDeletion.password' => ['The provided password is incorrect.'],
            ])->errorBag('userDeletion');
        }

        $user->bankAccounts()->delete();
        $user->virtualAccount()->delete();
        $user->wallet()->delete();
        $user->limits()->delete();
        $user->transactions()->delete();
        $user->delete();

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
