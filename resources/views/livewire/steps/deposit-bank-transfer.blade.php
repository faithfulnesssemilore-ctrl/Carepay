{{--
    Bank Transfer Deposit Step
    Displays virtual account details for bank transfer
    User can copy account details and confirm transfer
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
                <i class="fas fa-building text-primary-custom" style="font-size: 24px;"></i>
            </div>
            <div>
                <h2 class="h3 fw-bold mb-1">Bank Transfer</h2>
                <p class="small text-muted-custom mb-0">Use these details to transfer money</p>
            </div>
        </div>

        {{-- Account Details --}}
        <div class="d-flex flex-column gap-3">
            {{-- Bank Name --}}
            <div class="p-4 bg-secondary-custom rounded-xl">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted-custom mb-1">Bank Name</div>
                        <div class="fw-medium">CarePay Virtual Bank</div>
                    </div>
                    <button
                        type="button"
                        class="btn btn-link p-2 text-primary-custom copy-btn"
                        data-copy-text="CarePay Virtual Bank"
                        data-copy-field="bank"
                        style="text-decoration: none; border: none; background: none;"
                    >
                        <i class="fas fa-copy" style="font-size: 20px;"></i>
                    </button>
                </div>
            </div>

            {{-- Account Number --}}
            <div class="p-4 bg-secondary-custom rounded-xl">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted-custom mb-1">Account Number</div>
                        <div class="h4 fw-bold mb-0 font-monospace">7845621039</div>
                    </div>
                    <button
                        type="button"
                        class="btn btn-link p-2 text-primary-custom copy-btn"
                        data-copy-text="7845621039"
                        data-copy-field="account"
                        style="text-decoration: none; border: none; background: none;"
                    >
                        <i class="fas fa-copy" style="font-size: 20px;"></i>
                    </button>
                </div>
            </div>

            {{-- Account Name --}}
            <div class="p-4 bg-secondary-custom rounded-xl">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-muted-custom mb-1">Account Name</div>
                        <div class="fw-medium">John Doe - CarePay</div>
                    </div>
                    <button
                        type="button"
                        class="btn btn-link p-2 text-primary-custom copy-btn"
                        data-copy-text="John Doe - CarePay"
                        data-copy-field="name"
                        style="text-decoration: none; border: none; background: none;"
                    >
                        <i class="fas fa-copy" style="font-size: 20px;"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Instructions Card --}}
        <x-ui.card 
            variant="default"
            class="mt-4 border-0"
            style="
                background: rgba(168, 85, 247, 0.05);
                border: 1px solid rgba(168, 85, 247, 0.2) !important;
            "
        >
            <div class="card-body">
                <div class="d-flex gap-3">
                    <i class="fas fa-info-circle text-primary-custom flex-shrink-0 mt-1" style="font-size: 20px;"></i>
                    <div class="small">
                        <p class="fw-semibold mb-2">Instructions:</p>
                        <ol class="ps-3 mb-0">
                            <li>Copy the account number above</li>
                            <li>Go to your bank's app or website</li>
                            <li>Initiate a transfer to the account number</li>
                            <li>Return here and click "I have sent the money"</li>
                            <li>Your wallet will be credited within 5-10 minutes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Action Button --}}
        <button
            type="button"
            class="btn btn-gradient w-100 py-3 mt-4"
            wire:click="handleConfirmTransfer"
        >
            I have sent the money
        </button>
    </div>
</x-ui.card>

<script>
    // Copy button functionality
    document.addEventListener('DOMContentLoaded', function() {
        const copyButtons = document.querySelectorAll('.copy-btn');
        
        copyButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const text = this.dataset.copyText;
                const field = this.dataset.copyField;
                
                navigator.clipboard.writeText(text).then(() => {
                    // Update button to show success
                    const icon = this.querySelector('i');
                    const originalClass = icon.className;
                    icon.className = 'fas fa-check';
                    icon.style.color = '#a855f7';
                    
                    // Revert after 2 seconds
                    setTimeout(() => {
                        icon.className = originalClass;
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                });
            });
        });
    });
</script>
