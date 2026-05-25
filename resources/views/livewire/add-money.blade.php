{{--
    Add Money Component - Multi-method deposit flow
    Converted from React Deposit.tsx component
    Steps: select -> details -> success
    
    Deposit Methods:
    - Bank Transfer (Virtual account details)
    - Cash Deposit (Reference ID)
    - Debit Card (Card payment)
    - USSD Code (Mobile banking)
--}}

<div class="container" style="max-width: 900px;">
    <div class="d-flex flex-column gap-4">
        
        {{-- Header with Back Button --}}
        <div class="d-flex align-items-center gap-3">
            @if ($step !== 'select')
                <button 
                    type="button"
                    class="btn btn-link p-0 text-muted-custom"
                    wire:click="handleBack"
                    style="text-decoration: none;"
                >
                    <x-lucide-arrow-left class="w-6 h-6" />
                </button>
            @endif
            <div>
                <h1 class="display-5 fw-bold mb-2">Add Money</h1>
                <p class="text-muted-custom">Choose how you want to deposit funds</p>
            </div>
        </div>

        {{-- Error/Success Messages --}}
        @if ($errorMessage)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <x-lucide-alert-circle class="w-5 h-5 me-2" />
                {{ $errorMessage }}
                <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
            </div>
        @endif

        @if ($successMessage)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <x-lucide-check-circle class="w-5 h-5 me-2" />
                {{ $successMessage }}
                <button type="button" class="btn-close" wire:click="resetForm"></button>
            </div>
        @endif

        {{-- STEP 1: Select Deposit Method --}}
        @if ($step === 'select')
            <div class="row g-3">
                {{-- Bank Transfer Method --}}
                <div class="col-sm-6">
                    <x-ui.card 
                        variant="default"
                        hover="lift"
                        class="bg-secondary-custom border h-100"
                        wire:click="handleMethodSelect('bank-transfer')"
                        style="
                            cursor: pointer;
                            border-color: #2a2a3a;
                            border-radius: 12px;
                            transition: all 0.3s ease;
                        "
                    >
                        <div class="card-body text-center p-4">
                            {{-- Icon --}}
                            <div 
                                class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="
                                    width: 64px;
                                    height: 64px;
                                    background: rgba(168, 85, 247, 0.1);
                                "
                            >
                                <x-lucide-building-2 class="text-primary-custom" style="width: 32px; height: 32px;" />
                            </div>
                            {{-- Title --}}
                            <h3 class="h5 fw-semibold mb-2">Bank Transfer</h3>
                            {{-- Description --}}
                            <p class="small text-muted-custom mb-0">
                                Transfer from your bank account using virtual account details
                            </p>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Cash Deposit Method --}}
                <div class="col-sm-6">
                    <x-ui.card 
                        variant="default"
                        hover="lift"
                        class="bg-secondary-custom border h-100"
                        wire:click="handleMethodSelect('cash')"
                        style="
                            cursor: pointer;
                            border-color: #2a2a3a;
                            border-radius: 12px;
                            transition: all 0.3s ease;
                        "
                    >
                        <div class="card-body text-center p-4">
                            {{-- Icon --}}
                            <div 
                                class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="
                                    width: 64px;
                                    height: 64px;
                                    background: rgba(192, 132, 252, 0.1);
                                "
                            >
                                <x-lucide-wallet class="text-accent-custom" style="width: 32px; height: 32px;" />
                            </div>
                            {{-- Title --}}
                            <h3 class="h5 fw-semibold mb-2">Cash Deposit</h3>
                            {{-- Description --}}
                            <p class="small text-muted-custom mb-0">
                                Deposit cash at any of our agent locations
                            </p>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Debit Card Method --}}
                <div class="col-sm-6">
                    <x-ui.card 
                        variant="default"
                        hover="lift"
                        class="bg-secondary-custom border h-100"
                        wire:click="handleMethodSelect('card')"
                        style="
                            cursor: pointer;
                            border-color: #2a2a3a;
                            border-radius: 12px;
                            transition: all 0.3s ease;
                        "
                    >
                        <div class="card-body text-center p-4">
                            {{-- Icon --}}
                            <div 
                                class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="
                                    width: 64px;
                                    height: 64px;
                                    background: rgba(168, 85, 247, 0.1);
                                "
                            >
                                <x-lucide-credit-card class="text-primary-custom" style="width: 32px; height: 32px;" />
                            </div>
                            {{-- Title --}}
                            <h3 class="h5 fw-semibold mb-2">Debit Card</h3>
                            {{-- Description --}}
                            <p class="small text-muted-custom mb-0">
                                Instant top-up using your debit card
                            </p>
                        </div>
                    </x-ui.card>
                </div>

                {{-- USSD Code Method --}}
                <div class="col-sm-6">
                    <x-ui.card 
                        variant="default"
                        hover="lift"
                        class="bg-secondary-custom border h-100"
                        wire:click="handleMethodSelect('ussd')"
                        style="
                            cursor: pointer;
                            border-color: #2a2a3a;
                            border-radius: 12px;
                            transition: all 0.3s ease;
                        "
                    >
                        <div class="card-body text-center p-4">
                            {{-- Icon --}}
                            <div 
                                class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                                style="
                                    width: 64px;
                                    height: 64px;
                                    background: rgba(192, 132, 252, 0.1);
                                "
                            >
                                <x-lucide-smartphone class="text-accent-custom" style="width: 32px; height: 32px;" />
                            </div>
                            {{-- Title --}}
                            <h3 class="h5 fw-semibold mb-2">USSD Code</h3>
                            {{-- Description --}}
                            <p class="small text-muted-custom mb-0">
                                Add money using your mobile banking USSD code
                            </p>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        @endif

        {{-- STEP 2: Bank Transfer Details --}}
        @if ($step === 'details' && $selectedMethod === 'bank-transfer')
            @include('livewire.steps.deposit-bank-transfer')
        @endif

        {{-- STEP 2: Cash Deposit Details --}}
        @if ($step === 'details' && $selectedMethod === 'cash')
            @include('livewire.steps.deposit-cash')
        @endif

        {{-- STEP 2: Card Payment Details --}}
        @if ($step === 'details' && $selectedMethod === 'card')
            @include('livewire.steps.deposit-card')
        @endif

        {{-- STEP 2: USSD Code Details --}}
        @if ($step === 'details' && $selectedMethod === 'ussd')
            @include('livewire.steps.deposit-ussd')
        @endif

        {{-- STEP 3: Success Screen --}}
        @if ($step === 'success')
            @include('livewire.steps.deposit-success')
        @endif
    </div>
</div>

{{-- JavaScript for Copy Functionality --}}
<script>
    // Listen for copy-to-clipboard event from Livewire
    Livewire.on('copy-to-clipboard', (event) => {
        const text = event.text || event[0]?.text;
        if (text) {
            navigator.clipboard.writeText(text).then(() => {
                // Feedback shown in Blade via copiedField property
            }).catch(() => {
                console.error('Failed to copy to clipboard');
            });
        }
    });

    // Alternative: Direct copy function for copy buttons
    function copyToClipboard(text, fieldId) {
        navigator.clipboard.writeText(text).then(() => {
            // Show success feedback
            const btn = document.querySelector(`[data-copy-field="${fieldId}"]`);
            if (btn) {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = 'Copied';
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                }, 2000);
            }
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
</script>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    Livewire.on('paystack:init', (payload) => {
        if (!window.PaystackPop) {
            console.error('Paystack JS not loaded');
            return;
        }

        const handler = PaystackPop.setup({
            key: payload.publicKey,
            email: payload.email,
            amount: payload.amount,
            ref: payload.reference,
            callback: function(response) {
                if (response.status === 'success') {
                    window.location.href = payload.callbackUrl + '?reference=' + response.reference;
                } else {
                    console.error('Paystack payment not successful', response);
                }
            },
            onClose: function() {
                console.log('Paystack payment window closed.');
            }
        });

        handler.openIframe();
    });
</script>
