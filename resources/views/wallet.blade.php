@extends('layouts.app')

@section('content')
<div class="container-fluid py-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh;">
    <div class="row mb-4">
        <div class="col-lg-12">
            <h1 class="text-white mb-4">
                <i class="fas fa-wallet me-2"></i> My Wallet
            </h1>
        </div>
    </div>

    {{-- Wallet Balance Card --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #0f3460 0%, #16213e 100%); border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-2">Available Balance</p>
                            <h2 class="text-white">
                                @if($balanceVisible)
                                    <i class="fas fa-wallet me-2"></i> ₦{{ number_format($balance, 2) }}
                                @else
                                    <i class="fas fa-eye-slash me-2"></i> ••••••
                                @endif
                            </h2>
                            <p class="text-muted small">{{ $currency }}</p>
                        </div>
                        <button class="btn btn-sm btn-outline-light" wire:click="toggleBalance">
                            <i class="fas @if($balanceVisible) fa-eye @else fa-eye-slash @endif"></i>
                        </button>
                    </div>

                    <div class="row mt-4 g-2">
                        <div class="col-6">
                            <a href="{{ route('add-money') }}" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); border: none; border-radius: 10px;">
                                <i class="fas fa-plus me-2"></i> Add Money
                            </a>
                        </div>
                        <div class="col-6">
                            <button wire:click="refresh" class="btn btn-outline-light w-100" style="border-radius: 10px;">
                                <i class="fas fa-sync-alt me-2"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wallet Status Card --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.05); border-radius: 20px;">
                <div class="card-body p-4">
                    <h6 class="text-white mb-3">Wallet Information</h6>
                    
                    <div class="mb-3">
                        <p class="text-muted small mb-1">Status</p>
                        <span class="badge bg-success" style="font-size: 0.9rem;">{{ ucfirst($walletStatus) }}</span>
                    </div>

                    <div class="mb-3">
                        <p class="text-muted small mb-1">Currency</p>
                        <p class="text-white">{{ $currency }}</p>
                    </div>

                    <div>
                        <p class="text-muted small mb-1">Total Transactions</p>
                        <p class="text-white">{{ count($transactions) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Message Alerts --}}
    @if ($successMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
        <i class="fas fa-check-circle me-2"></i> {{ $successMessage }}
        <button type="button" class="btn-close" wire:click="$set('successMessage', '')"></button>
    </div>
    @endif

    @if ($errorMessage)
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
        <i class="fas fa-exclamation-circle me-2"></i> {{ $errorMessage }}
        <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
    </div>
    @endif

    {{-- Transaction History --}}
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.05); border-radius: 20px;">
                <div class="card-header border-0 pt-4 pb-3" style="background: transparent;">
                    <h6 class="card-title text-white mb-0">Transaction History</h6>
                </div>
                <div class="card-body pt-0">
                    @if($transactions && count($transactions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover" style="color: white;">
                            <thead style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                <tr>
                                    <th style="color: #aaa;">Date</th>
                                    <th style="color: #aaa;">Type</th>
                                    <th style="color: #aaa;">Description</th>
                                    <th style="color: #aaa;">Amount</th>
                                    <th style="color: #aaa;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td>{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: white;">
                                            {{ ucfirst(str_replace('_', ' ', $tx->transaction_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $tx->description }}</td>
                                    <td>
                                        <span class="@if($tx->amount > 0) text-success @else text-danger @endif">
                                            @if($tx->amount > 0) + @endif ₦{{ number_format(abs($tx->amount), 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($tx->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($tx->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @else
                                            <span class="badge bg-danger">{{ ucfirst($tx->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: rgba(255, 255, 255, 0.2);"></i>
                        <p class="text-muted mt-3">No transactions yet.</p>
                        <a href="{{ route('add-money') }}" class="btn btn-sm btn-primary" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); border: none;">
                            Start with a deposit
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('send-money') }}" class="text-decoration-none">
                <div class="card border-0 h-100" style="background: rgba(255, 255, 255, 0.05); border-radius: 15px; transition: all 0.3s ease;">
                    <div class="card-body text-center">
                        <div style="font-size: 2rem; color: #e94560;" class="mb-2">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h6 class="text-white">Send Money</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <a href="{{ route('bill-payment') }}" class="text-decoration-none">
                <div class="card border-0 h-100" style="background: rgba(255, 255, 255, 0.05); border-radius: 15px; transition: all 0.3s ease;">
                    <div class="card-body text-center">
                        <div style="font-size: 2rem; color: #2196F3;" class="mb-2">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h6 class="text-white">Pay Bills</h6>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: #e94560;">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>
</div>
@endsection
