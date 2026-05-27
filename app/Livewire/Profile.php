<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
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
    public function updateProfile()
    {
        // Validate input
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $user = Auth::user();
            if ($user) {
                $user->first_name = $this->firstName;
                $user->last_name = $this->lastName;
                $user->phone = $this->phone;
                $user->save();
            }

            $this->successMessage = 'Profile updated successfully!';
            $this->isEditing = false;

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update profile: '.$e->getMessage();
        }

        $this->isProcessing = false;
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
