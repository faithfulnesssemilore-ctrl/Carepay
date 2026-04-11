<div class="d-flex flex-column gap-4">
    {{-- Header --}}
    <div>
        <h1 class="display-5 fw-bold mb-2">Transactions</h1>
        <p class="text-muted-custom">Monitor and manage all platform transactions</p>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury p-4">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="small text-muted-custom mb-1">Total Volume</div>
                        <div class="h4 fw-bold">₦{{ number_format($totalVolume, 0) }}</div>
                    </div>
                    <div class="icon-container icon-container-sm bg-primary-custom" style="background: rgba(168, 85, 247, 0.2) !important;">
                        <x-lucide-trending-up class="w-5 h-5 text-primary-custom" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury p-4">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="small text-muted-custom mb-1">Total Fees</div>
                        <div class="h4 fw-bold">₦{{ number_format($totalFees, 2) }}</div>
                    </div>
                    <div class="icon-container icon-container-sm" style="background: rgba(59, 130, 246, 0.2);">
                        <x-lucide-dollar-sign class="w-5 h-5 text-info" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card card-luxury p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-secondary-custom border-secondary-custom">
                        <x-lucide-search class="text-muted-custom w-5 h-5" />
                    </span>
                    <input 
                        type="text" 
                        class="form-control bg-secondary-custom border-secondary-custom text-light" 
                        placeholder="Search by transaction ID, names..."
                        wire:model.live="searchQuery"
                    />
                </div>
            </div>
            <div class="col-md-4">
                <select 
                    class="form-select bg-secondary-custom border-secondary-custom text-light"
                    wire:model.live="filterStatus"
                >
                    <option value="all">All Status</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card card-luxury overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead class="table-secondary-custom">
                    <tr>
                        <th class="px-4 py-3">Transaction ID</th>
                        <th class="px-4 py-3">From</th>
                        <th class="px-4 py-3">To</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filteredTransactions as $transaction)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="text-monospace small text-primary-custom">{{ $transaction['id'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    @if($transaction['type'] === 'transfer')
                                        <x-lucide-arrow-up-right class="w-4 h-4 text-success" />
                                    @else
                                        <x-lucide-arrow-down-left class="w-4 h-4 text-info" />
                                    @endif
                                    <span class="small">{{ $transaction['from'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 small">{{ $transaction['to'] }}</td>
                            <td class="px-4 py-3">
                                <div class="fw-semibold">₦{{ number_format($transaction['amount'], 2) }}</div>
                                @if($transaction['fee'] > 0)
                                    <div class="small text-muted-custom">Fee: ₦{{ number_format($transaction['fee'], 2) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 small">{{ $transaction['category'] }}</td>
                            <td class="px-4 py-3">
                                @if($transaction['status'] === 'completed')
                                    <span class="badge bg-success">
                                        <x-lucide-check-circle-2 class="w-3 h-3 d-inline me-1" />
                                        Completed
                                    </span>
                                @elseif($transaction['status'] === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <x-lucide-clock class="w-3 h-3 d-inline me-1" />
                                        Pending
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <x-lucide-x-circle class="w-3 h-3 d-inline me-1" />
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 small text-muted-custom">{{ $transaction['date'] }}</td>
                            <td class="px-4 py-3">
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-light"
                                    wire:click="viewDetails('{{ $transaction['id'] }}')"
                                >
                                    Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted-custom">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <x-lucide-inbox class="w-8 h-8 opacity-50" />
                                    <span>No transactions found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Details Modal --}}
    @if($showModal && $selectedTransaction)
        <div class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark-custom border-secondary-custom">
                    <div class="modal-header border-secondary-custom">
                        <h5 class="modal-title">Transaction Details</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="small text-muted-custom">Transaction ID</div>
                                <div class="fw-semibold text-monospace">{{ $selectedTransaction['id'] }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted-custom">Category</div>
                                <div class="fw-semibold">{{ $selectedTransaction['category'] }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted-custom">From</div>
                                <div class="fw-semibold">{{ $selectedTransaction['from'] }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted-custom">To</div>
                                <div class="fw-semibold">{{ $selectedTransaction['to'] }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted-custom">Amount</div>
                                <div class="h5 fw-bold text-success">₦{{ number_format($selectedTransaction['amount'], 2) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted-custom">Fee</div>
                                <div class="fw-semibold">₦{{ number_format($selectedTransaction['fee'], 2) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted-custom">Date & Time</div>
                                <div class="fw-semibold">{{ $selectedTransaction['date'] }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="small text-muted-custom">Status</div>
                                <div>
                                    @if($selectedTransaction['status'] === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($selectedTransaction['status'] === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary-custom">
                        <button type="button" class="btn btn-primary-custom" wire:click="downloadReport()">Download Receipt</button>
                        <button type="button" class="btn btn-outline-light" wire:click="closeModal()">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
