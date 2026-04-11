<div class="container-fluid py-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh;">
    {{-- Header --}}
    <div class="mb-4">
        <h1 class="text-3xl fw-bold mb-2">Admin Dashboard</h1>
        <p class="text-muted-custom">Overview of platform metrics and activity</p>
    </div>

    {{-- Stats Grid --}}
    <div class="row g-3 mb-4">
        {{-- Total Users Card --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury border">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-container" style="background: rgba(168, 85, 247, 0.1);">
                            <x-lucide-users class="w-6 h-6 text-primary-custom" />
                        </div>
                        <div class="d-flex align-items-center gap-1 small text-success">
                            <x-lucide-trending-up class="w-4 h-4" />
                            <span>+12%</span>
                        </div>
                    </div>
                    <div class="text-3xl fw-bold mb-1">{{ number_format($totalUsers) }}</div>
                    <div class="small text-muted-custom">Total Users</div>
                </div>
            </div>
        </div>

        {{-- Total Volume Card --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury border">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-container" style="background: rgba(192, 132, 252, 0.1);">
                            <x-lucide-dollar-sign class="w-6 h-6 text-accent-custom" />
                        </div>
                        <div class="d-flex align-items-center gap-1 small text-success">
                            <x-lucide-trending-up class="w-4 h-4" />
                            <span>+28%</span>
                        </div>
                    </div>
                    <div class="text-3xl fw-bold mb-1">₦{{ number_format($totalVolume, 0) }}</div>
                    <div class="small text-muted-custom">Total Volume</div>
                </div>
            </div>
        </div>

        {{-- Total Transactions Card --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury border">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-container" style="background: rgba(168, 85, 247, 0.1);">
                            <x-lucide-activity class="w-6 h-6 text-primary-custom" />
                        </div>
                        <div class="d-flex align-items-center gap-1 small text-success">
                            <x-lucide-trending-up class="w-4 h-4" />
                            <span>+8%</span>
                        </div>
                    </div>
                    <div class="text-3xl fw-bold mb-1">{{ number_format($totalTransactions) }}</div>
                    <div class="small text-muted-custom">Transactions</div>
                </d>
            </div>
        </div>

        {{-- Revenue Card --}}
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury border">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="icon-container" style="background: rgba(192, 132, 252, 0.1);">
                            <x-lucide-dollar-sign class="w-6 h-6 text-accent-custom" />
                        </div>
                        <div class="d-flex align-items-center gap-1 small text-success">
                            <x-lucide-trending-up class="w-4 h-4" />
                            <span>+15%</span>
                        </div>
                    </div>
                    <div class="text-3xl fw-bold mb-1">₦{{ number_format($totalRevenue, 0) }}K</div>
                    <div class="small text-muted-custom">Revenue</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-4 mb-4">
        {{-- Revenue Overview Chart --}}
        <div class="col-lg-8">
            <div class="card card-luxury border">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">Revenue Overview</h3>
                            <p class="small text-muted-custom mb-0">Last 6 months</p>
                        </div>
                        <select class="form-select bg-secondary-custom border-0 rounded-xl" style="max-width: 150px;">
                            <option>6 months</option>
                            <option>3 months</option>
                            <option>1 month</option>
                        </select>
                    </div>
                    <div style="height: 250px; background: rgba(168, 85, 247, 0.05); border-radius: 8px; padding: 20px;">
                        <small class="text-muted-custom">Chart area - Revenue data visualization</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- User Distribution --}}
        <div class="col-lg-4">
            <div class="card card-luxury border">
                <div class="card-body">
                    <h3 class="h5 fw-semibold mb-4">User Distribution</h3>
                    <div style="height: 250px; background: rgba(168, 85, 247, 0.05); border-radius: 8px; padding: 20px; display: flex; align-items: center; justify-content: center;">
                        <small class="text-muted-custom">Pie chart area</small>
                    </div>
                    <div class="mt-4 d-flex flex-column gap-3">
                        @foreach($userDistribution as $item)
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle" style="width: 12px; height: 12px; background-color: {{ $item['color'] }};"></div>
                                    <span class="small">{{ $item['name'] }}</span>
                                </div>
                                <span class="fw-semibold">{{ number_format($item['value']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Transaction Activity Chart --}}
    <div class="row g-4 mb-4">
        <div class="col">
            <div class="card card-luxury border">
                <div class="card-body">
                    <h3 class="h5 fw-semibold mb-4">Daily Transactions</h3>
                    <div style="height: 200px; background: rgba(168, 85, 247, 0.05); border-radius: 8px; padding: 20px; display: flex; align-items: flex-end; justify-content: space-around;">
                        @foreach($transactionData as $day)
                            <div style="text-align: center;">
                                <div style="height: {{ ($day['value'] / 500) * 150 }}px; width: 30px; background: #a855f7; border-radius: 4px 4px 0 0; margin-bottom: 8px;"></div>
                                <small class="text-muted-custom">{{ $day['name'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="row">
        <div class="col">
            <div class="card card-luxury border">
                <div class="card-body">
                    <h3 class="h5 fw-semibold mb-4">Recent Activity</h3>
                    <div class="d-flex flex-column gap-3">
                        @foreach($recentActivity as $activity)
                            <div class="d-flex align-items-center gap-3 p-3 rounded-xl" style="background: {{ $activity['type'] === 'alert' ? 'rgba(239, 68, 68, 0.1)' : ($activity['type'] === 'transaction' ? 'rgba(192, 132, 252, 0.1)' : 'rgba(168, 85, 247, 0.1)') }};">
                                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $activity['type'] === 'user' ? 'text-primary-custom' : ($activity['type'] === 'alert' ? 'text-danger' : ($activity['type'] === 'transaction' ? 'text-accent-custom' : 'text-primary-custom')) }}"
                                    style="width: 40px; height: 40px; background: {{ $activity['type'] === 'alert' ? 'rgba(239, 68, 68, 0.2)' : ($activity['type'] === 'transaction' ? 'rgba(192, 132, 252, 0.2)' : 'rgba(168, 85, 247, 0.2)') }};">
                                    @if($activity['type'] === 'user')
                                        <x-lucide-user-plus class="w-5 h-5" />
                                    @elseif($activity['type'] === 'transaction')
                                        <x-lucide-arrow-up-right class="w-5 h-5" />
                                    @elseif($activity['type'] === 'kyc')
                                        <x-lucide-activity class="w-5 h-5" />
                                    @else
                                        <x-lucide-alert-circle class="w-5 h-5" />
                                    @endif
                                </div>
                                <div class="grow">
                                    <div class="fw-medium">{{ $activity['user'] }}</div>
                                    <div class="small text-muted-custom">{{ $activity['action'] }}</div>
                                </div>
                                <div class="small text-muted-custom">{{ $activity['time'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
