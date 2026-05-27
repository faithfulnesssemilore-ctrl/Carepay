<div style="max-width:680px;margin:0 auto;">

    {{-- success / error --}}
    @if($successMessage)
    <div class="d-flex align-items-center gap-2 p-3 rounded-xl mb-4"
         style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);">
        <x-lucide-check-circle style="width:16px;height:16px;color:#22c55e;flex-shrink:0;" />
        <span style="color:#22c55e;font-size:14px;">{{ $successMessage }}</span>
    </div>
    @endif

    @if($errorMessage)
    <div class="d-flex align-items-center gap-2 p-3 rounded-xl mb-4"
         style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);">
        <x-lucide-alert-circle style="width:16px;height:16px;color:#ef4444;flex-shrink:0;" />
        <span style="color:#ef4444;font-size:14px;">{{ $errorMessage }}</span>
    </div>
    @endif

    {{-- profile card --}}
    <div class="card-luxury p-4 mb-4">

        {{-- avatar + name --}}
        <div class="d-flex align-items-center gap-4 mb-4">
            <div class="rounded-circle gradient-bg-primary d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                 style="width:72px;height:72px;font-size:28px;">
                {{ strtoupper(substr($firstName ?: 'U', 0, 1)) }}
            </div>
            <div>
                <div style="font-size:1.2rem;font-weight:700;color:white;">
                    {{ $firstName }} {{ $lastName }}
                </div>
                <div style="font-size:13px;color:rgba(255,255,255,0.4);">
                    {{ $email }}
                </div>
                <div class="d-flex align-items-center gap-1 mt-1"
                     style="font-size:11px;color:#22c55e;">
                    <x-lucide-shield-check style="width:12px;height:12px;" />
                    Verified account
                </div>
            </div>
            <div class="ms-auto">
                <button wire:click="toggleEdit"
                        class="btn btn-outline-light btn-sm"
                        style="border-color:rgba(168,85,247,0.4);color:#a855f7;
                               font-size:13px;border-radius:8px;">
                    @if($isEditing)
                        Cancel
                    @else
                        <x-lucide-pencil style="width:14px;height:14px;display:inline;margin-right:4px;" />
                        Edit
                    @endif
                </button>
            </div>
        </div>

        {{-- divider --}}
        <div style="height:1px;background:rgba(255,255,255,0.06);margin-bottom:20px;"></div>

        @if($isEditing)
        {{-- edit form --}}
        <div class="row g-3">
            <div class="col-6">
                <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                               text-transform:uppercase;letter-spacing:0.3px;">
                    First Name
                </label>
                <input type="text" wire:model="firstName"
                       class="form-control mt-1"
                       style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                              color:white;padding:12px 14px;border-radius:10px;" />
                @error('firstName')
                <div style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-6">
                <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                               text-transform:uppercase;letter-spacing:0.3px;">
                    Last Name
                </label>
                <input type="text" wire:model="lastName"
                       class="form-control mt-1"
                       style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                              color:white;padding:12px 14px;border-radius:10px;" />
                @error('lastName')
                <div style="color:#ef4444;font-size:11px;margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                               text-transform:uppercase;letter-spacing:0.3px;">
                    Phone Number
                </label>
                <div class="input-group mt-1">
                    <span class="input-group-text"
                          style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                                 border-right:none;border-radius:10px 0 0 10px;">
                        <x-lucide-phone style="width:15px;height:15px;color:#a855f7;" />
                    </span>
                    <input type="tel" wire:model="phone"
                           class="form-control"
                           style="background:#141420;border:1px solid rgba(168,85,247,0.25);
                                  border-left:none;color:white;padding:12px 14px;
                                  border-radius:0 10px 10px 0;" />
                </div>
            </div>

            <div class="col-12">
                <label style="font-size:11px;font-weight:600;color:rgba(255,255,255,0.5);
                               text-transform:uppercase;letter-spacing:0.3px;">
                    Email Address
                </label>
                <input type="email" value="{{ $email }}" disabled
                       class="form-control mt-1"
                       style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);
                              color:rgba(255,255,255,0.3);padding:12px 14px;border-radius:10px;" />
                <div style="font-size:11px;color:rgba(255,255,255,0.3);margin-top:4px;">
                    Email cannot be changed
                </div>
            </div>

            <div class="col-12 mt-2">
                <button wire:click="updateProfile"
                        wire:loading.attr="disabled"
                        class="btn btn-gradient fw-semibold"
                        style="padding:12px 32px;border-radius:10px;">
                    <span wire:loading.remove wire:target="updateProfile">Save Changes</span>
                    <span wire:loading wire:target="updateProfile">
                        <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                    </span>
                </button>
            </div>
        </div>

        @else
        {{-- view mode --}}
        <div class="d-flex flex-column gap-3">
            @foreach([
                ['icon' => 'user', 'label' => 'First Name', 'value' => $firstName],
                ['icon' => 'user', 'label' => 'Last Name',  'value' => $lastName],
                ['icon' => 'mail', 'label' => 'Email',      'value' => $email],
                ['icon' => 'phone','label' => 'Phone',      'value' => $phone ?: 'Not set'],
            ] as $field)
            <div class="d-flex align-items-center justify-content-between p-3 rounded-xl"
                 style="background:rgba(255,255,255,0.03);">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:34px;height:34px;border-radius:8px;
                                background:rgba(168,85,247,0.1);
                                display:flex;align-items:center;justify-content:center;">
                        <x-dynamic-component :component="'lucide-' . $field['icon']"
                            style="width:15px;height:15px;color:#a855f7;" />
                    </div>
                    <div>
                        <div style="font-size:11px;color:rgba(255,255,255,0.4);">
                            {{ $field['label'] }}
                        </div>
                        <div style="font-size:14px;font-weight:600;color:white;">
                            {{ $field['value'] }}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- account stats card --}}
    <div class="card-luxury p-4">
        <h6 class="fw-bold mb-3" style="font-size:13px;text-transform:uppercase;
            letter-spacing:0.5px;color:rgba(255,255,255,0.5);">Account Info</h6>

        <div class="row g-3">
            @php $user = Auth::user(); @endphp
            <div class="col-6">
                <div style="background:rgba(168,85,247,0.06);border-radius:10px;padding:14px;">
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:4px;">
                        Member Since
                    </div>
                    <div style="font-size:14px;font-weight:700;color:white;">
                        {{ $user->created_at->format('M Y') }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div style="background:rgba(168,85,247,0.06);border-radius:10px;padding:14px;">
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:4px;">
                        KYC Status
                    </div>
                    <div style="font-size:14px;font-weight:700;
                                color:{{ $user->kyc_verified ? '#22c55e' : '#f59e0b' }};">
                        {{ $user->kyc_verified ? 'Verified' : 'Pending' }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div style="background:rgba(168,85,247,0.06);border-radius:10px;padding:14px;">
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:4px;">
                        Account Status
                    </div>
                    <div style="font-size:14px;font-weight:700;color:#22c55e;">
                        {{ ucfirst($user->status ?? 'Active') }}
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div style="background:rgba(168,85,247,0.06);border-radius:10px;padding:14px;">
                    <div style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:4px;">
                        Wallet Balance
                    </div>
                    <div style="font-size:14px;font-weight:700;color:#a855f7;">
                        ₦{{ number_format(($user->wallet->balance ?? 0) / 100, 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>