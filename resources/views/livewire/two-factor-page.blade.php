<div class="d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh; padding: 2rem;">
    <div class="container">
        <div class="mx-auto" style="max-width: 450px;">
            {{-- Header --}}
            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none mb-4">
                    <div class="icon-container gradient-bg-primary" style="width: 40px; height: 40px;">
                        <x-lucide-wallet class="text-white w-5 h-5" style="margin: auto;" />
                    </div>
                    <span class="gradient-text fs-3 fw-bold">CarePay</span>
                </a>
                <div class="icon-container icon-container-lg mx-auto mb-3" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                    <x-lucide-shield class="w-8 h-8 text-primary-custom" />
                </div>
                <h1 class="display-6 fw-bold mb-2">Two-Factor Authentication</h1>
                <p class="text-muted-custom">Enter the code from your authenticator app</p>
            </div>

            {{-- Error Message --}}
            @if($errorMessage)
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ $errorMessage }}
                    <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
                </div>
            @endif

            {{-- 2FA Form --}}
            <div class="card card-luxury p-4 shadow-primary">
                <div class="card-body">
                    @if($step === 'verify')
                        {{-- Verification Step --}}
                        <div class="alert" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2); color: #a855f7;" role="alert">
                            <div class="d-flex gap-3">
                                <x-lucide-smartphone class="w-5 h-5 flex-shrink-0 mt-1" />
                                <div>
                                    <div class="fw-semibold small">Check your authenticator app</div>
                                    <div class="small" style="opacity: 0.8;">Enter the 6-digit code from your app to continue</div>
                                </div>
                            </div>
                        </div>

                        <form wire:submit="verifyCode">
                            <div class="mb-4">
                                <label class="form-label">Authentication Code</label>
                                <input 
                                    type="text"
                                    wire:model="code"
                                    class="form-control bg-secondary-custom text-center fs-3 py-3 rounded-xl"
                                    style="letter-spacing: 0.5rem;"
                                    placeholder="000000"
                                    maxlength="6"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    required
                                />
                                @error('code')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-gradient w-100 py-3" {{ $isProcessing ? 'disabled' : '' }}>
                                {{ $isProcessing ? 'Verifying...' : 'Verify & Continue' }}
                            </button>
                        </form>

                        <div class="mt-4 d-flex flex-column gap-3">
                            <div class="text-center">
                                <button type="button" wire:click="useBackupCode" class="btn btn-link text-primary-custom p-0 text-decoration-none small">
                                    Use backup code instead
                                </button>
                            </div>
                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-muted-custom text-decoration-none small">
                                    Back to login
                                </a>
                            </div>
                        </div>

                    @elseif($step === 'backup')
                        {{-- Backup Codes Step --}}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Backup Codes</h5>
                            <p class="small text-muted-custom mb-3">Use one of these codes if you can't access your authenticator app:</p>
                            
                            <div style="background: rgba(255, 255, 255, 0.05); padding: 1rem; border-radius: 8px; max-height: 250px; overflow-y: auto;">
                                @foreach($backupCodes as $code)
                                    <div class="text-monospace small mb-2 d-flex justify-content-between align-items-center">
                                        <span>{{ $code }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-light" onclick="navigator.clipboard.writeText('{{ $code }}')">
                                            <x-lucide-copy class="w-3 h-3" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" wire:click="goBackToVerify" class="btn btn-outline-light flex-grow-1">Back</button>
                            <button type="button" wire:click="completeVerification" class="btn btn-gradient flex-grow-1">Continue</button>
                        </div>

                    @elseif($step === 'success')
                        {{-- Success Step --}}
                        <div class="text-center py-4">
                            <div class="mb-4">
                                <div class="icon-container icon-container-lg mx-auto" style="background: rgba(34, 197, 94, 0.1); width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                                    <x-lucide-check-circle-2 class="w-10 h-10 text-success" />
                                </div>
                            </div>
                            <h3 class="h4 fw-bold mb-2">Verification Successful</h3>
                            <p class="text-muted-custom mb-4">Your two-factor authentication has been verified</p>
                            <button type="button" wire:click="completeVerification" class="btn btn-gradient w-100">
                                Continue to Dashboard
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Help Text --}}
            <div class="text-center mt-4">
                <p class="text-muted-custom small mb-0">Having trouble? Contact support for assistance</p>
            </div>
        </div>
    </div>
</div>
