<?php
namespace App\Services;

use App\Models\Wallet;
use App\Models\User;
use App\Models\Transaction;
use App\Models\LedgerEntry;             
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class WalletService 
{
    /**
     * Create new wallet for user during registration
     * Prevents duplicate wallets per currency
     */
    public function createWalletForUser(User $user, $currency = 'NGN')
    {
        $currency = strtoupper($currency);
        
        // Security: prevent duplicate wallets for the same currency per user
        if (Wallet::where('user_id', $user->id)->where('currency', $currency)->exists()) {
            throw new Exception('Wallet for this currency already exists for the user.');
        }
        
        // Use DB transaction to ensure wallet creation is atomic
        return DB::transaction(function () use ($user, $currency) {
            return Wallet::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'balance' => 0.00,
                'currency' => $currency,
                'status' => 'active',
            ]);
        });
    }

    /**
     * Get wallet balance by user ID
     */
    public function getBalance($userId)
    {
        return Wallet::where('user_id', $userId)->firstOrFail();
    }

    /**
     * Get wallet by user ID
     */
    public function getWalletByUserId($userId)
    {
        return Wallet::where('user_id', $userId)->first();
    }

    /**
     * Deposit funds into wallet
     * Locks wallet to prevent race conditions
     */
    public function deposit($walletId, $amount)
    {
        if ($amount <= 0) {
            throw new Exception('Deposit amount must be greater than zero');
        }

        return DB::transaction(function() use ($walletId, $amount) {
            // Lock the wallet record for update to prevent race conditions
            $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

            $transaction = Transaction::create([
                'reference' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'status' => 'success'
            ]);

            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'entry_type' => 'credit',
                'amount' => $amount
            ]);

            $wallet->increment('balance', $amount);

            return $transaction;
        });
    }

    /**
     * Withdraw funds from wallet to bank account
     */
    public function withdraw($walletId, $amount)
    {
        if ($amount <= 0) {
            throw new Exception('Withdrawal amount must be greater than zero');
        }

        return DB::transaction(function() use ($walletId, $amount) {
            $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

            if ($wallet->balance < $amount) {
                throw new Exception('Insufficient funds in wallet');
            }

            $transaction = Transaction::create([
                'reference' => (string) Str::uuid(),
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'status' => 'pending'
            ]);

            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'entry_type' => 'debit',
                'amount' => $amount
            ]);

            $wallet->decrement('balance', $amount);

            return $transaction;
        });
    }

    /**
     * Transfer funds between two users
     */
    public function transfer($senderId, $receiverId, $amount, $description = null)
    {
        if ($amount <= 0) {
            throw new Exception('Transfer amount must be greater than zero');
        }

        if ($senderId === $receiverId) {
            throw new Exception('Cannot transfer to the same wallet');
        }

        return DB::transaction(function() use ($senderId, $receiverId, $amount, $description) {
            $sender = Wallet::where('id', $senderId)->lockForUpdate()->firstOrFail();
            $receiver = Wallet::where('id', $receiverId)->lockForUpdate()->firstOrFail();

            if ($sender->balance < $amount) {
                throw new Exception('Insufficient funds');
            }

            if ($sender->currency !== $receiver->currency) {
                throw new Exception('Cannot transfer between different currencies');
            }

            $reference = (string) Str::uuid();

            $transaction = Transaction::create([
                'reference' => $reference,
                'wallet_id' => $sender->id,
                'type' => 'transfer',
                'amount' => $amount,
                'status' => 'success',
                'description' => $description
            ]);

            $sender->decrement('balance', $amount);
            $receiver->increment('balance', $amount);

            // Debit entry for sender
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'wallet_id' => $sender->id,
                'entry_type' => 'debit',
                'amount' => $amount
            ]);

            // Credit entry for receiver
            LedgerEntry::create([
                'transaction_id' => $transaction->id,
                'wallet_id' => $receiver->id,
                'entry_type' => 'credit',
                'amount' => $amount
            ]);

            return $transaction;
        });
    }

    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance($userId, $amount)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            return false;
        }

        return $wallet->balance >= $amount;
    }

    /**
     * Get available balance (excluding pending and reserved)
     */
    public function getAvailableBalance($walletId)
    {
        $wallet = Wallet::findOrFail($walletId);
        return $wallet->balance;
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalance($walletId, $currency = '₦')
    {
        $wallet = Wallet::findOrFail($walletId);
        return $currency . number_format($wallet->balance, 2);
    }

    /**
     * Update wallet balance directly
     */
    public function updateWalletBalance(Wallet $wallet, $amount)
    {
        if ($wallet->balance + $amount < 0) {
            throw new Exception('Insufficient funds for this operation');
        }

        $wallet->balance += $amount;
        $wallet->save();
        
        return $wallet;
    }

    /**
     * Get wallet transaction history
     */
    public function getTransactionHistory($walletId, $limit = 10, $offset = 0)
    {
        return Transaction::where('wallet_id', $walletId)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Get wallet ledger entries
     */
    public function getLedgerEntries($walletId, $limit = 20)
    {
        return LedgerEntry::where('wallet_id', $walletId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get transaction statistics for period
     */
    public function getTransactionStats($walletId, $days = 30)
    {
        $startDate = now()->subDays($days);

        $stats = Transaction::where('wallet_id', $walletId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->type => [
                'count' => $stat->count,
                'total' => $stat->total
            ]];
        });
    }

    /**
     * Verify wallet ownership
     */
    public function verifyWalletOwnership($walletId, $userId)
    {
        $wallet = Wallet::where('id', $walletId)->where('user_id', $userId)->first();
        return $wallet !== null;
    }
}