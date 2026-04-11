{{--
    Cash Deposit Step
    Displays reference ID for cash deposits at agent locations
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
                    background: rgba(192, 132, 252, 0.1);
                "
            >
                <i class="fas fa-wallet text-accent-custom" style="font-size: 24px;"></i>
            </div>
            <div>
                <h2 class="h3 fw-bold mb-1">Cash Deposit</h2>
                <p class="small text-muted-custom mb-0">Visit any agent location</p>
            </div>
        </div>

        {{-- Reference ID --}}
        <div class="p-4 bg-secondary-custom rounded-xl mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="small text-muted-custom mb-1">Your Reference ID</div>
                    <div class="h3 fw-bold mb-0 font-monospace text-primary-custom">CPY-458921</div>
                </div>
                <button
                    type="button"
                    class="btn btn-link p-2 text-primary-custom copy-btn"
                    data-copy-text="CPY-458921"
                    data-copy-field="reference"
                    style="text-decoration: none; border: none; background: none;"
                >
                    <i class="fas fa-copy" style="font-size: 20px;"></i>
                </button>
            </div>
        </div>

        {{-- Instructions Card --}}
        <x-ui.card 
            variant="default"
            class="border-0 mb-4"
            style="
                background: rgba(192, 132, 252, 0.05);
                border: 1px solid rgba(192, 132, 252, 0.2) !important;
            "
        >
            <div class="card-body">
                <div class="d-flex gap-3">
                    <i class="fas fa-info-circle text-accent-custom flex-shrink-0 mt-1" style="font-size: 20px;"></i>
                    <div class="small">
                        <p class="fw-semibold mb-2">How to Deposit:</p>
                        <ol class="ps-3 mb-0">
                            <li>Copy your Reference ID above</li>
                            <li>Visit any CarePay agent location near you</li>
                            <li>Provide your Reference ID and the amount</li>
                            <li>Complete the cash deposit</li>
                            <li>Your wallet will be credited instantly</li>
                        </ol>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Action Button --}}
        <button
            type="button"
            class="btn btn-gradient w-100 py-3"
            wire:click="handleConfirmTransfer"
        >
            I understand
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
