{{--
    Transfer Confirmation Step
    Displays all transfer details for final review before sending
    UI Components: x-ui.card, x-ui.button
    CSS Classes: text-primary-custom, text-muted-custom, fw-bold
--}}

<div class="d-flex flex-column gap-4">
    <div>
        <h2 class="h3 fw-bold mb-2">Confirm Transfer</h2>
        <p class="text-muted-custom">Please review the details before sending</p>
    </div>

    {{-- Summary Card with All Transfer Details --}}
    <x-ui.card variant="default" class="bg-secondary-custom border-0">
        <div class="card-body d-flex flex-column gap-3">
            
            {{-- Recipient Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted-custom">Recipient</span>
                <div class="text-end">
                    <div class="fw-medium">{{ $selectedRecipient['name'] ?? 'N/A' }}</div>
                    <div class="small text-muted-custom">{{ $selectedRecipient['email'] ?? '' }}</div>
                </div>
            </div>
            
            {{-- Divider --}}
            <hr style="border-color: #2a2a3a; margin: 0;">

            {{-- Amount Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted-custom">Amount</span>
                <span class="h3 fw-bold text-primary-custom mb-0">₦{{ number_format($amount, 2) }}</span>
            </div>

            {{-- Note Section (Conditional) --}}
            @if ($note)
                <hr style="border-color: #2a2a3a; margin: 0;">
                <div>
                    <div class="text-muted-custom mb-1">Note</div>
                    <div class="small">{{ $note }}</div>
                </div>
            @endif

            {{-- Divider --}}
            <hr style="border-color: #2a2a3a; margin: 0;">

            {{-- Transfer Method Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted-custom">Method</span>
                <span class="text-capitalize fw-medium">
                    @if($method === 'wallet')
                        CarePay Wallet
                    @elseif($method === 'bank')
                        Bank Transfer
                    @elseif($method === 'card')
                        Debit Card
                    @else
                        {{ ucfirst($method) }}
                    @endif
                </span>
            </div>

            {{-- Divider --}}
            <hr style="border-color: #2a2a3a; margin: 0;">

            {{-- Fee Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted-custom">Fee</span>
                <span class="fw-medium">₦0.00</span>
            </div>

            {{-- Divider --}}
            <hr style="border-color: #2a2a3a; margin: 0;">

            {{-- Total Amount Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="fw-medium">Total</span>
                <span class="h3 fw-bold text-primary-custom mb-0">₦{{ number_format($amount, 2) }}</span>
            </div>
        </div>
    </x-ui.card>

    {{-- Action Buttons --}}
    <div class="row g-3">
        <div class="col-6">
            <button 
                type="button"
                class="btn btn-outline-light w-100 py-3 rounded-xl"
                style="border-color: #2a2a3a;"
                wire:click="setStep('method')"
            >
                <i class="fas fa-arrow-left me-2"></i>
                Back
            </button>
        </div>
        <div class="col-6">
            <button 
                type="button"
                class="btn btn-gradient w-100 py-3"
                wire:click="handleConfirm"
            >
                Confirm & Send
            </button>
        </div>
    </div>
</div>
