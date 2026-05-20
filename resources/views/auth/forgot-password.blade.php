<x-layouts.guest>
    <div class="d-flex align-items-center justify-content-center min-vh-100 p-4">
        <div class="mx-auto" style="max-width: 450px; width: 100%;">
            <x-ui.card variant="luxury" class="border-0 shadow-primary">
                <div class="card-body p-4">
                    <div class="mb-4 text-center">
                        <h2 class="h4 fw-bold">Forgot your password?</h2>
                        <p class="text-muted-custom mb-0">Enter your email address and we’ll send you a link to reset it.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control bg-secondary-custom border-0 rounded-xl py-3"
                                required
                                autofocus
                            />
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-gradient w-100 py-3">Email Password Reset Link</button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-primary-custom text-decoration-none">Back to login</a>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.guest>
