<div style="max-width:640px;margin:0 auto;">

    {{-- error --}}
    @if($errorMessage)
    <div class="d-flex align-items-center gap-2 p-3 rounded-xl mb-4"
         style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);">
        <x-lucide-alert-circle style="width:16px;height:16px;color:#ef4444;flex-shrink:0;" />
        <span style="color:#ef4444;font-size:14px;">{{ $errorMessage }}</span>
    </div>
    @endif

    {{-- STEP: category --}}
    @if($currentStep === 'category')

        <div class="mb-4">
            <h2 class="fw-bold mb-1" style="font-size:1.5rem;">Pay bills quickly</h2>
            <p class="text-muted-custom" style="font-size:0.95rem;">Choose a service and pay directly from your wallet.</p>
        </div>

        {{-- balance pill --}}
        <div class="d-flex align-items-center gap-2 mb-4 p-3 rounded-xl"
             style="background:rgba(168,85,247,0.08);border:1px solid rgba(168,85,247,0.15);">
            <x-lucide-wallet style="width:16px;height:16px;color:#a855f7;" />
            <span style="font-size:13px;color:rgba(255,255,255,0.6);">Balance:</span>
            <span style="font-size:13px;font-weight:700;color:white;">₦{{ number_format($currentBalance, 2) }}</span>
        </div>

        {{-- category grid --}}
        <div class="row g-3 mb-4">
            @foreach([
                ['id' => 'airtime',     'icon' => 'smartphone', 'label' => 'Airtime',     'desc' => 'MTN, Airtel, Glo, 9mobile',         'color' => '#a855f7'],
                ['id' => 'data',        'icon' => 'wifi',        'label' => 'Data',         'desc' => 'All networks',                      'color' => '#22c55e'],
                ['id' => 'electricity', 'icon' => 'zap',         'label' => 'Electricity',  'desc' => 'Prepaid & Postpaid meters',         'color' => '#f59e0b'],
                ['id' => 'cable',       'icon' => 'tv-2',        'label' => 'Cable TV',     'desc' => 'DSTV, GOtv, Startimes',            'color' => '#3b82f6'],
            ] as $cat)
            <div class="col-6">
                <button wire:click="selectCategory('{{ $cat['id'] }}')"
                        class="w-100 border-0 text-start p-4 rounded-xl hover-lift"
                        style="background:#141420;border:1px solid rgba(255,255,255,0.06) !important;
                               cursor:pointer;transition:all 0.25s;">
                    <div style="width:44px;height:44px;border-radius:12px;margin-bottom:12px;
                                background:{{ $cat['color'] }}20;
                                display:flex;align-items:center;justify-content:center;">
                        <x-dynamic-component :component="'lucide-' . $cat['icon']"
                            style="width:20px;height:20px;color:{{ $cat['color'] }};" />
                    </div>
                    <div style="font-weight:700;color:white;font-size:15px;margin-bottom:3px;">
                        {{ $cat['label'] }}
                    </div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);">
                        {{ $cat['desc'] }}
                    </div>
                </button>
            </div>
            @endforeach
        </div>

       

    @endif

    {{-- STEP: provider --}}
    @if($currentStep === 'provider')
    <div>
        <button wire:click="goBack" class="d-flex align-items-center gap-2 border-0 bg-transparent mb-4 p-0"
                style="color:rgba(255,255,255,0.5);font-size:14px;cursor:pointer;">
            <x-lucide-arrow-left style="width:16px;height:16px;" />
            Back
        </button>

        <h2 class="fw-bold mb-1" style="font-size:1.3rem;">Choose Provider</h2>
        <p class="text-muted-custom small mb-4">
            Select your {{ $selectedCategory }} provider
        </p>

        @php
            $providers = match($selectedCategory) {
                'airtime'     => $airtimeProviders,
                'data'        => $dataProviders,
                'electricity' => $electricityProviders,
                'cable'       => $cableProviders,
                default       => [],
            };
        @endphp

        <div class="d-flex flex-column gap-2">
            @foreach($providers as $prov)
            <button wire:click="selectProvider('{{ $prov['id'] }}')"
                    class="d-flex align-items-center justify-content-between p-4 rounded-xl border-0 text-start w-100"
                    style="background:#141420;cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(168,85,247,0.1)'"
                    onmouseout="this.style.background='#141420'">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:10px;
                                background:rgba(168,85,247,0.1);
                                display:flex;align-items:center;justify-content:center;
                                font-weight:800;font-size:12px;color:#a855f7;">
                        {{ strtoupper(substr($prov['name'], 0, 2)) }}
                    </div>
                    <span style="color:white;font-weight:600;font-size:15px;">{{ $prov['name'] }}</span>
                </div>
                <x-lucide-chevron-right style="width:16px;height:16px;color:rgba(255,255,255,0.3);" />
            </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- STEP: details --}}
    @if($currentStep === 'details')
    <div>
        <button wire:click="goBack" class="d-flex align-items-center gap-2 border-0 bg-transparent mb-4 p-0"
                style="color:rgba(255,255,255,0.5);font-size:14px;cursor:pointer;">
            <x-lucide-arrow-left style="width:16px;height:16px;" />
            Back
        </button>

        <h2 class="fw-bold mb-1" style="font-size:1.3rem;">
            {{ ucfirst($selectedCategory) }} Details
        </h2>
        <p class="text-muted-custom small mb-4">{{ $selectedProvider }}</p>

        {{-- phone number for airtime and data --}}
        @if(in_array($selectedCategory, ['airtime', 'data']))
        <div class="mb-4">
            <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                           text-transform:uppercase;letter-spacing:0.3px;">
                Phone Number
            </label>
            <div class="input-group mt-1">
                <span class="input-group-text"
                      style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                             border-right:none;border-radius:12px 0 0 12px;">
                    <x-lucide-smartphone style="width:16px;height:16px;color:#a855f7;" />
                </span>
                <input type="tel" wire:model="phone"
                       class="form-control"
                       placeholder="08012345678"
                       maxlength="11"
                       style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                              border-left:none;color:white;padding:14px 16px;
                              border-radius:0 12px 12px 0;" />
            </div>
        </div>
        @endif

        {{-- data plan selector --}}
        @if($selectedCategory === 'data')
        <div class="mb-4">
            <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                           text-transform:uppercase;letter-spacing:0.3px;">
                Data Plan
            </label>
            <select wire:model="dataPlan" class="form-select mt-1"
                    style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                           color:white;padding:14px 16px;border-radius:12px;">
                <option value="">-- Select a plan --</option>
                @foreach($dataPlans as $plan)
                <option value="{{ $plan['code'] }}" style="background:#141420;">
                    {{ $plan['name'] }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- meter number for electricity --}}
        @if($selectedCategory === 'electricity')
        <div class="mb-4">
            <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                           text-transform:uppercase;letter-spacing:0.3px;">
                Meter Number
            </label>
            <div class="input-group mt-1">
                <span class="input-group-text"
                      style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                             border-right:none;border-radius:12px 0 0 12px;">
                    <x-lucide-hash style="width:16px;height:16px;color:#a855f7;" />
                </span>
                <input type="text" wire:model="meterNumber"
                       class="form-control"
                       placeholder="Enter meter number"
                       style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                              border-left:none;color:white;padding:14px 16px;
                              border-radius:0 12px 12px 0;" />
            </div>
        </div>

        <div class="mb-4">
            <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                           text-transform:uppercase;letter-spacing:0.3px;">
                Meter Type
            </label>
            <div class="d-flex gap-2 mt-2">
                <button wire:click="$set('meterType', 'prepaid')" type="button"
                        class="flex-fill py-2 rounded-xl border-0 fw-semibold"
                        style="background:{{ $meterType === 'prepaid' ? 'rgba(168,85,247,0.2)' : '#141420' }};
                               color:{{ $meterType === 'prepaid' ? '#a855f7' : 'rgba(255,255,255,0.4)' }};
                               border:1px solid {{ $meterType === 'prepaid' ? '#a855f7' : 'rgba(255,255,255,0.08)' }} !important;">
                    Prepaid
                </button>
                <button wire:click="$set('meterType', 'postpaid')" type="button"
                        class="flex-fill py-2 rounded-xl border-0 fw-semibold"
                        style="background:{{ $meterType === 'postpaid' ? 'rgba(168,85,247,0.2)' : '#141420' }};
                               color:{{ $meterType === 'postpaid' ? '#a855f7' : 'rgba(255,255,255,0.4)' }};
                               border:1px solid {{ $meterType === 'postpaid' ? '#a855f7' : 'rgba(255,255,255,0.08)' }} !important;">
                    Postpaid
                </button>
            </div>
        </div>

        <div class="mb-4">
            <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                           text-transform:uppercase;letter-spacing:0.3px;">
                Phone Number
            </label>
            <input type="tel" wire:model="phone"
                   class="form-control mt-1"
                   placeholder="08012345678"
                   style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                          color:white;padding:14px 16px;border-radius:12px;" />
        </div>
        @endif

        {{-- smartcard for cable --}}
        @if($selectedCategory === 'cable')
        <div class="mb-4">
            <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                           text-transform:uppercase;letter-spacing:0.3px;">
                Smartcard Number
            </label>
            <div class="input-group mt-1">
                <span class="input-group-text"
                      style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                             border-right:none;border-radius:12px 0 0 12px;">
                    <x-lucide-credit-card style="width:16px;height:16px;color:#a855f7;" />
                </span>
                <input type="text" wire:model="smartcard"
                       class="form-control"
                       placeholder="Enter smartcard number"
                       style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                              border-left:none;color:white;padding:14px 16px;
                              border-radius:0 12px 12px 0;" />
            </div>
        </div>
        @endif

        {{-- amount (not for data) --}}
        @if($selectedCategory !== 'data')
        <div class="mb-4">
            <div class="card-luxury p-3 mb-4" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="text-muted-custom small">Selected provider</div>
                        <div class="fw-semibold">{{ $selectedProvider }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted-custom small">Category</div>
                        <div class="fw-semibold">{{ ucfirst($selectedCategory) }}</div>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted-custom small">Available balance</div>
                    <div class="fw-semibold">₦{{ number_format($currentBalance, 2) }}</div>
                </div>
            </div>

            <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                           text-transform:uppercase;letter-spacing:0.3px;">
                Amount (₦)
            </label>
            <div class="input-group mt-1">
                <span class="input-group-text"
                      style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                             border-right:none;border-radius:12px 0 0 12px;
                             color:#a855f7;font-weight:700;">₦</span>
                <input type="number" wire:model="amount"
                       class="form-control"
                       placeholder="0.00" min="0" step="50"
                       style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                              border-left:none;color:white;padding:14px 16px;
                              border-radius:0 12px 12px 0;font-size:1.1rem;" />
            </div>
            <div style="font-size:11px;color:rgba(255,255,255,0.3);margin-top:6px;">
                Balance: ₦{{ number_format($currentBalance, 2) }}
            </div>
        </div>
        @else
        <div class="mb-5"></div>
        @endif

        @if($errorMessage)
        <div class="p-3 rounded-xl mb-3"
             style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);
                    color:#ef4444;font-size:13px;">
            {{ $errorMessage }}
        </div>
        @endif

        <button wire:click="submitDetails"
                wire:loading.attr="disabled"
                class="btn btn-gradient w-100 fw-semibold"
                style="padding:14px;font-size:1rem;border-radius:12px;">
            <span wire:loading.remove wire:target="submitDetails">
                Continue
                <x-lucide-arrow-right style="width:16px;height:16px;display:inline;margin-left:6px;" />
            </span>
            <span wire:loading wire:target="submitDetails">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Checking...
            </span>
        </button>
    </div>
    @endif

    {{-- STEP: confirm --}}
    @if($currentStep === 'confirm')
    <div>
        <button wire:click="goBack" class="d-flex align-items-center gap-2 border-0 bg-transparent mb-4 p-0"
                style="color:rgba(255,255,255,0.5);font-size:14px;cursor:pointer;">
            <x-lucide-arrow-left style="width:16px;height:16px;" />
            Back
        </button>

        <h2 class="fw-bold mb-1" style="font-size:1.3rem;">Confirm Payment</h2>
        <p class="text-muted-custom small mb-4">Review before paying</p>

        <div class="card-luxury p-4 mb-4">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:rgba(255,255,255,0.5);font-size:13px;">Category</span>
                    <span style="font-weight:600;font-size:14px;">{{ ucfirst($selectedCategory) }}</span>
                </div>
                <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:rgba(255,255,255,0.5);font-size:13px;">Provider</span>
                    <span style="font-weight:600;font-size:14px;">{{ $selectedProvider }}</span>
                </div>
                <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
                @if($phone)
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:rgba(255,255,255,0.5);font-size:13px;">Phone</span>
                    <span style="font-weight:600;font-size:14px;font-family:monospace;">{{ $phone }}</span>
                </div>
                <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
                @endif
                @if($meterNumber)
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:rgba(255,255,255,0.5);font-size:13px;">Meter Number</span>
                    <span style="font-weight:600;font-size:14px;font-family:monospace;">{{ $meterNumber }}</span>
                </div>
                <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
                @endif
                @if($smartcard)
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:rgba(255,255,255,0.5);font-size:13px;">Smartcard</span>
                    <span style="font-weight:600;font-size:14px;font-family:monospace;">{{ $smartcard }}</span>
                </div>
                <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
                @endif
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:rgba(255,255,255,0.5);font-size:13px;">Amount</span>
                    <span style="font-weight:800;font-size:1.2rem;color:#a855f7;">
                        ₦{{ number_format($amount ?: 0, 2) }}
                    </span>
                </div>
                <div style="height:1px;background:rgba(255,255,255,0.06);"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <span style="color:rgba(255,255,255,0.5);font-size:13px;">Balance after</span>
                    <span style="font-weight:600;font-size:14px;
                                 color:{{ ($currentBalance - floatval($amount)) >= 0 ? '#22c55e' : '#ef4444' }};">
                        ₦{{ number_format($currentBalance - floatval($amount ?: 0), 2) }}
                    </span>
                </div>
            </div>
        </div>

        @if($errorMessage)
        <div class="p-3 rounded-xl mb-3"
             style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);
                    color:#ef4444;font-size:13px;">
            {{ $errorMessage }}
        </div>
        @endif

        <button wire:click="confirmPayment"
                wire:loading.attr="disabled"
                class="btn btn-gradient w-100 fw-semibold"
                style="padding:14px;font-size:1rem;border-radius:12px;">
            <span wire:loading.remove wire:target="confirmPayment">
                Pay Now
                <x-lucide-zap style="width:16px;height:16px;display:inline;margin-left:6px;" />
            </span>
            <span wire:loading wire:target="confirmPayment">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Processing payment...
            </span>
        </button>
    </div>
    @endif

    {{-- STEP: success --}}
    @if($currentStep === 'success')
    <div class="text-center py-5">
        <div style="width:80px;height:80px;border-radius:50%;background:rgba(34,197,94,0.1);
                    display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <x-lucide-check-circle style="width:40px;height:40px;color:#22c55e;" />
        </div>

        <h2 class="fw-bold mb-2">Payment Successful!</h2>
        <p class="text-muted-custom mb-4">Your bill has been paid successfully</p>

        <div class="card-luxury p-4 mb-5 text-start">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted-custom small">Reference</span>
                <span style="font-family:monospace;font-size:13px;">{{ $referenceNumber }}</span>
            </div>
            @if($token)
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted-custom small">Token</span>
                <span style="font-family:monospace;font-size:13px;color:#22c55e;">{{ $token }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between">
                <span class="text-muted-custom small">Amount</span>
                <span style="font-weight:700;color:#a855f7;">₦{{ number_format($amount ?: 0, 2) }}</span>
            </div>
        </div>

        <div class="d-flex gap-3 flex-column flex-sm-row">
            <button wire:click="resetForm" class="btn btn-outline-light flex-fill" style="border-radius:12px;padding:12px;">
                Pay Another
            </button>
            @if($successTransactionId)
            <a href="{{ route('transaction.receipt.download', ['transaction' => $successTransactionId]) }}"
               class="btn btn-light flex-fill"
               style="border-radius:12px;padding:12px;">
                Download Receipt
            </a>
            @endif
            <a href="{{ route('transactions') }}" class="btn btn-gradient flex-fill" style="border-radius:12px;padding:12px;">
                View History
            </a>
        </div>
    </div>
    @endif

</div>
