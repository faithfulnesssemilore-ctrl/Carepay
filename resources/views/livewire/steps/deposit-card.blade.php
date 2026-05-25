{{--
    Debit Card Deposit Step
    Allows user to enter amount and select payment card for instant deposit
--}}

<x-ui.card variant="default" hover="lift" class="border">
    <div class="card-body p-4 p-md-5">
        {{-- Header --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <div
                class="rounded-circle d-flex align-items-center justify-content-center"
                style="
                    width: 48px;
                    height: 48px;
                    background: rgba(168, 85, 247, 0.1);
                "
            >
                <x-lucide-credit-card class="text-primary-custom" style="width: 24px; height: 24px;" />
            </div>
            <div>
                <h2 class="h3 fw-bold mb-1">Debit Card Top-up</h2>
                <p class="small text-muted-custom mb-0">Instant deposit using your card</p>
            </div>
        </div>

        <form>
            {{-- Amount Input --}}
            <div class="form-group mb-3">
                <label class="form-label">Amount</label>
                <div class="position-relative">
                    <span 
                        class="position-absolute text-muted-custom h3 fw-bold" 
                        style="left: 1rem; top: 50%; transform: translateY(-50%);"
                    >
                        ₦
                    </span>
                    <input
                        type="number"
                        class="form-control bg-secondary-custom border-0 rounded-xl h3 fw-bold py-4"
                        wire:model="cardAmount"
                        placeholder="0.00"
                        step="0.01"
                        min="0"
                        style="padding-left: 3rem;"
                    />
                </div>
            </div>

            {{-- Preset Amount Buttons --}}
            <div class="row g-2 mb-4">
                @foreach([50, 100, 200, 500] as $amount)
                    <div class="col-3">
                        <button
                            type="button"
                            class="btn btn-outline-light w-100 rounded-xl"
                            wire:click="$set('cardAmount', '{{ $amount }}')"
                            style="border-color: #2a2a3a;"
                        >
                            ₦{{ $amount }}
                        </button>
                    </div>
                @endforeach
            </div>

            {{-- Payment Method Selection --}}
            <div class="form-group mb-3">
                <label class="form-label">Payment Method</label>
                <select 
                    class="form-select bg-secondary-custom border-0 rounded-xl py-3"
                    wire:model="selectedCard"
                >
                    <option value="">Select a card</option>
                    <option value="visa">Visa ending in •••• 4532</option>
                    <option value="mastercard">Mastercard ending in •••• 8721</option>
                    <option value="new">Add new card</option>
                </select>
            </div>

            {{-- Fee Breakdown --}}
            <x-ui.card 
                variant="default"
                class="mb-4 border-0"
                style="
                    background: rgba(168, 85, 247, 0.05);
                    border: 1px solid rgba(168, 85, 247, 0.2) !important;
                "
            >
                <div class="card-body py-3">
                    {{-- Amount Row --}}
                    <div class="d-flex justify-content-between align-items-center small mb-2">
                        <span class="text-muted-custom">Amount</span>
                        <span class="fw-semibold">₦{{ number_format($cardAmount ?? 0, 2) }}</span>
                    </div>
                    
                    <hr style="border-color: rgba(168, 85, 247, 0.2); margin: 0.75rem 0" />
                    
                    {{-- Fee Row --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted-custom">Fee (2.5%)</span>
                        <span class="fw-semibold text-primary-custom">₦{{ number_format(($cardAmount ?? 0) * 0.025, 2) }}</span>
                    </div>
                    
                    <hr style="border-color: rgba(168, 85, 247, 0.2); margin: 0.75rem 0" />
                    
                    {{-- Total Row --}}
                    <div class="d-flex justify-content-between align-items-center fw-bold">
                        <span>Total</span>
                        <span class="text-primary-custom">₦{{ number_format(($cardAmount ?? 0) * 1.025, 2) }}</span>
                    </div>
                </div>
            </x-ui.card>

            {{-- Action Button --}}
            <button
                type="button"
                class="btn btn-gradient w-100 py-3"
                wire:click="handleConfirmTransfer"
                @if (empty($cardAmount) || floatval($cardAmount) <= 0 || empty($selectedCard))
                    disabled
                @endif
            >
                Pay Now
            </button>
        </form>
    </div>
</x-ui.card>
