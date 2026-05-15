{{--
    Success Confirmation Step - Bank Transfer Complete
    Displays success message and transfer summary to external bank
    UI Components: x-ui.card, x-ui.button
    CSS Classes: text-primary-custom, text-center, gradient-bg-primary
--}}

<div class="text-center d-flex flex-column gap-4 py-4">
    
    {{-- Success Icon Container --}}
    <div 
        class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
        style="
            width: 80px; 
            height: 80px; 
            background: rgba(34, 197, 94, 0.1);
        "
    >
        <x-lucide-check-circle class="text-success" style="width: 40px; height: 40px;" />
    </div>

    {{-- Success Message --}}
    <div>
        <h2 class="display-6 fw-bold mb-2">Transfer Initiated!</h2>
        <p class="text-muted-custom">Your money is on the way</p>
    </div>

    {{-- Transfer Summary Card --}}
    <x-ui.card variant="default" class="bg-secondary-custom border-0">
        <div class="card-body">
            {{-- Amount Display --}}
            <div class="display-5 fw-bold text-primary-custom mb-3">
                ₦{{ number_format($amount, 2) }}
            </div>
            
            {{-- Bank Account Details --}}
            <div class="text-muted-custom small mb-2">
                Sent to
            </div>
            <div class="fw-semibold mb-3">{{ $resolvedAccountName }}</div>
            <div class="text-muted-custom small font-monospace">{{ $accountNumber }}</div>
        </div>
    </x-ui.card>

    {{-- Info Box --}}
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <x-lucide-info class="me-2" style="width: 18px; height: 18px; display: inline;" />
        Transfer usually completes within 1-2 hours
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- Completion Button --}}
    <button 
        type="button"
        class="btn btn-gradient w-100 py-3"
        wire:click="handleComplete"
    >
        <x-lucide-list style="width: 18px; height: 18px; display: inline; margin-right: 8px;" />
        View Transactions
    </button>
</div>
