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
                        <p class="text-muted-custom mb-0">Use your admin credentials to manage wallets, transactions, and users.</p>
                    </div>

                    <div class="d-grid gap-3">
                        <a href="{{ route('login') }}" class="btn btn-gradient w-100 py-3">Go to Admin login</a>
                        <a href="{{ route('home') }}" class="btn btn-outline-gradient w-100 py-3">Return to home</a>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.guest>
