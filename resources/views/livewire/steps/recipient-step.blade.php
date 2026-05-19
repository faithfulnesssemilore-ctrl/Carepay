{{-- recipient step - account number + bank selection like OPay --}}

<div class="d-flex flex-column gap-4">

    {{-- heading --}}
    <div>
        <h2 class="fw-bold mb-1" style="font-size:1.5rem;">Send Money</h2>
        <p class="text-muted-custom small mb-0">Transfer to any Nigerian bank account</p>
    </div>

    {{-- recent contacts row - shown at top like OPay --}}
    @if(count($recentContacts) > 0)
    <div>
        <p class="text-muted-custom small fw-semibold mb-2" style="letter-spacing:0.5px;text-transform:uppercase;font-size:11px;">Recent</p>
        <div class="d-flex gap-3" style="overflow-x:auto;padding-bottom:6px;scrollbar-width:none;">
            @foreach($recentContacts as $contact)
            <button
                type="button"
                class="d-flex flex-column align-items-center gap-1 border-0 bg-transparent p-0"
                style="min-width:64px;cursor:pointer;"
                wire:click="selectRecentContact({{ json_encode($contact) }})"
            >
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white gradient-bg-primary"
                     style="width:52px;height:52px;font-size:18px;flex-shrink:0;">
                    {{ $contact['initials'] }}
                </div>
                <span class="text-muted-custom text-center text-truncate"
                      style="font-size:10px;max-width:60px;line-height:1.3;">
                    {{ $contact['account_name'] }}
                </span>
                <span class="text-muted-custom text-center text-truncate"
                      style="font-size:9px;max-width:60px;opacity:0.6;">
                    {{ $contact['bank_name'] }}
                </span>
            </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- divider --}}
    <div style="display:flex;align-items:center;gap:12px;">
        <div style="flex:1;height:1px;background:rgba(255,255,255,0.07);"></div>
        <span class="text-muted-custom" style="font-size:11px;">ENTER ACCOUNT DETAILS</span>
        <div style="flex:1;height:1px;background:rgba(255,255,255,0.07);"></div>
    </div>

    {{-- account number input --}}
    <div>
        <label class="form-label small fw-semibold text-muted-custom"
               style="letter-spacing:0.3px;text-transform:uppercase;font-size:11px;">
            Account Number
        </label>
        <div class="input-group">
            <span class="input-group-text"
                  style="background:#1a1a24;border:1px solid rgba(168,85,247,0.25);
                         border-right:none;border-radius:12px 0 0 12px;padding:0 14px;">
                <x-lucide-hash style="width:16px;height:16px;color:rgba(168,85,247,0.7);" />
            </span>
            <input
                type="number"
                wire:model.live.debounce.500ms="accountNumber"
                placeholder="Enter 10-digit account number"
                maxlength="10"
                inputmode="numeric"
                class="form-control"
                style="background:#1a1a24;border:1px solid rgba(168,85,247,0.25);
                       border-left:none;color:white;padding:14px 16px;
                       border-radius:0 12px 12px 0;font-size:1rem;
                       letter-spacing:2px;"
            />
        </div>
        @if(strlen($accountNumber) > 0 && strlen($accountNumber) !== 10)
        <div class="small mt-1" style="color:#f59e0b;">
            <x-lucide-alert-triangle style="width:12px;height:12px;display:inline;" />
            Account number must be exactly 10 digits
        </div>
        @endif
    </div>

    {{-- bank selection --}}
    <div>
        <label class="form-label small fw-semibold text-muted-custom"
               style="letter-spacing:0.3px;text-transform:uppercase;font-size:11px;">
            Select Bank
        </label>
        <div class="input-group">
            <span class="input-group-text"
                  style="background:#1a1a24;border:1px solid rgba(168,85,247,0.25);
                         border-right:none;border-radius:12px 0 0 12px;padding:0 14px;">
                <x-lucide-building-2 style="width:16px;height:16px;color:rgba(168,85,247,0.7);" />
            </span>
            <select
                wire:model.live="selectedBankCode"
                class="form-select"
                style="background:#1a1a24;border:1px solid rgba(168,85,247,0.25);
                       border-left:none;color:white;padding:14px 16px;
                       border-radius:0 12px 12px 0;font-size:0.95rem;"
            >
                <option value="">-- Choose your bank --</option>
                @foreach($banks as $bank)
                <option value="{{ $bank['code'] }}" style="background:#1a1a24;color:white;">
                    {{ $bank['name'] }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- account name resolution result --}}
    @if($isResolvingAccount)
    <div class="d-flex align-items-center gap-3 p-3 rounded-xl"
         style="background:rgba(168,85,247,0.08);border:1px solid rgba(168,85,247,0.2);">
        <div class="spinner-border spinner-border-sm text-primary-custom" role="status"></div>
        <span class="text-muted-custom small">Verifying account...</span>
    </div>

    @elseif($resolvedAccountName)
    <div class="d-flex align-items-center gap-3 p-3 rounded-xl animate-fade-up"
         style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.3);">
        <div style="width:40px;height:40px;border-radius:50%;background:rgba(34,197,94,0.15);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <x-lucide-check-circle style="width:20px;height:20px;color:#22c55e;" />
        </div>
        <div class="flex-fill">
            <div class="fw-bold text-white" style="font-size:0.95rem;">
                {{ $resolvedAccountName }}
            </div>
            <div class="text-muted-custom" style="font-size:12px;">
                {{ $selectedBankName ?: 'Bank' }} · {{ $accountNumber }}
            </div>
        </div>
        <x-lucide-check style="width:18px;height:18px;color:#22c55e;flex-shrink:0;" />
    </div>

    @elseif($accountResolutionError)
    <div class="d-flex align-items-center gap-3 p-3 rounded-xl"
         style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);">
        <x-lucide-alert-circle style="width:18px;height:18px;color:#ef4444;flex-shrink:0;" />
        <span class="small" style="color:#ef4444;">{{ $accountResolutionError }}</span>
    </div>
    @endif

    {{-- error message --}}
    @if($errorMessage)
    <div class="d-flex align-items-center gap-2 p-3 rounded-xl"
         style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);">
        <x-lucide-alert-circle style="width:16px;height:16px;color:#ef4444;flex-shrink:0;" />
        <span class="small" style="color:#ef4444;">{{ $errorMessage }}</span>
    </div>
    @endif

    {{-- continue button --}}
    <button
        type="button"
        wire:click="proceedToAmount"
        wire:loading.attr="disabled"
        class="btn btn-gradient w-100 fw-semibold"
        style="padding:14px;font-size:1rem;border-radius:12px;"
        @if(!$resolvedAccountName) disabled @endif
    >
        <span wire:loading.remove wire:target="proceedToAmount">
            Continue
            <x-lucide-arrow-right style="width:16px;height:16px;display:inline;margin-left:6px;" />
        </span>
        <span wire:loading wire:target="proceedToAmount">
            <span class="spinner-border spinner-border-sm me-2"></span>
            Please wait...
        </span>
    </button>

    {{-- info note --}}
    <div class="d-flex align-items-center gap-2 p-3 rounded-xl"
         style="background:rgba(168,85,247,0.06);border:1px solid rgba(168,85,247,0.15);">
        <x-lucide-info style="width:14px;height:14px;color:#a855f7;flex-shrink:0;" />
        <span class="text-muted-custom" style="font-size:12px;line-height:1.5;">
            Enter any 10-digit Nigerian bank account number. We verify it with Paystack before you proceed.
            For a demo transfer, use account <strong>9026446100</strong> with bank <strong>CarePay</strong> or <strong>Opay</strong>.
        </span>
    </div>

</div>
