{{--
    Success Confirmation Step
    Displays success message and transfer summary
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
            background: rgba(168, 85, 247, 0.1);
        "
    >
        <i class="fas fa-check-circle text-primary-custom" style="font-size: 40px;"></i>
    </div>

    {{-- Success Message --}}
    <div>
        <h2 class="display-6 fw-bold mb-2">Transfer Successful!</h2>
        <p class="text-muted-custom">Your money has been sent</p>
    </div>

    {{-- Transfer Summary Card --}}
    <x-ui.card variant="default" class="bg-secondary-custom border-0">
        <div class="card-body">
            {{-- Amount Display --}}
            <div class="display-5 fw-bold text-primary-custom mb-2">
                ₦{{ number_format($amount, 2) }}
            </div>
            
            {{-- Recipient Name --}}
            <div class="text-muted-custom">
                sent to <span class="fw-semibold">{{ $selectedRecipient['name'] ?? 'N/A' }}</span>
            </div>
        </div>
    </x-ui.card>

    {{-- Completion Button --}}
    <button 
        type="button"
        class="btn btn-gradient w-100 py-3"
        wire:click="handleComplete"
    >
        <i class="fas fa-list me-2"></i>
        View Transactions
    </button>

    {{-- Additional Info --}}
    <div class="alert alert-info small">
        <i class="fas fa-info-circle me-2"></i>
        A confirmation email has been sent to {{ $selectedRecipient['email'] ?? 'your email' }}
    </div>
</div>
