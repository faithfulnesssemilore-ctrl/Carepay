
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
        
        {{-- Balance & Limits Info --}}
        <div class="mt-3 small text-muted-custom">
            <div class="d-flex justify-content-between mb-2">
                <span>Available balance:</span>
                <span class="text-primary-custom fw-semibold">₦{{ number_format($walletBalance, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Single transfer limit:</span>
                <span class="fw-semibold">₦{{ number_format($singleLimit, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Daily limit remaining:</span>
                <span class="fw-semibold">₦{{ number_format($dailyLimit - $dailyUsed, 2) }} / ₦{{ number_format($dailyLimit, 2) }}</span>
            </div>
        </div>
    </div>

   

    {{-- Note/Message Input --}}
    <div class="form-group">
        <label class="form-label fw-medium">Description (Optional)</label>
        <x-ui.textarea 
            name="note"
            rows="3"
            placeholder="Add a message..."
            class="bg-secondary-custom border-0 rounded-xl"
            wire:model="note"
        />
    </div>

    @php
        $remaining = max(0, $dailyLimit - $dailyUsed);
        $entered = floatval($amount ?: 0);
    @endphp

    @if($entered > $remaining)
        <div class="alert alert-warning small mt-3">
            This amount would exceed your daily limit. You can send ₦{{ number_format($remaining, 2) }} more today.
        </div>
    @endif

    @if($entered > $singleLimit)
        <div class="alert alert-warning small mt-2">
            Amount exceeds your single transaction limit of ₦{{ number_format($singleLimit, 2) }}.
        </div>
    @endif

    @if($entered > $walletBalance)
        <div class="alert alert-danger small mt-2">
            Insufficient balance. Your balance is ₦{{ number_format($walletBalance, 2) }}.
        </div>
    @endif

    {{-- Navigation Buttons --}}
    <div class="row g-3">
        <div class="col-6">
            <button 
                type="button"
                class="btn btn-outline-light w-100 py-3 rounded-xl"
                style="border-color: #2a2a3a;"
                wire:click="setStep('recipient')"
            >
                <x-lucide-arrow-left style="width: 18px; height: 18px; display: inline; margin-right: 8px;" />
                Back
            </button>
        </div>
        <div class="col-6">
            <button 
                type="button"
                class="btn btn-gradient w-100 py-3 d-flex align-items-center justify-content-center gap-2"
                wire:click="handleAmountSubmit"
                @disabled($entered <= 0 || $entered > $remaining || $entered > $singleLimit || $entered > $walletBalance)
            >
                Continue
                <x-lucide-arrow-right style="width: 18px; height: 18px;" />
            </button>
        </div>
    </div>
</div>
