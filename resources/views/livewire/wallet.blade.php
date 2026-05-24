
<div class="d-flex flex-column gap-3 gap-md-4">
    {{-- Header --}}
    <div>
        <h1 class="display-5 fw-bold mb-2">Wallet</h1>
        <p class="text-muted-custom fs-6">Manage your digital wallet and funds</p>
    </div>

    {{-- Balance Cards - Responsive grid --}}
    <div class="row g-2 g-md-3">
        @forelse($balanceData as $item)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card {{ $loop->first ? 'gradient-bg-primary text-white' : 'card-luxury' }} border-0 h-100 p-3 p-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    @if($loop->first)
                        <x-lucide-wallet class="w-5 h-5 opacity-75" />
                    @elseif($loop->index === 1)
                        <x-lucide-calendar class="w-5 h-5 text-muted-custom opacity-75" />
                    @else
                        <x-lucide-credit-card class="w-5 h-5 text-muted-custom opacity-75" />
                    @endif
                    <span class="small {{ $loop->first ? 'opacity-75' : '' }}">{{ $item['name'] }}</span>
                </div>
                <div class="display-6 fw-bold mb-2">₦{{ number_format($item['value'], 2) }}</div>
                <div class="small {{ $loop->first ? 'opacity-75' : 'text-muted-custom' }}">
                    @if($loop->first && $item['value'] > 0)
                        <x-lucide-trending-up class="w-3 h-3 d-inline me-1" />
                        Ready to use
                    @elseif($loop->index === 1)
                        <x-lucide-hourglass class="w-3 h-3 d-inline me-1" />
                        Processing
                    @else
                        <x-lucide-lock class="w-3 h-3 d-inline me-1" />
                        For upcoming
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-4">
            <p class="text-muted-custom">Wallet data loading...</p>
        </div>
        @endforelse
    </div>

    {{-- Action Buttons - 2x2 on mobile, 1x4 on larger --}}
    <div class="row g-2 g-md-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('add-money') }}" class="text-decoration-none d-block">
                <div class="card card-luxury p-3 p-md-4 text-center h-100 hover-lift">
                    <div class="icon-container icon-container-sm mx-auto mb-2" style="background: rgba(168, 85, 247, 0.2);">
                        <x-lucide-plus class="w-5 h-5 text-primary-custom" />
                    </div>
                    <h6 class="fw-medium small mb-1">Add Money</h6>
                    <p class="text-muted-custom small mb-0">Deposit funds</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('send-money') }}" class="text-decoration-none d-block">
                <div class="card card-luxury p-3 p-md-4 text-center h-100 hover-lift">
                    <div class="icon-container icon-container-sm mx-auto mb-2" style="background: rgba(34, 197, 94, 0.2);">
                        <x-lucide-send class="w-5 h-5 text-success" />
                    </div>
                    <h6 class="fw-medium small mb-1">Send Money</h6>
                    <p class="text-muted-custom small mb-0">Transfer funds</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('bill-payment') }}" class="text-decoration-none d-block">
                <div class="card card-luxury p-3 p-md-4 text-center h-100 hover-lift">
                    <div class="icon-container icon-container-sm mx-auto mb-2" style="background: rgba(59, 130, 246, 0.2);">
                        <x-lucide-receipt class="w-5 h-5 text-info" />
                    </div>
                    <h6 class="fw-medium small mb-1">Pay Bills</h6>
                    <p class="text-muted-custom small mb-0">Utilities & more</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="#" class="text-decoration-none d-block" wire:click="toggleBalance">
                <div class="card card-luxury p-3 p-md-4 text-center h-100 hover-lift">
                    <div class="icon-container icon-container-sm mx-auto mb-2" style="background: rgba(192, 132, 252, 0.2);">
                        @if($balanceVisible)
                            <x-lucide-eye class="w-5 h-5 text-accent-custom" />
                        @else
                            <x-lucide-eye-off class="w-5 h-5 text-accent-custom" />
                        @endif
                    </div>
                    <h6 class="fw-medium small mb-1">{{ $balanceVisible ? 'Hide' : 'Show' }}</h6>
                    <p class="text-muted-custom small mb-0">Balance</p>
                </div>
            </a>
        </div>
    </div>

    {{-- Balance Distribution & Scheduled Payments - Stack on mobile --}}
    <div class="row g-3 g-md-4">
        {{-- Balance Distribution Chart --}}
        <div class="col-12 col-lg-8">
            <div class="card card-luxury p-3 p-md-4 border">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Balance Breakdown</h5>
                    <small class="text-muted-custom">₦{{ number_format($balance + $pendingBalance + $reservedBalance, 2) }}</small>
                </div>
                
                <div class="row g-2">
                    @forelse($bphp as $item)
                    <div class="col-6 col-md-4">
                        <div class="text-center">
                            <div class="rounded-circle mx-auto mb-2" 
                                 style="width: 16px; height: 16px; background: {{ $item['color'] }};"></div>
                            <div class="small text-muted-custom mb-1">{{ $item['name'] }}</div>
                            <div class="fw-semibold">₦{{ number_format($item['value'], 0) }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4">
                        <p class="text-muted-custom small">No balance data</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Activity/Scheduled Payments --}}
        <div class="col-12 col-lg-4">
            <div class="card card-luxury p-3 p-md-4 border">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Recent Activity</h5>
                    <a href="{{ route('transactions') }}" class="btn btn-link text-primary-custom small p-0">View all</a>
                </div>

                <div class="d-flex flex-column gap-2">
                   @forelse(collect($transactions)->take(5) as $tx)
                    <div class="p-2 p-md-3 rounded" style="background: rgba(255, 255, 255, 0.03);">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center gap-2 flex-fill">
                                <div class="icon-container icon-container-sm" 
                                     style="background: {{ $tx->type === 'credit' ? 'rgba(34, 197, 94, 0.2)' : 'rgba(168, 85, 247, 0.2)' }};">
                                    @if($tx->type === 'credit')
                                        <x-lucide-arrow-down-left class="w-4 h-4 text-success" />
                                    @else
                                        <x-lucide-arrow-up-right class="w-4 h-4 text-primary-custom" />
                                    @endif
                                </div>
                                <div class="text-truncate">
                                    <div class="small fw-semibold">{{ ucfirst($tx->type) }}</div>
                                    <div class="text-muted-custom" style="font-size: 0.75rem;">{{ $tx->created_at->format('M d') }}</div>
                                </div>
                            </div>
                            <div class="fw-semibold text-nowrap" 
                                 style="color: {{ $tx->type === 'credit' ? '#10b981' : '#a855f7' }}">
                                {{ $tx->type === 'credit' ? '+' : '-' }}₦{{ number_format(abs($tx->amount), 0) }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <x-lucide-inbox class="w-8 h-8 text-muted-custom mx-auto mb-2" />
                        <p class="text-muted-custom small">No transactions yet</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Scheduled Payments Section --}}
    @if(count($scheduledPayments) > 0)
    <div class="card card-luxury p-3 p-md-4 border">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Scheduled Payments</h5>
            <span class="badge bg-warning text-dark">{{ count($scheduledPayments) }}</span>
        </div>

        <div class="row g-2">
            @foreach($scheduledPayments as $payment)
            <div class="col-12 col-md-6">
                <div class="p-3 rounded" style="background: rgba(255, 255, 255, 0.03); border-left: 3px solid #a855f7;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-medium small">{{ $payment->description ?? 'Scheduled Payment' }}</div>
                            <div class="text-muted-custom small">{{ $payment->scheduled_date->format('M d, Y') }}</div>
                        </div>
                        <span class="badge bg-primary">₦{{ number_format($payment->amount, 0) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>