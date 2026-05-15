{{--
    Transfer Confirmation Step
    Displays transfer details to bank account for final review
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
            
            {{-- Bank Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted-custom">Bank</span>
                <div class="text-end">
                    <div class="fw-medium">{{ $resolvedAccountName ?? 'N/A' }}</div>
                </div>
            </div>
            
            {{-- Divider --}}
            <hr style="border-color: #2a2a3a; margin: 0;">

            {{-- Account Number Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted-custom">Account Number</span>
                <span class="fw-medium font-monospace">{{ $accountNumber }}</span>
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

            {{-- Fee Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted-custom">Processing Fee</span>
                <span class="fw-medium">₦0.00</span>
            </div>

            {{-- Divider --}}
            <hr style="border-color: #2a2a3a; margin: 0;">

            {{-- Total Amount Section --}}
            <div class="d-flex align-items-center justify-content-between">
                <span class="fw-medium">Total Amount</span>
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
                wire:click="setStep('amount')"
            >
                <x-lucide-arrow-left style="width: 18px; height: 18px; display: inline; margin-right: 8px;" />
                Back
            </button>
        </div>
        <div class="col-6">
            <button 
                type="button"
                class="btn btn-gradient w-100 py-3"
                wire:click="showPinVerification"
                :disabled="isProcessing"
            >
                <span wire:loading.remove>Confirm & Send</span>
                <span wire:loading>
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Processing...
                </span>
            </button>
        </div>
    </div>

    {{-- PIN Modal --}}
    @if ($showPinModal)
        <div class="modal d-block" style="background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center;">
            <div class="modal-dialog">
                <div class="modal-content bg-primary" style="background-color: #0a0a0f !important; border: 1px solid #2a2a3a;">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Enter Your PIN</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closePinModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">4-Digit PIN</label>
                            <input 
                                type="password" 
                                class="form-control bg-secondary-custom border-0 rounded-xl text-center fw-bold"
                                placeholder="••••"
                                wire:model="pinInput"
                                maxlength="4"
                                pattern="[0-9]*"
                                inputmode="numeric"
                                autofocus
                            />
                        </div>
                        @if ($errorMessage)
                            <div class="alert alert-danger alert-sm">{{ $errorMessage }}</div>
                        @endif
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-light" wire:click="closePinModal">Cancel</button>
                        <button type="button" class="btn btn-gradient" wire:click="verifyPinAndTransfer" :disabled="isProcessing">
                            <span wire:loading.remove>Send</span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Sending...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
