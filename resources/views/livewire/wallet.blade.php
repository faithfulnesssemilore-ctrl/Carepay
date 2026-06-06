
<div class="d-flex flex-column gap-3 gap-md-4">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div>
            <h1 class="display-6 fw-bold mb-1">Wallet</h1>
            <p class="text-muted-custom mb-0">Manage funds, scheduled payments, and quick actions in one place.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button wire:click="refresh" class="btn btn-outline-light btn-sm">
                <x-lucide-refresh-cw class="me-1" /> Refresh
            </button>
            <a href="{{ route('transactions') }}" class="btn btn-primary btn-sm">
                <x-lucide-list-check class="me-1" /> All Transactions
            </a>
        </div>
    </div>

    @if($errorMessage)
        <div class="alert alert-danger py-2">
            <strong>Wallet load failed:</strong> {{ $errorMessage }}
        </div>
    @endif

    @if($successMessage)
        <div class="alert alert-success py-2">
            {{ $successMessage }}
        </div>
    @endif

    {{-- Balance summary --}}
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card gradient-bg-primary text-white border-0 h-100 p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="small opacity-75">Available Wallet Balance</div>
                        <div class="display-6 fw-bold mt-2">
                            @if($balanceVisible)
                                ₦{{ number_format($balance, 2) }}
                            @else
                                ••••••••
                            @endif
                        </div>
                    </div>
                    <button wire:click="toggleBalance" class="btn btn-soft-light btn-sm">
                        @if($balanceVisible)
                            Hide
                        @else
                            Show
                        @endif
                    </button>
                </div>
                <div class="small opacity-75">Currency: {{ $currency }} · Status: {{ ucfirst($walletStatus) }}</div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="card card-luxury p-3 h-100 border-0 shadow-sm">
                        <div class="small text-muted-custom mb-2">Available</div>
                        <div class="h4 fw-bold">₦{{ number_format($balance, 2) }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card card-luxury p-3 h-100 border-0 shadow-sm">
                        <div class="small text-muted-custom mb-2">Pending</div>
                        <div class="h4 fw-bold">₦{{ number_format($pendingBalance, 2) }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card card-luxury p-3 h-100 border-0 shadow-sm">
                        <div class="small text-muted-custom mb-2">Reserved</div>
                        <div class="h4 fw-bold">₦{{ number_format($reservedBalance, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('add-money') }}" class="text-decoration-none d-block">
                <div class="card card-luxury p-3 h-100 text-center hover-lift border-0 shadow-sm">
                    <div class="icon-container icon-container-sm mx-auto mb-3" style="background: rgba(168, 85, 247, 0.15);">
                        <x-lucide-plus class="w-5 h-5 text-primary-custom" />
                    </div>
                    <div class="fw-semibold mb-1">Add Money</div>
                    <div class="text-muted-custom small">Deposit funds</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('send-money') }}" class="text-decoration-none d-block">
                <div class="card card-luxury p-3 h-100 text-center hover-lift border-0 shadow-sm">
                    <div class="icon-container icon-container-sm mx-auto mb-3" style="background: rgba(34, 197, 94, 0.15);">
                        <x-lucide-send class="w-5 h-5 text-success" />
                    </div>
                    <div class="fw-semibold mb-1">Send Money</div>
                    <div class="text-muted-custom small">Transfer funds</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('bill-payment') }}" class="text-decoration-none d-block">
                <div class="card card-luxury p-3 h-100 text-center hover-lift border-0 shadow-sm">
                    <div class="icon-container icon-container-sm mx-auto mb-3" style="background: rgba(59, 130, 246, 0.15);">
                        <x-lucide-receipt class="w-5 h-5 text-info" />
                    </div>
                    <div class="fw-semibold mb-1">Pay Bills</div>
                    <div class="text-muted-custom small">Utilities & more</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <button wire:click="toggleBalance" class="btn btn-link p-0 w-100 text-start text-decoration-none">
                <div class="card card-luxury p-3 h-100 text-center hover-lift border-0 shadow-sm">
                    <div class="icon-container icon-container-sm mx-auto mb-3" style="background: rgba(192, 132, 252, 0.15);">
                        @if($balanceVisible)
                            <x-lucide-eye class="w-5 h-5 text-accent-custom" />
                        @else
                            <x-lucide-eye-off class="w-5 h-5 text-accent-custom" />
                        @endif
                    </div>
                    <div class="fw-semibold mb-1">{{ $balanceVisible ? 'Hide' : 'Show' }}</div>
                    <div class="text-muted-custom small">Balance</div>
                </div>
            </button>
        </div>
    </div>

    {{-- Balance breakdown and recent activity --}}
    <div class="row g-3 g-md-4">
        <div class="col-12 col-xl-7">
            <div class="card card-luxury p-4 border-0 shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Balance Breakdown</h5>
                        <div class="small text-muted-custom">Total across active, pending and reserved funds.</div>
                    </div>
                    <div class="small text-muted-custom">Total: ₦{{ number_format($balance + $pendingBalance + $reservedBalance, 2) }}</div>
                </div>
                <div class="row g-3">
                    @forelse($bphp as $item)
                        <div class="col-12 col-sm-4">
                            <div class="rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: {{ $item['color'] }}33;"></span>
                                    <div class="small text-muted-custom mb-0">{{ $item['name'] }}</div>
                                </div>
                                <div class="h5 fw-bold">₦{{ number_format($item['value'], 2) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4">
                            <p class="text-muted-custom small">No balance breakdown available.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card card-luxury p-4 border-0 shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-1">Recent Activity</h5>
                        <div class="small text-muted-custom">Latest transactions at a glance.</div>
                    </div>
                    <a href="{{ route('transactions') }}" class="small text-primary-custom">View all</a>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse(collect($transactions)->take(5) as $tx)
                        <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.04);">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div class="d-flex align-items-center gap-3 flex-grow-1">
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: {{ $tx->type === 'credit' ? 'rgba(34, 197, 94, 0.15)' : 'rgba(168, 85, 247, 0.15)' }};">
                                        @if($tx->type === 'credit')
                                            <x-lucide-arrow-down-left class="w-4 h-4 text-success" />
                                        @else
                                            <x-lucide-arrow-up-right class="w-4 h-4 text-primary-custom" />
                                        @endif
                                    </span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">{{ ucfirst($tx->transaction_label) }}</div>
                                        <div class="small text-muted-custom">{{ $tx->created_at->format('d M, h:i A') }}</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-primary-custom' }}">
                                        {{ $tx->type === 'credit' ? '+' : '-' }}₦{{ number_format(abs($tx->amount_naira), 2) }}
                                    </div>
                                    <div class="small text-muted-custom">{{ ucfirst($tx->status) }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <x-lucide-inbox class="w-10 h-10 text-muted-custom mx-auto mb-2" />
                            <p class="text-muted-custom small mb-0">No recent activity yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Scheduled Payments Section --}}
    @if(count($scheduledPayments) > 0)
        <div class="card card-luxury p-4 border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1">Scheduled Payments</h5>
                    <div class="small text-muted-custom">Upcoming payments due soon.</div>
                </div>
                <span class="badge bg-warning text-dark">{{ count($scheduledPayments) }}</span>
            </div>

            <div class="row g-3">
                @foreach($scheduledPayments as $payment)
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.04); border-left: 4px solid #a855f7;">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $payment->description ?? 'Scheduled Payment' }}</div>
                                    <div class="small text-muted-custom">{{ $payment->scheduled_date->format('d M, Y') }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-semibold">₦{{ number_format($payment->amount_naira, 2) }}</div>
                                    <div class="small text-muted-custom">{{ ucfirst($payment->status) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>