<div class="container py-4">

    <h4 class="mb-4 fw-bold">Transaction History</h4>

    <!-- STATS -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <small>Total Transactions</small>
                    <h4>{{ $totalTransactions }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <small>Money In</small>
                    <h4 class="text-success">
                        ₦{{ number_format($totalIn,2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <small>Money Out</small>
                    <h4 class="text-danger">
                        ₦{{ number_format($totalOut,2) }}
                    </h4>
                </div>
            </div>
        </div>
    </div>


    <!-- FILTER -->
    <div class="card mb-3">
        <div class="card-body d-flex gap-2">
            <input 
                type="text" 
                class="form-control"
                placeholder="Search..."
                wire:model.live="searchQuery"
            >

            <select wire:model.live="filterStatus" class="form-control w-auto">
                <option value="all">All</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>


    <!-- TABLE -->
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">

                <thead>
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
                                {{ ucfirst($tx->transaction_type) }}
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
                                    $isCredit = in_array($tx->transaction_type, ['deposit','received']);
                                @endphp

                                <strong class="{{ $isCredit ? 'text-success':'text-danger' }}">
                                    {{ $isCredit ? '+' : '-' }}
                                    ₦{{ number_format($tx->amount,2) }}
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
                    <p><strong>Type:</strong> {{ $selectedTransaction->transaction_type }}</p>
                    <p><strong>Status:</strong> {{ $selectedTransaction->status }}</p>
                    <p><strong>Amount:</strong> ₦{{ number_format($selectedTransaction->amount,2) }}</p>
                    <p><strong>Date:</strong> {{ $selectedTransaction->created_at }}</p>
                    <p><strong>Description:</strong> {{ $selectedTransaction->description }}</p>

                </div>

            </div>
        </div>
    </div>
    @endif

</div>