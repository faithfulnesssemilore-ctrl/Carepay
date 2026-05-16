<div class="bg-dark-custom" style="min-height: 100vh;">
    {{-- Top Header --}}
    <div class="container py-3 py-md-4">
        <div class="row mb-4 mb-md-5 align-items-center">
            <div class="col-12 col-md-6 mb-3 mb-md-0">
                <h1 class="display-5 fw-bold mb-1">Welcome back, {{ Auth::user()->first_name ?? 'User' }}! 👋</h1>
                <p class="text-muted-custom fs-6">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <div class="col-12 col-md-6 text-start text-md-end d-flex justify-content-start justify-content-md-end align-items-center gap-2 gap-md-3">
                <button class="btn btn-outline-light btn-sm" wire:click="refresh" title="Refresh">
                    <x-lucide-refresh-cw class="w-4 h-4" />
                </button>
                <button class="btn btn-outline-light btn-sm position-relative" data-bs-toggle="offcanvas" data-bs-target="#notificationsOffcanvas" title="Notifications">
                    <x-lucide-bell class="w-4 h-4" />
                    @if($notificationCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ $notificationCount }}
                        </span>
                    @endif
                </button>
                <a href="{{ route('profile') }}" class="text-decoration-none">
                    <img src="https://ui-avatars.com/api/?name={{ Auth::user()->first_name }}&background=a855f7&color=fff" style="width: 35px; height: 35px; border-radius: 50%; border: 2px solid #a855f7;" alt="Profile">
                </a>
            </div>
        </div>
    </div>

    <div class="container">

        {{-- BALANCE CARD WITH BLUR EFFECTS --}}
        <div class="row mb-4 mb-md-5">
            <div class="col-12">
                <div class="card position-relative overflow-hidden gradient-bg-primary rounded-4 p-4 p-md-5 text-white border-0 shadow-primary-lg" style="min-height: 280px;">
                    {{-- Blur Circle Effects --}}
                    <div class="blur-circle-primary" style="top: -50px; right: -50px; position: absolute;"></div>
                    <div class="blur-circle-accent" style="bottom: -50px; left: -50px; position: absolute;"></div>
                    
                    <div class="position-relative">
                        {{-- Header with Toggle --}}
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <x-lucide-wallet class="w-6 h-6" />
                                <span class="small opacity-75">Total Balance</span>
                            </div>
                            <button
                                wire:click="toggleBalance"
                                class="btn btn-sm p-2 text-white"
                                style="background: rgba(255, 255, 255, 0.1); border-radius: 0.5rem; border: none;"
                            >
                                @if($balanceVisible)
                                    <x-lucide-eye class="w-5 h-5" />
                                @else
                                    <x-lucide-eye-off class="w-5 h-5" />
                                @endif
                            </button>
                        </div>

                        {{-- Balance Display --}}
                        <div class="mb-4">
                            <div class="display-3 fw-bold mb-2">
                                @if($balanceVisible)
                                    ₦{{ number_format($balance, 2) }}
                                @else
                                    ••••••
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                @if($incomePercentage >= 0)
                                    <x-lucide-trending-up class="w-4 h-4" />
                                    <span>+{{ number_format($incomePercentage, 1) }}% from last month</span>
                                @else
                                    <x-lucide-trending-down class="w-4 h-4" />
                                    <span>{{ number_format($incomePercentage, 1) }}% from last month</span>
                                @endif
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row g-3">
                            <div class="col-6">
                                <a href="{{ route('send-money') }}" class="btn w-100 py-3 rounded-xl text-white border-0 d-flex align-items-center justify-content-center gap-2" style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);">
                                    <x-lucide-send class="w-5 h-5" />
                                    <span>Send</span>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('add-money') }}" class="btn w-100 py-3 rounded-xl text-white border-0 d-flex align-items-center justify-content-center gap-2" style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px);">
                                    <x-lucide-plus class="w-5 h-5" />
                                    <span>Add Money</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="row g-3 mb-4 mb-md-5">
            {{-- Income Card --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-luxury border h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="icon-container" style="background: rgba(168, 85, 247, 0.1);">
                                <x-lucide-arrow-up-right class="w-6 h-6 text-primary-custom" />
                            </div>
                            <div class="d-flex align-items-center gap-1 small text-success">
                                <x-lucide-trending-up class="w-4 h-4" />
                                <span>@if($incomePercentage >= 0)+{{ number_format($incomePercentage, 0) }}%@else{{ number_format($incomePercentage, 0) }}%@endif</span>
                            </div>
                        </div>
                        <div class="h3 fw-bold mb-1">₦{{ number_format($monthlyIncome, 0) }}</div>
                        <div class="small text-muted-custom">Income this month</div>
                    </div>
                </div>
            </div>

            {{-- Expenses Card --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-luxury border h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="icon-container" style="background: rgba(239, 68, 68, 0.1);">
                                <x-lucide-arrow-down-left class="w-6 h-6" style="color: #ef4444;" />
                            </div>
                            <div class="d-flex align-items-center gap-1 small text-danger">
                                <x-lucide-trending-down class="w-4 h-4" />
                                <span>@if($expensePercentage >= 0)+{{ number_format($expensePercentage, 0) }}%@else{{ number_format($expensePercentage, 0) }}%@endif</span>
                            </div>
                        </div>
                        <div class="h3 fw-bold mb-1">₦{{ number_format($monthlyExpenses, 0) }}</div>
                        <div class="small text-muted-custom">Expenses this month</div>
                    </div>
                </div>
            </div>

            {{-- Transactions Card --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-luxury border h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="icon-container" style="background: rgba(192, 132, 252, 0.1);">
                                <x-lucide-send class="w-6 h-6 text-accent-custom" />
                            </div>
                        </div>
                        <div class="h3 fw-bold mb-1">{{ $transactionCount }}</div>
                        <div class="small text-muted-custom">Transactions</div>
                    </div>
                </div>
            </div>

            {{-- Bills Paid Card --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card card-luxury border h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="icon-container" style="background: rgba(168, 85, 247, 0.1);">
                                <x-lucide-receipt class="w-6 h-6 text-primary-custom" />
                            </div>
                        </div>
                        <div class="h3 fw-bold mb-1">₦{{ number_format($billsPaid, 0) }}</div>
                        <div class="small text-muted-custom">Bills paid</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT ROW --}}
        <div class="row g-4 mb-4 mb-md-5">
            {{-- Spending Overview Chart --}}
            <div style="position:relative;height:220px;">
    <canvas id="spendingChart"></canvas>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('spendingChart');
    if (!el) return;
    if (el._chart) el._chart.destroy();

    const data = @json($chartData);

    el._chart = new Chart(el.getContext('2d'), {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Income',
                    data: data.income || [],
                    borderColor: '#a855f7',
                    backgroundColor: 'rgba(168,85,247,0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#a855f7',
                    pointRadius: 3,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Expenses',
                    data: data.expenses || [],
                    borderColor: '#c084fc',
                    backgroundColor: 'rgba(192,132,252,0.05)',
                    borderWidth: 2,
                    pointBackgroundColor: '#c084fc',
                    pointRadius: 3,
                    fill: true,
                    tension: 0.4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#888', font: { size: 11 } } }
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#888' } },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: '#888',
                        callback: function(v) { return '₦' + v.toLocaleString(); }
                    }
                }
            }
        }
    });
});
</script>
@endpush

            {{-- Quick Actions Sidebar --}}
            <div class="col-lg-4">
                <div class="card card-luxury border">
                    <div class="card-body">
                        <h3 class="h5 fw-semibold mb-4">Quick Actions</h3>
                        <div class="d-flex flex-column gap-3">
                            {{-- Send Money --}}
                            <a href="{{ route('send-money') }}" class="d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none hover-lift" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2);">
                                <div class="icon-container icon-container-sm gradient-bg-primary flex-shrink-0">
                                    <x-lucide-send class="w-5 h-5 text-white" />
                                </div>
                                <div class="flex-fill">
                                    <div class="fw-medium text-white">Send Money</div>
                                    <div class="small text-muted-custom">Transfer to anyone</div>
                                </div>
                            </a>

                            {{-- Pay Bills --}}
                            <a href="{{ route('bill-payment') }}" class="d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none hover-lift" style="background: rgba(192, 132, 252, 0.1); border: 1px solid rgba(192, 132, 252, 0.2);">
                                <div class="icon-container icon-container-sm flex-shrink-0" style="background: #c084fc;">
                                    <x-lucide-receipt class="w-5 h-5 text-white" />
                                </div>
                                <div class="flex-fill">
                                    <div class="fw-medium text-white">Pay Bills</div>
                                    <div class="small text-muted-custom">Pay your bills</div>
                                </div>
                            </a>

                            {{-- Wallet --}}
                            <a href="{{ route('wallet') }}" class="d-flex align-items-center gap-3 p-3 rounded-xl text-decoration-none hover-lift bg-secondary-custom" style="border: 1px solid #2a2a3a;">
                                <div class="icon-container icon-container-sm gradient-bg-primary flex-shrink-0">
                                    <x-lucide-wallet class="w-5 h-5 text-white" />
                                </div>
                                <div class="flex-fill">
                                    <div class="fw-medium text-white">Wallet</div>
                                    <div class="small text-muted-custom">Manage funds</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- UPCOMING PAYMENTS & RECENT TRANSACTIONS --}}
        @if($upcomingPayments->count() > 0 || $recentTransactions->count() > 0)
        <div class="row mb-4 mb-md-5 g-4">
            @if($upcomingPayments->count() > 0)
            <div class="col-12 col-lg-6">
                <div class="card card-luxury border p-3 p-md-4">
                    <div class="card-header border-0 p-0 mb-4">
                        <h6 class="fw-bold mb-0">Upcoming Payments</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex flex-column gap-2 gap-md-3">
                            @foreach($upcomingPayments as $payment)
                            <div class="d-flex justify-content-between align-items-center p-2 p-md-3 rounded" style="background: rgba(255, 255, 255, 0.05);">
                                <div class="d-flex align-items-center gap-2 gap-md-3">
                                    <div class="icon-container icon-container-sm" style="background: rgba(249, 115, 22, 0.2);">
                                        <x-lucide-calendar class="w-4 h-4" style="color: #f97316;" />
                                    </div>
                                    <div class="text-truncate">
                                        <p class="small fw-semibold mb-1">{{ $payment->description ?? 'Scheduled Payment' }}</p>
                                        <p class="text-muted-custom" style="font-size: 0.8rem;">{{ $payment->scheduled_date->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark small">₦{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($recentTransactions->count() > 0)
            <div class="col-12 col-lg-6">
                <div class="card card-luxury border p-3 p-md-4">
                    <div class="card-header border-0 p-0 mb-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0">Recent Transactions</h6>
                        <a href="{{ route('transactions') }}" class="text-primary-custom small text-decoration-none">View all</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="d-flex flex-column gap-2 gap-md-3">
                            @foreach($recentTransactions->take(4) as $tx)
                            <div class="d-flex justify-content-between align-items-center p-2 p-md-3 rounded" style="background: rgba(255, 255, 255, 0.05);">
                                <div class="d-flex align-items-center gap-2 gap-md-3">
                                    <div class="icon-container icon-container-sm" style="background: @if($tx->transaction_type === 'in') rgba(34, 197, 94, 0.2) @else rgba(168, 85, 247, 0.2) @endif;">
                                        @if($tx->transaction_type === 'in')
                                            <x-lucide-arrow-down-left class="w-4 h-4 text-success" />
                                        @else
                                            <x-lucide-arrow-up-right class="w-4 h-4 text-primary-custom" />
                                        @endif
                                    </div>
                                    <div class="text-truncate">
                                        <p class="small fw-semibold mb-1">{{ ucfirst($tx->transaction_type === 'in' ? 'Received' : 'Sent') }}</p>
                                        <p class="text-muted-custom" style="font-size: 0.8rem;">{{ $tx->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <span class="fw-semibold @if($tx->amount > 0) text-success @else text-primary-custom @endif" style="font-size: 0.9rem;">
                                    @if($tx->amount > 0) + @endif ₦{{ number_format(abs($tx->amount), 0) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Notifications Offcanvas --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="notificationsOffcanvas" aria-labelledby="offcanvasLabel" style="background: #0f172a;">
        <div class="offcanvas-header border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
            <h5 class="offcanvas-title fw-bold" id="offcanvasLabel">Notifications</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            @forelse($upcomingPayments as $payment)
            <div class="p-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                <div class="d-flex gap-3">
                    <div class="icon-container icon-container-sm flex-shrink-0" style="background: rgba(249, 115, 22, 0.2);">
                        <x-lucide-calendar class="w-4 h-4" style="color: #f97316;" />
                    </div>
                    <div>
                        <p class="small fw-semibold mb-1">Upcoming Payment</p>
                        <p class="text-muted-custom small mb-0">{{ $payment->description ?? 'Payment' }} on {{ $payment->scheduled_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
            @empty
            @endforelse

            @forelse($recentTransactions->take(3) as $tx)
            <div class="p-3 border-bottom" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                <div class="d-flex gap-3">
                    <div class="icon-container icon-container-sm flex-shrink-0" style="background: @if($tx->transaction_type === 'in') rgba(34, 197, 94, 0.2) @else rgba(168, 85, 247, 0.2) @endif;">
                        @if($tx->transaction_type === 'in')
                            <x-lucide-arrow-down-left class="w-4 h-4 text-success" />
                        @else
                            <x-lucide-arrow-up-right class="w-4 h-4 text-primary-custom" />
                        @endif
                    </div>
                    <div>
                        <p class="small fw-semibold mb-1">{{ ucfirst($tx->transaction_type === 'in' ? 'Received' : 'Sent') }}</p>
                        <p class="text-muted-custom small mb-0">{{ $tx->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
            @empty
            @endforelse

            @if($upcomingPayments->count() === 0 && $recentTransactions->count() === 0)
            <div class="p-4 text-center">
                <p class="text-muted-custom small">No notifications</p>
            </div>
            @endif
        </div>
    </div>
</div>
