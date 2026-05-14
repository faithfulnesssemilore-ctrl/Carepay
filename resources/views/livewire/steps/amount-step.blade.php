{{--
    Amount Input Step
    Allows user to enter the transfer amount with preset buttons
    UI Components: x-ui.input, x-ui.textarea, x-ui.button
    CSS Classes: btn-gradient, text-primary-custom
--}}

<div class="d-flex flex-column gap-4">
    <div>
        <h2 class="h3 fw-bold mb-2">Enter Amount</h2>
        <p class="text-muted-custom">How much do you want to send?</p>
    </div>

    {{-- Amount Input Field --}}
    <div class="form-group">
        <label class="form-label fw-medium">Amount</label>
        <div class="position-relative">
            {{-- Currency Symbol --}}
            <span 
                class="position-absolute text-muted-custom display-6 fw-bold" 
                style="
                    left: 1rem; 
                    top: 50%; 
                    transform: translateY(-50%);
                "
            >
                ₦
            </span>
            
            {{-- Amount Input --}}
            <x-ui.input 
                type="number"
                name="amount"
                placeholder="0.00"
                class="bg-secondary-custom border-0 rounded-xl display-6 fw-bold py-3"
                style="padding-left: 3rem;"
                step="0.01"
                min="0"
                wire:model="amount"
            />
        </div>
        
        {{-- Available Balance Info --}}
        <div class="mt-2 small text-muted-custom">
            Available balance: 
            <span class="text-primary-custom fw-semibold">₦{{ number_format($walletBalance, 2) }}</span>
        </div>
    </div>

    {{-- Preset Amount Buttons --}}
    <div>
        <label class="form-label fw-medium d-block mb-2">Quick Amount</label>
        <div class="row g-2">
            @foreach([50, 100, 250, 500] as $preset)
                <div class="col-3">
                    <x-ui.button 
                        variant="outline-light"
                        class="w-100 rounded-xl"
                        style="border-color: #2a2a3a;"
                        wire:click="$set('amount', '{{ $preset }}')"
                    >
                        ₦{{ $preset }}
                    </x-ui.button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Note/Message Input --}}
    <div class="form-group">
        <label class="form-label fw-medium">Note (Optional)</label>
        <x-ui.textarea 
            name="note"
            rows="3"
            placeholder="Add a message..."
            class="bg-secondary-custom border-0 rounded-xl"
            wire:model="note"
        />
    </div>

    {{-- Navigation Buttons --}}
    <div class="row g-3">
        <div class="col-6">
            <button 
                type="button"
                class="btn btn-outline-light w-100 py-3 rounded-xl"
                style="border-color: #2a2a3a;"
                wire:click="setStep('recipient')"
            >
                <i class="fas fa-arrow-left me-2"></i>
                Back
            </button>
        </div>
        <div class="col-6">
            <button 
                type="button"
                class="btn btn-gradient w-100 py-3 d-flex align-items-center justify-content-center gap-2"
                wire:click="handleAmountSubmit"
            >
                Continue
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </div>
</div>
