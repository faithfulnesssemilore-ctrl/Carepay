<div class="min-vh-100 bg-slate-50 py-8">
    <div class="mx-auto" style="max-width: 520px; width: 100%; padding: 0 1rem;">
        @if ($errorMessage)
            <x-ui.alert variant="danger" dismissible class="mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errorMessage }}</span>
                </div>
            </x-ui.alert>
        @endif

        <x-ui.card variant="luxury" class="border-0 shadow-primary">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-container icon-container-lg mx-auto mb-3 gradient-bg-primary text-white shadow-primary">
                        <x-lucide-lock style="width:24px;height:24px;color:white;" />
                    </div>
                    <h1 class="h3 fw-semibold mb-2">Welcome back</h1>
                    <p class="text-muted-custom mb-0">Securely access your wallet, view transactions, and stay updated.</p>
                </div>

                <form wire:submit.prevent="login" class="space-y-4">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <div class="input-with-icon">
                            <span class="icon-prefix">
                                <x-lucide-mail style="width:18px;height:18px;" />
                            </span>
                            <x-ui.input
                                type="email"
                                wire:model="email"
                                placeholder="Enter your email"
                                required
                            />
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-with-icon">
                            <span class="icon-prefix">
                                <x-lucide-lock style="width:18px;height:18px;" />
                            </span>
                            <x-ui.input
                                type="password"
                                id="passwordInput"
                                wire:model="password"
                                placeholder="Enter password"
                                required
                            />
                            <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-column gap-3 gap-md-0 flex-md-row justify-content-between align-items-center mb-3">
                        <label class="d-flex align-items-center gap-2 mb-0">
                            <input type="checkbox" wire:model="remember">
                            Remember me
                        </label>
                        <a href="/forgot-password" class="text-primary-custom text-decoration-none small">Forgot password?</a>
                    </div>

                    <x-ui.button type="submit" class="w-100 py-3 rounded-3">
                        @if ($isLoading)
                            Signing in...
                        @else
                            Sign In
                        @endif
                    </x-ui.button>
                </form>

                <div class="text-center my-4 text-muted-custom">Or</div>

                <a href="/register" class="btn btn-outline-gradient w-100 py-3 fw-semibold">
                    Create Account
                </a>
            </div>
        </x-ui.card>

        <div class="row gx-2 mt-4 text-center text-md-start">
            <div class="col-6 mb-2 mb-md-0">
                <a href="/privacy-policy" class="text-primary-custom text-decoration-none small">Privacy policy</a>
            </div>
            <div class="col-6 text-md-end">
                <span class="text-muted-custom small">Secured by bank-level encryption</span>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('toggleIcon');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
