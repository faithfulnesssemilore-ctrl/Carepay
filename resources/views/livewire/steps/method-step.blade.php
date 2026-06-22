
<div class="d-flex flex-column gap-4">
    <div>
        <h2 class="h3 fw-bold mb-2">Choose Transfer Method</h2>
        <p class="text-muted-custom">Select how you want to send the money</p>
    </div>

    {{-- Transfer Method Options --}}
    <div class="d-flex flex-column gap-3">
        
        {{-- Wallet Option --}}
        <x-ui.card 
            variant="default"
            hover="lift"
            class="bg-secondary-custom border"
            wire:click="setMethod('wallet')"
            style="
                cursor: pointer; 
                border-color: #2a2a3a; 
                border-radius: 12px; 
                transition: all 0.3s ease;
            "
        >
            <div class="card-body d-flex align-items-center gap-3">
                {{-- Icon Container --}}
                <div 
                    class="d-flex align-items-center justify-content-center"
                    style="
                        background: rgba(168, 85, 247, 0.1); 
                        padding: 12px; 
                        border-radius: 8px;
                        flex-shrink: 0;
                    "
                >
                    <i class="fas fa-wallet text-primary-custom" style="font-size: 24px;"></i>
                </div>
                
                {{-- Method Details --}}
                <div class="flex-fill">
                    <div class="fw-medium mb-1">CarePay Wallet</div>
                    <div class="small text-muted-custom">Instant transfer • No fees</div>
                </div>
                
                {{-- Arrow Icon --}}
                <i class="fas fa-arrow-right text-muted-custom" style="flex-shrink: 0;"></i>
            </div>
        </x-ui.card>

        {{-- Bank Transfer Option --}}
        <x-ui.card 
            variant="default"
            hover="lift"
            class="bg-secondary-custom border"
            wire:click="setMethod('bank')"
            style="
                cursor: pointer; 
                border-color: #2a2a3a; 
                border-radius: 12px; 
                transition: all 0.3s ease;
            "
        >
            <div class="card-body d-flex align-items-center gap-3">
                {{-- Icon Container --}}
                <div 
                    class="d-flex align-items-center justify-content-center"
                    style="
                        background: rgba(192, 132, 252, 0.1); 
                        padding: 12px; 
                        border-radius: 8px;
                        flex-shrink: 0;
                    "
                >
                    <i class="fas fa-building text-accent-custom" style="font-size: 24px;"></i>
                </div>
                
                {{-- Method Details --}}
                <div class="flex-fill">
                    <div class="fw-medium mb-1">Bank Transfer</div>
                    <div class="small text-muted-custom">1-2 business days • ₦1.50 fee</div>
                </div>
                
                {{-- Arrow Icon --}}
                <i class="fas fa-arrow-right text-muted-custom" style="flex-shrink: 0;"></i>
            </div>
        </x-ui.card>

        {{-- Credit Card Option --}}
        <x-ui.card 
            variant="default"
            hover="lift"
            class="bg-secondary-custom border"
            wire:click="setMethod('card')"
            style="
                cursor: pointer; 
                border-color: #2a2a3a; 
                border-radius: 12px; 
                transition: all 0.3s ease;
            "
        >
            <div class="card-body d-flex align-items-center gap-3">
                {{-- Icon Container --}}
                <div 
                    class="d-flex align-items-center justify-content-center"
                    style="
                        background: rgba(168, 85, 247, 0.1); 
                        padding: 12px; 
                        border-radius: 8px;
                        flex-shrink: 0;
                    "
                >
                    <i class="fas fa-credit-card text-primary-custom" style="font-size: 24px;"></i>
                </div>
                
                {{-- Method Details --}}
                <div class="flex-fill">
                    <div class="fw-medium mb-1">Debit Card</div>
                    <div class="small text-muted-custom">Instant transfer • 1.5% fee</div>
                </div>
                
                {{-- Arrow Icon --}}
                <i class="fas fa-arrow-right text-muted-custom" style="flex-shrink: 0;"></i>
            </div>
        </x-ui.card>
    </div>

    {{-- Back Button --}}
    <button 
        type="button"
        class="btn btn-outline-light w-100 py-3 rounded-xl"
        style="border-color: #2a2a3a;"
        wire:click="setStep('amount')"
    >
        <i class="fas fa-arrow-left me-2"></i>
        Back
    </button>
</div>
