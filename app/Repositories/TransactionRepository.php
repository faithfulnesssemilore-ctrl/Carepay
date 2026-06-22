<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Support\Carbon;

class TransactionRepository
{
    /**
     * Get all transactions for a user within a date range
     */
    public function forUserInDateRange(int $userId, Carbon $startDate, Carbon $endDate)
    {
        return Transaction::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get credit transactions for a user
     */
    public function creditsForUser(int $userId)
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'credit')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get debit transactions for a user
     */
    public function debitsForUser(int $userId)
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'debit')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get today's debit transactions for a user (for daily limit checks)
     */
    public function debitsForUserToday(int $userId)
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'debit')
            ->whereDate('created_at', today())
            ->sum('amount');
    }

    /**
     * Check if a transaction reference already exists (idempotency)
     */
    public function referenceExists(string $reference): bool
    {
        return Transaction::where('reference', $reference)->exists();
    }

    /**
     * Get transaction by reference
     */
    public function getByReference(string $reference)
    {
        return Transaction::where('reference', $reference)->first();
    }

    /**
     * Get recent transactions for a user (for dashboard)
     */
    public function recentForUser(int $userId, int $limit = 10)
    {
        return Transaction::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get transaction statistics for a user
     */
    public function statsForUser(int $userId, Carbon $startDate, Carbon $endDate)
    {
        $transactions = $this->forUserInDateRange($userId, $startDate, $endDate)->get();

        return [
            'total_credits' => $transactions->where('type', 'credit')->sum('amount'),
            'total_debits' => $transactions->where('type', 'debit')->sum('amount'),
            'transaction_count' => $transactions->count(),
            'credit_count' => $transactions->where('type', 'credit')->count(),
            'debit_count' => $transactions->where('type', 'debit')->count(),
        ];
    }
}
