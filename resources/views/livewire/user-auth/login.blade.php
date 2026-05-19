<div class="d-flex align-items-center justify-content-center min-vh-100 p-4">
    <div class="mx-auto" style="max-width: 450px; width: 100%;">

        <div class="text-center mb-5">
            <div class="mb-3">
                <div class="icon-container p-3 rounded shadow-primary mx-auto" style="width: fit-content;">
                    <i class="fas fa-wallet text-white" style="font-size: 28px;"></i>
                </div>
            </div>
            <h1 class="display-6 fw-bold mb-2 gradient-text">CarePay</h1>
            <p class="text-muted-custom fs-6">Welcome back, sign in to continue</p>
        </div>

        @if ($errorMessage)
            <x-ui.alert variant="danger" dismissible class="mb-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-circle-exclamation"></i>
                    <span>{{ $errorMessage }}</span>
                </div>
            </x-ui.alert>
        @endif

        <x-ui.card variant="luxury" class="border-0 shadow-primary">
            <div class="card-body p-4">

                <form wire:submit.prevent="login">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">
                            Email Address
                        </label>

                        <x-ui.input
                            type="email"
                            wire:model="email"
                            placeholder="Enter your email"
                            required
                        />

                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="mb-3">
                        <label class="form-label">
                            Password
                        </label>

                        <div class="input-group">

                            <x-ui.input
                                type="password"
                                id="passwordInput"
                                wire:model="password"
                                placeholder="Enter password"
                                required
                            />

                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>

                        </div>

                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="mb-3">
                        <label class="d-flex align-items-center gap-2">
                            <input type="checkbox" wire:model="remember">
                            Remember me
                        </label>
                    </div>


                    <x-ui.button
                        type="submit"
                        class="w-100"
                    >
                        @if ($isLoading)
                            Signing in...
                        @else
                            Sign In
                        @endif
                    </x-ui.button>

                </form>

                <hr>

                <a href="/register" class="btn btn-outline-light w-100">
                    Create Account
                </a>

            </div>
        </x-ui.card>

        <div class="row gx-2 mt-4">
            <div class="col-6">
                <a href="/forgot-password" class="text-primary-custom text-decoration-none small">Forgot password?</a>
            </div>
            <div class="col-6 text-end">
                    <span class="text-muted-custom small text-muted-custom">Secured by bank-level encryption</span>
            </div>
        </div>

    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('toggleIcon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>