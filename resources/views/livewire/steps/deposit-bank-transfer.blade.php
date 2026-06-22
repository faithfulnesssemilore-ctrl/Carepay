
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
                <x-lucide-building-2 class="text-primary-custom" style="width: 24px; height: 24px;" />
            </div>
            <div>
                <h2 class="h3 fw-bold mb-1">Bank Transfer</h2>
                <p class="small text-muted-custom mb-0">Use these details to transfer money</p>
            </div>
        </div>

        @if (!$hasVirtualAccount)
            {{-- Loading State --}}
            <div class="card-luxury text-center p-4">
                <x-lucide-clock class="text-primary-custom mb-3" style="width:40px;height:40px;" />
                <h6 class="fw-bold">Setting up your account</h6>
                <p class="text-muted-custom small">Your bank account is being created. Usually takes under a minute.</p>
                <button wire:click="$refresh" class="btn btn-gradient mt-2">Refresh</button>
            </div>
        @else
            {{-- Account Details --}}
            <div class="d-flex flex-column gap-3">
                {{-- Bank Name --}}
                <div class="p-4 bg-secondary-custom rounded-xl">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted-custom mb-1">Bank Name</div>
                            <div class="fw-medium">{{ $bankName }}</div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-link p-2 text-primary-custom copy-btn"
                            data-copy-text="{{ $bankName }}"
                            data-copy-field="bank"
                            style="text-decoration: none; border: none; background: none;"
                        >
                            <x-lucide-copy style="width: 20px; height: 20px;" />
                        </button>
                    </div>
                </div>

                {{-- Account Number --}}
                <div class="p-4 bg-secondary-custom rounded-xl">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted-custom mb-1">Account Number</div>
                            <div class="h4 fw-bold mb-0 font-monospace">{{ $accountNumber }}</div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-link p-2 text-primary-custom copy-btn"
                            data-copy-text="{{ $accountNumber }}"
                            data-copy-field="account"
                            style="text-decoration: none; border: none; background: none;"
                        >
                            <x-lucide-copy style="width: 20px; height: 20px;" />
                        </button>
                    </div>
                </div>

                {{-- Account Name --}}
                <div class="p-4 bg-secondary-custom rounded-xl">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted-custom mb-1">Account Name</div>
                            <div class="fw-medium">{{ $accountName }} - CarePay</div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-link p-2 text-primary-custom copy-btn"
                            data-copy-text="{{ $accountName }} - CarePay"
                            data-copy-field="name"
                            style="text-decoration: none; border: none; background: none;"
                        >
                            <x-lucide-copy style="width: 20px; height: 20px;" />
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
                        <x-lucide-info class="text-primary-custom flex-shrink-0 mt-1" style="width: 20px; height: 20px;" />
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
        @endif
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
                    const icon = this.querySelector('svg');
                    const originalHtml = this.innerHTML;
                    
                    this.innerHTML = '<x-lucide-check style="width: 20px; height: 20px;" />';
                    this.style.color = '#22c55e';
                    
                    // Revert after 2 seconds
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.style.color = '';
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                });
            });
        });
    });
</script>
