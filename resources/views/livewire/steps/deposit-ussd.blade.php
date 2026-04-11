{{--
    USSD Code Deposit Step
    Displays USSD code for selected bank for mobile banking deposit
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
                <i class="fas fa-mobile-alt text-accent-custom" style="font-size: 24px;"></i>
            </div>
            <div>
                <h2 class="h3 fw-bold mb-1">USSD Deposit</h2>
                <p class="small text-muted-custom mb-0">Use mobile banking code</p>
            </div>
        </div>

        <form>
            {{-- Bank Selection --}}
            <div class="form-group mb-4">
                <label class="form-label">Select Your Bank</label>
                <select 
                    class="form-select bg-secondary-custom border-0 rounded-xl py-3"
                    wire:model="selectedBank"
                >
                    <option value="">Choose your bank</option>
                    <option value="chase">Chase Bank</option>
                    <option value="bofa">Bank of America</option>
                    <option value="wellsfargo">Wells Fargo</option>
                    <option value="citibank">Citibank</option>
                    <option value="usbank">US Bank</option>
                    <option value="pnc">PNC Bank</option>
                    <option value="tdbank">TD Bank</option>
                    <option value="capitalone">Capital One</option>
                </select>
            </div>

            {{-- USSD Code Display (Conditional) --}}
            @if (!empty($selectedBank))
                {{-- USSD Code Box --}}
                <div class="p-4 bg-secondary-custom rounded-xl mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted-custom mb-2">USSD Code for {{ $selectedBank }}</div>
                            <div 
                                class="h3 fw-bold font-monospace text-primary-custom"
                                style="letter-spacing: 2px;"
                            >
                                *833*{{ substr(strtoupper($selectedBank), 0, 3) }}#
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn btn-link p-2 text-primary-custom copy-btn"
                            data-copy-text="*833*{{ substr(strtoupper($selectedBank), 0, 3) }}#"
                            data-copy-field="ussd"
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
                                <p class="fw-semibold mb-2">How to Use USSD Code:</p>
                                <ol class="ps-3 mb-0">
                                    <li>Copy the USSD code above</li>
                                    <li>Dial the code from your mobile phone</li>
                                    <li>Follow the prompts on screen</li>
                                    <li>Enter your amount and confirm</li>
                                    <li>Your wallet will be credited instantly</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            @endif

            {{-- Action Button --}}
            <button
                type="button"
                class="btn btn-gradient w-100 py-3 mt-4"
                wire:click="handleConfirmTransfer"
                @if (empty($selectedBank))
                    disabled
                @endif
            >
                I understand
            </button>
        </form>
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
