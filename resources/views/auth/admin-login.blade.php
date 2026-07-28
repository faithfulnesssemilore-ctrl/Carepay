<x-layouts.guest>
    <div class="d-flex align-items-center justify-content-center min-vh-100 p-4 bg-slate-950">
        <div class="mx-auto" style="max-width: 540px; width: 100%;">
            <x-ui.card variant="luxury" class="border-0 shadow-primary">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="icon-container icon-container-lg mx-auto mb-3 gradient-bg-primary text-white shadow-primary">
                            <x-lucide-shield-check style="width:24px;height:24px;color:white;" />
                        </div>
                        <h2 class="h3 fw-bold mb-2">Admin panel access</h2>
                        <p class="text-muted-custom mb-0">Sign in with your CarePay administrator account.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.authenticate') }}" class="d-grid gap-3">
                        @csrf
                        <div>
                            <label for="email" class="form-label text-white">Email address</label>
                            <input id="email" name="email" type="email" class="form-control" required autofocus value="{{ old('email') }}">
                        </div>
                        <div>
                            <label for="password" class="form-label text-white">Password</label>
                            <input id="password" name="password" type="password" class="form-control" required>
                        </div>
                        <div class="form-check">
                            <input id="remember" name="remember" type="checkbox" class="form-check-input">
                            <label for="remember" class="form-check-label text-white">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-gradient w-100 py-3">Sign in to admin panel</button>
                    </form>

                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('login') }}" class="btn btn-outline-gradient w-100 py-2">Go to user login</a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary w-100 py-2">Return to home</a>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.guest>
