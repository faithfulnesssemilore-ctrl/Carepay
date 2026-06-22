<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Transaction History</h4>
            <small class="text-muted">Recent activity and statements</small>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <div class="text-end me-3">
                <div class="small text-muted">Money In</div>
                <div class="h5 text-success mb-0">₦{{ number_format($totalIn, 2) }}</div>
            </div>
            <div class="text-end me-3">
                <div class="small text-muted">Money Out</div>
                <div class="h5 text-danger mb-0">₦{{ number_format($totalOut, 2) }}</div>
            </div>
            <button class="btn btn-primary btn-sm" wire:click="openExportModal">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>


    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($transactions as $tx)

                        <tr wire:click="selectTransaction('{{ $tx->id }}')" style="cursor:pointer">

                            <td>
                                {{ ucfirst($tx->transaction_label) }}
                            </td>

                            <td>
                                {{ $tx->reference }}
                            </td>

                            <td>
                                <span class="badge 
                                @if($tx->status == 'completed') bg-success
                                @elseif($tx->status == 'pending') bg-warning
                                @else bg-danger
                                @endif">
                                    {{ $tx->status }}
                                </span>
                            </td>

                            <td>
                                {{ $tx->created_at->format('d M Y, h:i A') }}
                            </td>

                            <td class="text-end">

                                @php
                                    $isCredit = $tx->type === 'credit';
                                @endphp

                                <strong class="{{ $isCredit ? 'text-success':'text-danger' }}">
                                    {{ $isCredit ? '+' : '-' }}
                                    ₦{{ number_format($tx->amount_naira,2) }}
                                </strong>

                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No transactions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    </div>


    <!-- MODAL -->
    @if($selectedTransaction)
    <div class="modal fade show d-block">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Transaction Details</h5>
                    <button class="btn-close" wire:click="clearSelection"></button>
                </div>

                <div class="modal-body">

                    <p><strong>Reference:</strong> {{ $selectedTransaction->reference }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($selectedTransaction->transaction_label) }}</p>
                    <p><strong>Status:</strong> {{ $selectedTransaction->status }}</p>
                    <p><strong>Amount:</strong> ₦{{ number_format($selectedTransaction->amount_naira,2) }}</p>
                    <p><strong>Date:</strong> {{ $selectedTransaction->created_at }}</p>
                    <p><strong>Description:</strong> {{ $selectedTransaction->description }}</p>

                    @if($selectedTransaction->type === 'debit' || $selectedTransaction->type === 'credit')
                        <hr>
                        <a href="{{ route('transaction.receipt.download', $selectedTransaction->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-file-pdf"></i> Download Receipt
                        </a>
                    @endif

                </div>

            </div>
        </div>
    </div>
    @endif

    <!-- EXPORT MODAL -->
    @if($showExportModal)
    <div class="modal fade show d-block">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>Export Statement of Account</h5>
                    <button class="btn-close" wire:click="closeExportModal" {{ $isExporting ? 'disabled' : '' }}></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input 
                            type="date" 
                            class="form-control"
                            wire:model="exportStartDate"
                            {{ $isExporting ? 'disabled' : '' }}
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input 
                            type="date" 
                            class="form-control"
                            wire:model="exportEndDate"
                            {{ $isExporting ? 'disabled' : '' }}
                        >
                    </div>

                    @if($exportMessage)
                    <div class="alert alert-info mb-3">
                        {{ $exportMessage }}
                    </div>
                    @endif

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" wire:click="closeExportModal" {{ $isExporting ? 'disabled' : '' }}>
                        Cancel
                    </button>
                    <button class="btn btn-primary" wire:click="requestStatementExport" {{ $isExporting ? 'disabled' : '' }}>
                        @if($isExporting)
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Processing...
                        @else
                        Export Statement
                        @endif
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>