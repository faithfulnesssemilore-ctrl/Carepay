<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TwoFactorPage extends Component
{
    public $code = '';
    public $step = 'verify'; // verify, backup, success
    public $errorMessage = '';
    public $successMessage = '';
    public $isProcessing = false;
    public $showBackupCodes = false;
    public $backupCodes = [];

    public function mount()
    {
        // Initialize backup codes if needed
        $this->backupCodes = [
            'CARE-12345-67890',
            'CARE-98765-43210',
            'CARE-56789-01234',
            'CARE-34567-89012',
            'CARE-11111-22222',
        ];
    }

    public function verifyCode()
    {
        $this->validate([
            'code' => 'required|digits:6',
        ]);

        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            // Simulate 2FA verification
            // In real implementation, verify against authenticator app
            if ($this->code === '000000') { // Dummy code for testing
                $this->step = 'success';
                $this->successMessage = 'Two-factor authentication verified successfully!';
            } else {
                $this->errorMessage = 'Invalid verification code. Please try again.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Verification failed: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    public function useBackupCode()
    {
        $this->step = 'backup';
        $this->showBackupCodes = true;
    }

    public function goBackToVerify()
    {
        $this->step = 'verify';
        $this->showBackupCodes = false;
        $this->code = '';
        $this->errorMessage = '';
    }

    public function completeVerification()
    {
        // Redirect to dashboard after successful 2FA
        return redirect('/app');
    }

    public function render()
    {
        return view('livewire.two-factor-page');
    }
}
