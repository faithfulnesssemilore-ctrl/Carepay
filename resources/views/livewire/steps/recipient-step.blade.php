{{--
    Recipient Selection Step - Updated to use Account Number + Bank Selection
    Instead of searching for recipients, users enter account number and select bank
    Account name is resolved via Paystack API
--}}

<div class="d-flex flex-column gap-4">
    <div>
        <h2 class="h3 fw-bold mb-2">Send to Account</h2>
        <p class="text-muted-custom">Enter account number and select bank</p>
    </div>

    {{-- Account Number Input --}}
    <div class="mb-3">
        <label class="form-label fw-medium">Account Number</label>
        <x-ui.input 
            type="text"
            name="accountNumber"
            placeholder="Enter 10-digit account number"
            class="bg-secondary-custom border-0 rounded-xl"
            wire:model.live="accountNumber"
            maxlength="10"
            pattern="[0-9]*"
            inputmode="numeric"
        />
        <div class="small text-muted-custom mt-1">Your account must be 10 digits (like OpAy)</div>
    </div>

    {{-- Bank Selection Dropdown --}}
    <div class="mb-3">
        <label class="form-label fw-medium">Bank</label>
        <select 
            class="form-control bg-secondary-custom border-0 rounded-xl text-white"
            wire:model.live="selectedBankCode"
            style="color: #999 !important;"
        >
            <option value="">-- Select a bank --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank['code'] }}">{{ $bank['name'] }}</option>
            @endforeach
        </select>
    </div>

    {{-- Account Resolution Status --}}
    @if($accountResolutionError)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <x-lucide-alert-triangle class="me-2" style="width: 18px; height: 18px; display: inline;" />
            {{ $accountResolutionError }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($resolvedAccountName)
        <div class="card-luxury p-4 border border-success">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center gradient-bg-primary text-white" style="width: 48px; height: 48px; flex-shrink: 0;">
                    <x-lucide-check style="width: 24px; height: 24px;" />
                </div>
                <div class="flex-fill">
                    <div class="small text-muted-custom">Account Name</div>
                    <div class="fw-bold">{{ $resolvedAccountName }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Error Message --}}
    @if($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <x-lucide-alert-circle class="me-2" style="width: 18px; height: 18px; display: inline;" />
            {{ $errorMessage }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="d-flex gap-3">
        <button 
            type="button"
            class="btn btn-outline-secondary flex-fill rounded-xl"
            wire:click="resetForm"
        >
            Cancel
        </button>
        <button 
            type="button"
            class="btn btn-gradient flex-fill rounded-xl fw-semibold"
            wire:click="proceedToAmount"
            :disabled="!$resolvedAccountName"
            :disabled="isProcessing"
        >
            <span wire:loading.remove>Continue</span>
            <span wire:loading>
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Loading...
            </span>
        </button>
    </div>
</div>
