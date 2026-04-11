<div class="container-fluid py-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh; max-width: 900px; margin: 0 auto;">
    {{-- Header --}}
    <div class="mb-5">
        <h1 class="display-5 fw-bold mb-2">Bill Payment</h1>
        <p class="text-muted-custom">Pay your bills quickly and securely</p>
    </div>

    {{-- Error Message --}}
    @if($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ $errorMessage }}
            <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
        </div>
    @endif

    {{-- Recent Bills (Show on Category Step) --}}
    @if($currentStep === 'category')
        <div class="card card-luxury border mb-4">
            <div class="card-body">
                <h3 class="h5 fw-semibold mb-4">Recent Payments</h3>
                <div class="d-flex flex-column gap-3">
                    @foreach($recentBills as $bill)
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-xl" style="background: rgba(168, 85, 247, 0.05);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-container" style="background: rgba(168, 85, 247, 0.1);">
                                    <x-lucide-receipt class="w-5 h-5 text-primary-custom" />
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $bill['provider'] }}</div>
                                    <div class="small text-muted-custom">{{ $bill['category'] }}</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold">₦{{ number_format($bill['amount'], 2) }}</div>
                                <div class="small text-muted-custom">{{ $bill['date'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Main Content Card --}}
    <div class="card card-luxury border p-4">
        {{-- Category Step --}}
        @if($currentStep === 'category')
            <div>
                <h2 class="h4 fw-bold mb-2">Select Bill Category</h2>
                <p class="text-muted-custom mb-4">Choose the type of bill you want to pay</p>

                <div class="row g-3">
                    @foreach($billCategories as $category)
                        <div class="col-sm-6">
                            <button 
                                wire:click="selectCategory('{{ $category['id'] }}')"
                                class="btn btn-outline-light w-100 p-4 text-start"
                                style="border: 1px solid #2a2a3a; border-radius: 20px; transition: all 0.3s ease;"
                            >
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-container" style="background: rgba(168, 85, 247, 0.1);">
                                        <x-dynamic-component :component="'lucide-' . $category['icon']" class="w-6 h-6 text-primary-custom" />
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ $category['name'] }}</div>
                                        <div class="small text-muted-custom">{{ count($category['providers']) }} providers available</div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Details Step --}}
        @if($currentStep === 'details' && $selectedCategory)
            <div>
                <h2 class="h4 fw-bold mb-2">{{ $selectedCategory['name'] }} Details</h2>
                <p class="text-muted-custom mb-4">Enter the payment details</p>

                <form wire:submit="submitDetails">
                    <div class="mb-3">
                        <label class="form-label">Select Provider</label>
                        <select wire:model="provider" class="form-select bg-secondary-custom border-0 py-3">
                            <option value="">-- Choose a provider --</option>
                            @foreach($selectedCategory['providers'] as $prov)
                                <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                        @error('provider') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input 
                            type="text" 
                            wire:model="accountNumber" 
                            class="form-control bg-secondary-custom border-0 py-3"
                            placeholder="Enter account number"
                        />
                        @error('accountNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary-custom border-0">₦</span>
                            <input 
                                type="number" 
                                wire:model="amount" 
                                class="form-control bg-secondary-custom border-0"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                            />
                        </div>
                        @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" wire:click="goBack" class="btn btn-outline-light flex-grow-1">Back</button>
                        <button type="submit" class="btn btn-gradient flex-grow-1">Continue</button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Confirm Step --}}
        @if($currentStep === 'confirm' && $selectedCategory)
            <div>
                <h2 class="h4 fw-bold mb-2">Confirm Payment</h2>
                <p class="text-muted-custom mb-4">Review your payment details before confirming</p>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div style="background: rgba(168, 85, 247, 0.05);" class="p-3 rounded-2">
                            <div class="small text-muted-custom mb-1">Bill Type</div>
                            <div class="fw-semibold">{{ $selectedCategory['name'] }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background: rgba(192, 132, 252, 0.05);" class="p-3 rounded-2">
                            <div class="small text-muted-custom mb-1">Provider</div>
                            <div class="fw-semibold">{{ $provider }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background: rgba(168, 85, 247, 0.05);" class="p-3 rounded-2">
                            <div class="small text-muted-custom mb-1">Account Number</div>
                            <div class="fw-semibold text-monospace">{{ substr($accountNumber, 0, 2) . str_repeat('*', strlen($accountNumber) - 4) . substr($accountNumber, -2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background: rgba(192, 132, 252, 0.05);" class="p-3 rounded-2">
                            <div class="small text-muted-custom mb-1">Amount</div>
                            <div class="h5 fw-bold text-primary-custom">₦{{ number_format($amount, 2) }}</div>
                        </div>
                    </div>
                </div>

                <div style="background: rgba(255, 255, 255, 0.05);" class="p-3 rounded-2 mb-4">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted-custom">Current Balance:</span>
                        <span>₦{{ number_format($currentBalance, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted-custom">Balance after payment:</span>
                        <span class="{{ $currentBalance - $amount >= 0 ? 'text-success' : 'text-danger' }}">
                            ₦{{ number_format($currentBalance - $amount, 2) }}
                        </span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" wire:click="goBack" class="btn btn-outline-light flex-grow-1">Back</button>
                    <button 
                        type="button" 
                        wire:click="confirmPayment" 
                        {{ $isProcessing ? 'disabled' : '' }}
                        class="btn btn-gradient flex-grow-1"
                    >
                        {{ $isProcessing ? 'Processing...' : 'Confirm Payment' }}
                    </button>
                </div>
            </div>
        @endif

        {{-- Success Step --}}
        @if($currentStep === 'success')
            <div class="text-center py-4">
                <div class="mb-4">
                    <div class="icon-container icon-container-lg mx-auto" style="background: rgba(34, 197, 94, 0.1); width: 80px; height: 80px;">
                        <x-lucide-check-circle-2 class="w-10 h-10 text-success" />
                    </div>
                </div>
                <h2 class="h4 fw-bold mb-2">Payment Successful!</h2>
                <p class="text-muted-custom mb-4">Your bill payment has been processed successfully</p>

                <div class="d-flex flex-column gap-2 mb-4">
                    <div class="small">
                        <strong>Reference Number:</strong>
                        <span class="text-monospace">{{ $referenceNumber }}</span>
                    </div>
                    <div class="small">
                        <strong>Amount:</strong>
                        <span>₦{{ number_format($amount, 2) }}</span>
                    </div>
                </div>

                <a href="{{ route('transactions') }}" class="btn btn-gradient w-100">View Transaction</a>
            </div>
        @endif
    </div>
</div>
