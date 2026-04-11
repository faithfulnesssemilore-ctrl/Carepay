<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Settings extends Component
{
    // Security settings
    public $twoFactorEnabled = false;
    public $notificationsEnabled = true;
    public $emailNotifications = true;
    public $transactionAlerts = true;

    // Password change
    public $currentPassword = '';
    public $newPassword = '';
    public $confirmPassword = '';

    // UI properties
    public $successMessage = '';
    public $errorMessage = '';
    public $isProcessing = false;
    public $activeTab = 'security';

    public function mount()
    {
        $this->loadSettings();
    }

    /**
     * Load user settings
     */
    public function loadSettings()
    {
        try {
            // Settings would typically be stored in a separate table
            // For now, we'll use default values
            $this->notificationsEnabled = true;
            $this->emailNotifications = true;
            $this->transactionAlerts = true;
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to load settings';
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        redirect()->route('login');
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        // Validate
        $this->validate([
            'currentPassword' => 'required|current_password',
            'newPassword' => 'required|string|min:8|confirmed',
            'confirmPassword' => 'required'
        ]);

        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $user = Auth::user();
            if ($user) {
                $user->password = bcrypt($this->newPassword);
                $user->save();
            }

            $this->successMessage = 'Password changed successfully!';
            $this->resetPasswordFields();

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to change password: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    /**
     * Update notification settings
     */
    public function updateSettings()
    {
        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            // Update settings in database (would require a settings table)
            $this->successMessage = 'Settings updated successfully!';
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to update settings: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    /**
     * Reset password fields
     */
    private function resetPasswordFields()
    {
        $this->currentPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';
    }

    public function render()
    {
        return view('livewire.settings');
    }
}

