<?php

namespace App\Livewire\UserAuth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Register extends Component
{
    use WithFileUploads; // Trait for handling file uploads in Livewire
    // Declare public properties for the form steps and current step
    public int $currentStep = 1;

    // Declare public properties for form data binding
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $pin = '';
    public string $pin_confirmation = '';
        public $idDocument = null;
    public string $idType = '';
    public string $idNumber = '';
        public bool $termsAccepted = false;
public bool $kycVerified = false;

    // OTP related properties
    public string $verificationCode = '';
    public bool $emailVerified = false;
    public bool $verificationSent = false;
    public int $otpAttempts = 0;
    public $lastOtpSentAt = null;
    public bool $canResendOtp = true;
public bool $otpVerified = false;

    // Method to go to the next step
    public function nextStep()
    {
        if ($this->currentStep === 1) {
                        $this->validate([
                'firstName' => 'required|string|min:2|max:255',
                'lastName'  => 'required|string|min:2|max:255',
                'email'     => 'required|email|unique:users,email',
                'phone'     => 'required|string|min:11|max:20',
            ]);

                        $this->sendOtp();
            $this->currentStep++;
            return;
        }

                if ($this->currentStep === 2) {
// Check if OTP is verified before proceeding
            if (!$this->otpVerified) {
                $this->addError('verificationCode', 'Please verify your email code first.');
                return;
            }

            $this->currentStep++;
            return;
        }

                if ($this->currentStep === 3) {
            $this->validate([
                'idDocument' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                'idType' => 'required|in:passport,drivers_license,national_id',
                'idNumber' => 'required|string|max:255',
            ]);
                    }

                if ($this->currentStep === 4) {
            $this->validate([
                'pin' => 'required|digits:4|confirmed',
            ]);
                    }

                if ($this->currentStep === 5) {
            $this->validate([
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)->mixedCase()->numbers()->symbols(),
                ],
            ]);
                    }

                if ($this->currentStep === 6) {
            $this->validate([
                'termsAccepted' => 'accepted',
            ]);

            $this->submit();
            return;
        }

        $this->currentStep++;
    }

    // Method to go to the previous step
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;

            // Reset OTP verification if going back to step 1
            if ($this->currentStep == 1) {
                $this->otpVerified = false;
                $this->verificationCode = null;
                $this->resetValidation('verificationCode');
            }
        }
    }

    // Method to send OTP to user's email for verification
    public function sendOtp()
    {
        // Check if we can resend (60 seconds cooldown)
        if ($this->lastOtpSentAt && Carbon::parse($this->lastOtpSentAt)->addSeconds(60)->isFuture()) {
            $this->addError('verificationCode', 'Please wait before requesting a new code.');
            return;
        }

                $otp = (new Otp)->generate($this->email, 'numeric', 6, 300);         // 5 minutes = 300 seconds

        // Send email
        Mail::raw("Your verification code is: {$otp->token}", function ($message) {
            $message->to($this->email)->subject('Email Verification Code');
        });

        $this->verificationSent = true;
        $this->lastOtpSentAt = now();
        $this->otpAttempts = 0; // Reset attempts on new code
        $this->otpVerified = false; // Reset verification status
        $this->canResendOtp = false;

        // Re-enable resend after 60 seconds
        $this->canResendOtp = false;
$this->lastOtpSentAt = now();
    }

    // Method to verify OTP code
    public function verifyOtp()
    {
        $this->validate([
            'verificationCode' => 'required|digits:6',
        ]);

        // Check attempt limit
        if ($this->otpAttempts >= 5) {
            $this->addError('verificationCode', 'Too many failed attempts. Please request a new code.');
            return;
        }

                $otp = new Otp();
        $result = $otp->validate($this->email, $this->verificationCode);

        if (!$result->status) {
            $this->otpAttempts++;
                        $this->addError('verificationCode', 'Invalid or expired OTP. ' . (5 - $this->otpAttempts) . ' attempts remaining.');
            return;
        }

        // OTP is valid
        $this->emailVerified = true;
        $this->otpVerified = true;

        // Clear any previous errors
        $this->resetValidation('verificationCode');

        // Show success message
        session()->flash('otp-success', 'Email verified successfully! You can now continue with your registration.');

        // Move to next step automatically
        $this->currentStep = 3;
        return;
    }

    // Method to enable resend button
    public function enableResend()
    {
        $this->canResendOtp = true;
    }
         public function create(){ // This create document  
            $validate = $this->validate();
            if ($this->idDocument) {
                $validate['id_document'] = $this->idDocument->store('id_documents', 'public');
            }

         }


    // Method to submit the registration
    public function submit()
    {
                $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'pin' => 'required|digits:4|confirmed',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'idDocument' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'idType' => 'required|in:passport,drivers_license,national_id',
            'idNumber' => 'required|string|max:255',
            'termsAccepted' => 'accepted',
        ]);

        // 1. Create user first
        $user = \App\Models\User::create([
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
            'id_type' => $this->idType,
            'id_number' => $this->idNumber,
            'kyc_verified' => false,
            'pin' => Hash::make($this->pin),
            'registration_complete' => true,
            'terms_accepted' => $this->termsAccepted,
            'email_verified_at' => now(),
            'role' => 0,
            'status' => 'active',
        ]);

        // 2. Store KYC document
        if ($this->idDocument) {
            $path = $this->idDocument->store('id_documents', 'public');
            $user->update(['id_document' => $path]);
        }

        // 3. Wallet, Virtual Account, and Limits auto-created by User model boot method
        // No need to create them here - they're already created automatically

    // 4. Login
        Auth::login($user);
        
        return redirect()->route('dashboard');
    }
    // Validation messages
    protected $messages = [
        'firstName.required' => 'First name is required.',
        'lastName.required' => 'Last name is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already registered.',
        'phone.required' => 'Phone number is required.',
        'verificationCode.required' => 'Verification code is required.',
        'verificationCode.digits' => 'Verification code must be 6 digits.',
        'pin.required' => 'PIN is required.',
        'pin.digits' => 'PIN must be 4 digits.',
        'pin_confirmation' => 'PIN confirmation does not match.',
        'password.required' => 'Password is required.',
        'password.confirmed' => 'Password confirmation does not match.',
        'idDocument.required' => 'ID document is required.',
        'idDocument.mimes' => 'ID document must be a JPG, JPEG, PNG, or PDF file.',
        'idDocument.max' => 'ID document must be less than 2MB.',
        'idType.required' => 'ID type is required.',
        'idType.in' => 'Please select a valid ID type.',
        'idNumber.required' => 'ID number is required.',
        'termsAccepted.accepted' => 'You must accept the terms and conditions.',
    ];

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.user-auth.register');
    }
}
