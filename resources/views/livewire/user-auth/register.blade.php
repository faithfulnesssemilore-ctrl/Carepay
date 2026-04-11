<div>
    {{-- Be present above all else. - Naval Ravikant --}}
    <div class="d-flex align-items-center justify-content-center bg-dark-custom min-vh-100 p-4">
        <div class="mx-auto" style="max-width: 900px; width: 100%;">

            {{-- Header with logo --}}
            <div class="text-center mb-4">
                <a href="/" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-4">
                    <div class="icon-container gradient-bg-primary p-2 rounded shadow">
                        <x-icon name="lucide-wallet" class="text-white" style="width:28px; height:28px;" />
                    </div>
                    <span class="gradient-text fs-3 fw-bold">CarePay</span>
                </a>
                <h1 class="display-6 fw-bold mb-2">Create Your Account</h1>
                <p class="text-muted-custom">Complete the steps to get started</p>
            </div>

            {{-- Progress Steps --}}
            <div class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    @php
                        $steps = [
                            ['number' => 1, 'label' => 'Personal', 'icon' => 'user'],
                            ['number' => 2, 'label' => 'Verification', 'icon' => 'mail'],
                            ['number' => 3, 'label' => 'KYC', 'icon' => 'shield'],
                            ['number' => 4, 'label' => 'PIN', 'icon' => 'key'],
                            ['number' => 5, 'label' => 'Password', 'icon' => 'lock'],
                            ['number' => 6, 'label' => 'Terms', 'icon' => 'file-text'],
                        ];
                    @endphp

                    @foreach ($steps as $index => $step)
                        <div class="d-flex align-items-center flex-fill">
                            <div class="d-flex flex-column align-items-center flex-fill">
                                <div class="rounded-circle d-flex align-items-center justify-content-center
                                    {{ $currentStep >= $step['number'] ? 'bg-primary text-white' : 'bg-card-custom text-muted-custom' }}"
                                    style="width: 40px; height: 40px; border: 2px solid {{ $currentStep >= $step['number'] ? '#a855f7' : '#2a2a3a' }}; transition: 0.3s;">
                                    @if($currentStep > $step['number'])
                                        <x-icon name="lucide-check-circle-2" style="width:20px; height:20px;" />
                                    @else
                                        <x-icon name="lucide-{{ $step['icon'] }}" style="width:20px; height:20px;" />
                                    @endif
                                </div>
                                <span class="small mt-2 d-none d-sm-block text-muted-custom">{{ $step['label'] }}</span>
                            </div>
                            @if(!$loop->last)
                                <div class="flex-fill mx-2" style="height: 2px; background: {{ $currentStep > $step['number'] ? '#a855f7' : '#2a2a3a' }};"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Form Card --}}
            <div class="card card-luxury p-4 shadow-primary border-0">
                <div class="card-body">
                    <form wire:submit.prevent="nextStep">
                        @csrf

                        {{-- Step 1: Personal Information --}}
                        @if($currentStep == 1)
                            <div class="d-flex flex-column gap-4">
                                <div>
                                    <h2 class="h3 fw-bold mb-2">Personal Information</h2>
                                    <p class="text-muted-custom">Let's start with your basic details</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label">First Name</label>
                                        <input type="text" wire:model="firstName" class="form-control bg-secondary-custom rounded-xl py-3 @error('firstName') is-invalid @enderror" placeholder="John" required>
                                        @error('firstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" wire:model="lastName" class="form-control bg-secondary-custom rounded-xl py-3 @error('lastName') is-invalid @enderror" placeholder="Doe" required>
                                        @error('lastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Email Address</label>
                                    <div class="position-relative">
                                        <x-icon name="lucide-mail" class="position-absolute text-muted-custom" style="left:1rem; top:50%; transform:translateY(-50%); width:20px; height:20px;" />
                                        <input type="email" wire:model="email" class="form-control bg-secondary-custom rounded-xl py-3 ps-5 @error('email') is-invalid @enderror" placeholder="john.doe@example.com" required>
                                    </div>
                                    @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="form-label">Phone Number</label>
                                    <div class="position-relative">
                                        <x-icon name="lucide-phone" class="position-absolute text-muted-custom" style="left:1rem; top:50%; transform:translateY(-50%); width:20px; height:20px;" />
                                        <input type="tel" wire:model="phone" class="form-control bg-secondary-custom rounded-xl py-3 ps-5 @error('phone') is-invalid @enderror" placeholder="+1 (555) 000-0000" required>
                                    </div>
                                    @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Step 2: Email OTP Verification --}}
                        @if($currentStep == 2)
                            <div class="d-flex flex-column gap-4">
                                <div>
                                    <h2 class="h3 fw-bold mb-2">Verify Your Email</h2>
                                    <p class="text-muted-custom">We've sent a 6-digit verification code to your email</p>
                                </div>

                                @if($verificationSent)
                                <div class="d-flex align-items-start gap-3 p-3 rounded" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2);">
                                    <x-icon name="lucide-mail" class="text-primary-custom shrink-0 mt-1" style="width:20px; height:20px;" />
                                    <div>
                                        <p class="fw-semibold small mb-1">Email: {{ $email }}</p>
                                        <p class="small mb-0 text-muted-custom">Check your inbox (and spam folder) for the verification code</p>
                                    </div>
                                </div>
                                @endif

                                @if (session()->has('otp-success'))
                                    <div class="alert alert-success d-flex align-items-center gap-3 p-3 rounded" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);">
                                        <x-icon name="lucide-check-circle" class="text-success shrink-0" style="width:20px; height:20px;" />
                                        <div>
                                            <p class="fw-semibold small mb-0 text-success">{{ session('otp-success') }}</p>
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <label class="form-label">Enter 6-Digit Verification Code</label>
                                    <input type="text" wire:model="verificationCode" class="form-control bg-secondary-custom text-center fs-3 py-3 rounded-xl @error('verificationCode') is-invalid @enderror" placeholder="000000" maxlength="6" style="letter-spacing: 0.5rem;" required>
                                    @error('verificationCode') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="text-center">
                                    <button type="button" wire:click="sendOtp" :disabled="!$wire.canResendOtp" class="btn btn-link text-primary-custom p-0 text-decoration-none small">
                                        {{ $canResendOtp ? 'Resend verification code' : 'Resend available in ' . ($lastOtpSentAt ? \Carbon\Carbon::parse($lastOtpSentAt)->addSeconds(60)->diffInSeconds(now()) : 60) . ' seconds' }}
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Step 3: KYC --}}
                        @if($currentStep == 3)
                            <div class="d-flex flex-column gap-4">
                                <div>
                                    <h2 class="h3 fw-bold mb-2">Identity Verification (KYC)</h2>
                                    <p class="text-muted-custom">Upload your ID to verify your identity</p>
                                </div>

                                <div class="d-flex align-items-start gap-3 p-3 rounded" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2);">
                                    <x-icon name="lucide-shield" class="text-primary-custom shrink-0 mt-1" style="width:20px; height:20px;" />
                                    <div>
                                        <p class="fw-semibold small mb-1">Why do we need this?</p>
                                        <p class="small mb-0 text-muted-custom">KYC verification helps us keep your account secure and comply with regulations</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">ID Type</label>
                                    <select wire:model="idType" class="form-select bg-secondary-custom rounded-xl py-3 @error('idType') is-invalid @enderror" required>
                                        <option value="">Select ID type</option>
                                        <option value="passport">Passport</option>
                                        <option value="drivers_license">Driver's License</option>
                                        <option value="national_id">National ID Card</option>
                                    </select>
                                    @error('idType') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="form-label">ID Number</label>
                                    <input type="text" wire:model="idNumber" class="form-control bg-secondary-custom rounded-xl py-3 @error('idNumber') is-invalid @enderror" placeholder="Enter your ID number" required>
                                    @error('idNumber') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="form-label">Upload ID Document</label>
                                    <input type="file" wire:model="idDocument" class="form-control bg-secondary-custom rounded-xl py-3 @error('idDocument') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                                    @error('idDocument') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    <small class="text-muted-custom">Accepted formats: JPG, JPEG, PNG, PDF. Max size: 2MB</small>
                                </div>
                            </div>
                        @endif

                        {{-- Step 4: PIN Setup --}}
                        @if($currentStep == 4)
                            <div class="d-flex flex-column gap-4">
                                <div>
                                    <h2 class="h3 fw-bold mb-2">Create Your PIN</h2>
                                    <p class="text-muted-custom">Set up a 4-digit PIN for transaction security</p>
                                </div>

                                <div>
                                    <label class="form-label">4-Digit PIN</label>
                                    <div class="position-relative">
                                        <x-icon name="lucide-lock" class="position-absolute text-muted-custom" style="left:1rem; top:50%; transform:translateY(-50%); width:20px; height:20px;" />
                                        <input type="password" wire:model="pin" class="form-control bg-secondary-custom rounded-xl py-3 ps-5 @error('pin') is-invalid @enderror" placeholder="please input ur 4 digit stuff" maxlength="4" required>
                                    </div>
                                    @error('pin') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="form-label">Confirm PIN</label>
                                    <div class="position-relative">
                                        <x-icon name="lucide-lock" class="position-absolute text-muted-custom" style="left:1rem; top:50%; transform:translateY(-50%); width:20px; height:20px;" />
                                        <input type="password" wire:model="pin_confirmation" class="form-control bg-secondary-custom rounded-xl py-3 ps-5 @error('pin_confirmation') is-invalid @enderror" placeholder="confirm" maxlength="4" required>
                                    </div>
                                    @error('pin_confirmation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>                        @endif

                        {{-- Step 5: Password Setup --}}
                        @if($currentStep == 5)
                            <div class="d-flex flex-column gap-4">
                                <div>
                                    <h2 class="h3 fw-bold mb-2">Create Your Password</h2>
                                    <p class="text-muted-custom">Set a strong password for your account</p>
                                </div>

                                <div>
                                    <label class="form-label">Password</label>
                                    <div class="position-relative">
                                        <x-icon name="lucide-lock" class="position-absolute text-muted-custom" style="left:1rem; top:50%; transform:translateY(-50%); width:20px; height:20px;" />
                                        <input type="password" wire:model="password" class="form-control bg-secondary-custom rounded-xl py-3 ps-5 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                                    </div>
                                    @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    <small class="text-muted-custom">Must contain uppercase, lowercase, numbers, and symbols</small>
                                </div>

                                <div>
                                    <label class="form-label">Confirm Password</label>
                                    <div class="position-relative">
                                        <x-icon name="lucide-lock" class="position-absolute text-muted-custom" style="left:1rem; top:50%; transform:translateY(-50%); width:20px; height:20px;" />
                                        <input type="password" wire:model="password_confirmation" class="form-control bg-secondary-custom rounded-xl py-3 ps-5 @error('password_confirmation') is-invalid @enderror" placeholder="••••••••" required>
                                    </div>
                                    @error('password_confirmation') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Step 6: Terms and Conditions --}}
                        @if($currentStep == 6)
                            <div class="d-flex flex-column gap-4">
                                <div>
                                    <h2 class="h3 fw-bold mb-2">Terms and Conditions</h2>
                                    <p class="text-muted-custom">Please review and accept our terms</p>
                                </div>

                                <div class="border rounded-xl p-4" style="background: rgba(42, 42, 58, 0.5); max-height: 300px; overflow-y: auto;">
                                    <h5>Terms of Service</h5>
                                    <p class="small text-muted-custom mb-3">
                                        By creating an account, you agree to our terms of service, privacy policy, and understand that all transactions are subject to verification and compliance requirements.
                                    </p>
                                    <h6>Key Points:</h6>
                                    <ul class="small text-muted-custom">
                                        <li>You must be 18+ to use this service</li>
                                        <li>All information provided must be accurate</li>
                                        <li>KYC verification is required for full access</li>
                                        <li>Transactions may be subject to limits and fees</li>
                                    </ul>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" wire:model="termsAccepted" class="form-check-input @error('termsAccepted') is-invalid @enderror" id="termsAccepted">
                                    <label class="form-check-label" for="termsAccepted">
                                        I agree to the <a href="#" class="text-primary-custom">Terms of Service</a> and <a href="#" class="text-primary-custom">Privacy Policy</a>
                                    </label>
                                    @error('termsAccepted') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Navigation Buttons --}}
                        <div class="row mt-4 g-3">
                            @if($currentStep > 1)
                                <div class="col-6">
                                    <button type="button" wire:click="previousStep" class="btn btn-outline-light w-100 py-3 rounded-xl d-flex align-items-center justify-content-center gap-2">
                                        <x-icon name="lucide-arrow-left" style="width:20px; height:20px;" />
                                        Back
                                    </button>
                                </div>
                            @endif

                            @if($currentStep == 2)
                                @if(!$otpVerified)
                                    <div class="col-6">
                                        <button type="button" wire:click="verifyOtp" class="btn-gradient w-100 py-3 rounded-xl d-flex align-items-center justify-content-center gap-2">
                                            <x-icon name="lucide-shield-check" style="width:20px; height:20px;" />
                                            Verify Code
                                        </button>
                                    </div>
                                @else
                                    <div class="col-6">
                                        <button type="submit" class="btn-gradient w-100 py-3 rounded-xl d-flex align-items-center justify-content-center gap-2">
                                            <x-icon name="lucide-arrow-right" style="width:20px; height:20px;" />
                                            Continue
                                        </button>
                                    </div>
                                @endif
                            @else
                                <div class="{{ $currentStep > 1 ? 'col-6' : 'col-12' }}">
                                    <button type="submit" class="btn-gradient w-100 py-3 rounded-xl d-flex align-items-center justify-content-center gap-2">
                                        {{ $currentStep === 6 ? 'Create Account' : 'Continue' }}
                                        @if($currentStep < 6)
                                            <x-icon name="lucide-arrow-right" style="width:20px; height:20px;" />
                                        @endif
                                    </button>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center mt-4">
                <p class="text-muted-custom small mb-0">
                    Already have an account?
                    <a href="/login" class="text-primary-custom text-decoration-none">Sign in</a>
                </p>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('livewire:loaded', () => {
        Livewire.on('enable-resend', () => {
            // This will be triggered after 60 seconds to re-enable the resend button
        });
    });
    </script>
</div>
