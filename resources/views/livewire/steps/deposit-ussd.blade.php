{{--
    USSD Code Deposit Step
    Displays USSD code for selected bank and amount
    User can copy code or dial directly
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
                <x-lucide-smartphone class="text-accent-custom" style="width: 24px; height: 24px;" />
            </div>
            <div>
                <h2 class="h3 fw-bold mb-1">USSD Deposit</h2>
                <p class="small text-muted-custom mb-0">Dial code from any mobile phone</p>
            </div>
        </div>

        <div class="d-flex flex-column gap-4">
            {{-- Amount Input --}}
            <div class="form-group">
                <label class="form-label fw-medium">Deposit Amount</label>
                <div class="position-relative">
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
                    <input 
                        type="number"
                        class="form-control bg-secondary-custom border-0 rounded-xl display-6 fw-bold py-3"
                        style="padding-left: 3rem;"
                        placeholder="0"
                        wire:model.live="ussdAmount"
                        min="100"
                        step="100"
                    />
                </div>
            </div>

            {{-- Bank Selection --}}
            <div class="form-group">
                <label class="form-label fw-medium">Select Bank</label>
                <select 
                    class="form-select bg-secondary-custom border-0 rounded-xl py-3 text-white"
                    wire:model.live="selectedBank"
                    style="color: #999 !important;"
                >
                    <option value="">-- Choose your bank --</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- USSD Code Display (Conditional) --}}
            @if (!empty($ussdCode))
                {{-- USSD Code Box --}}
                <div class="p-4 bg-secondary-custom rounded-xl border border-success">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="small text-muted-custom mb-2">USSD Code to Dial</div>
                            <div 
                                class="display-6 fw-bold font-monospace text-primary-custom"
                                style="letter-spacing: 2px; word-break: break-all;"
                            >
                                {{ $ussdCode }}
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-link p-2 text-primary-custom copy-btn flex-shrink-0"
                            data-copy-text="{{ $ussdCode }}"
                            data-copy-field="ussd"
                            style="text-decoration: none; border: none; background: none;"
                        >
                            <x-lucide-copy style="width: 24px; height: 24px;" />
                        </button>
                    </div>
                </div>

                {{-- Instructions Card --}}
                <x-ui.card 
                    variant="default"
                    class="border-0"
                    style="
                        background: rgba(192, 132, 252, 0.05);
                        border: 1px solid rgba(192, 132, 252, 0.2) !important;
                    "
                >
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <x-lucide-info class="text-accent-custom flex-shrink-0 mt-1" style="width: 20px; height: 20px;" />
                            <div class="small">
                                <p class="fw-semibold mb-2">How to Deposit:</p>
                                <ol class="ps-3 mb-0">
                                    <li>Copy or note the USSD code above</li>
                                    <li>Open your phone dialer</li>
                                    <li>Dial the code and press call</li>
                                    <li>Follow the USSD menu prompts</li>
                                    <li>Your wallet will be credited instantly</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Action Buttons --}}
                <div class="d-grid gap-3">
                    <a 
                        href="tel:{{ urlencode($ussdCode) }}" 
                        class="btn btn-gradient btn-lg"
                        style="text-decoration: none;"
                    >
                        <x-lucide-phone style="width: 18px; height: 18px; display: inline; margin-right: 8px;" />
                        Open Dialer
                    </a>
                    <button
                        type="button"
                        class="btn btn-outline-light py-3 rounded-xl"
                        style="border-color: #2a2a3a;"
                        wire:click="handleConfirmTransfer"
                    >
                        Already Sent - Confirm
                    </button>
                </div>
            @elseif (!empty($selectedBank) && empty($ussdCode))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <x-lucide-alert-triangle style="width: 18px; height: 18px; display: inline; margin-right: 8px;" />
                    Please enter an amount to generate USSD code
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif (empty($selectedBank) && !empty($ussdAmount))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <x-lucide-alert-triangle style="width: 18px; height: 18px; display: inline; margin-right: 8px;" />
                    Please select your bank to generate USSD code
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @else
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <x-lucide-info style="width: 18px; height: 18px; display: inline; margin-right: 8px;" />
                    Enter amount and select your bank above
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
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
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<svg style="width: 24px; height: 24px; color: #22c55e;"><use xlink:href="#lucide-check"></use></svg>';
                    
                    // Revert after 2 seconds
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                });
            });
        });
    });
</script>
