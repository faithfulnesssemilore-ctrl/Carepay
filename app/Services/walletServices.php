<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\LedgerEntry;
use App\Models\UserLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class WalletService
{
    // Get a user's wallet (or throw an error if not found)
    public function getWallet($userId): Wallet
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            throw new Exception('Wallet not found for this user.');
        }

        return $wallet;
    }

    // Create a wallet for a new user during registration
    public function createForUser($userId, string $currency = 'NGN'): Wallet
    {
        // Prevent duplicate wallets
        if (Wallet::where('user_id', $userId)->exists()) {
            throw new Exception('Wallet already exists for this user.');
        }

        return Wallet::create([
            'user_id'  => $userId,
            'balance'  => 0,          // stored in kobo (100 kobo = ₦1)
            'currency' => $currency,
            'status'   => 'active',
        ]);
    }

    // Credit (add money to) a wallet
    // This is called ONLY after payment is verified
    public function credit($userId, int $amountInKobo, string $reference, string $description = 'Credit'): Transaction
    {
        // Make sure we are not crediting 0 or negative
        if ($amountInKobo <= 0) {
            throw new Exception('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($userId, $amountInKobo, $reference, $description) {

            // Check if this reference was already processed
            // This prevents double-crediting from duplicate webhooks
            if (Transaction::where('reference', $reference)->exists()) {
                throw new Exception('This transaction has already been processed.');
            }

            // Lock the wallet row so no other request can touch it right now
            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new Exception('Wallet is not active.');
            }

            // Add the money
            $wallet->increment('balance', $amountInKobo);

            // Record the transaction
            $transaction = Transaction::create([
                'user_id'     => $userId,
                'wallet_id'   => $wallet->id,
                'type'        => 'credit',
                'amount'      => $amountInKobo,
                'currency'    => $wallet->currency,
                'reference'   => $reference,
                'status'      => 'success',
                'description' => $description,
            ]);

            // Record in ledger for accounting
            LedgerEntry::create([
                'wallet_id'      => $wallet->id,
                'transaction_id' => $transaction->id,
                'entry_type'     => 'credit',
                'amount'         => $amountInKobo,
            ]);

            return $transaction;
        });
    }

    // Debit (remove money from) a wallet
    // Used for transfers and withdrawals
    public function debit($userId, int $amountInKobo, string $reference, string $description = 'Debit'): Transaction
    {
        if ($amountInKobo <= 0) {
            throw new Exception('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($userId, $amountInKobo, $reference, $description) {

            // Check for duplicate
            if (Transaction::where('reference', $reference)->exists()) {
                throw new Exception('This transaction has already been processed.');
            }

            // Lock the wallet row
            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new Exception('Wallet is not active.');
            }

            // Check balance AFTER locking (prevents race conditions)
            if ($wallet->balance < $amountInKobo) {
                throw new Exception('Insufficient balance.');
            }

            // Remove the money
            $wallet->decrement('balance', $amountInKobo);

            // Record the transaction
            $transaction = Transaction::create([
                'user_id'     => $userId,
                'wallet_id'   => $wallet->id,
                'type'        => 'debit',
                'amount'      => $amountInKobo,
                'currency'    => $wallet->currency,
                'reference'   => $reference,
                'status'      => 'success',
                'description' => $description,
            ]);

            // Record in ledger
            LedgerEntry::create([
                'wallet_id'      => $wallet->id,
                'transaction_id' => $transaction->id,
                'entry_type'     => 'debit',
                'amount'         => $amountInKobo,
            ]);

            return $transaction;
        });
    }

    
    // Transfer money from one user to another
    // Both debit and credit happen together atomically
    public function transfer($senderId, $recipientId, int $amountInKobo, string $description = ''): array
    {
        if ($senderId === $recipientId) {
            throw new Exception('You cannot send money to yourself.');
        }

        if ($amountInKobo < 100) { // minimum ₦1
            throw new Exception('Minimum transfer amount is ₦1.');
        }

        // Check transfer limits before processing
        $limits = UserLimit::where('user_id', $senderId)->first();
        
        if (!$limits) {
            // Create default limits if they don't exist
            $limits = UserLimit::create([
                'user_id' => $senderId,
                'single_transaction_limit' => 100000,  // NGN not kobo
                'daily_transfer_limit' => 500000,
                'limit_reset_date' => now()->toDateString(),
            ]);
        }

        // Check single transaction limit (convert to kobo for comparison)
        $singleLimitKobo = $limits->singleLimitInKobo();
        if ($amountInKobo > $singleLimitKobo) {
            throw new Exception('Transfer exceeds single transaction limit of ₦' . $limits->single_transaction_limit);
        }

        // Check daily transfer limit
        $todaySpent = Transaction::where('user_id', $senderId)
            ->where('type', 'debit')
            ->whereDate('created_at', now()->toDateDate())
            ->sum('amount');

        $dailyLimitKobo = $limits->dailyLimitInKobo();
        if (($todaySpent + $amountInKobo) > $dailyLimitKobo) {
            $remaining = $dailyLimitKobo - $todaySpent;
            throw new Exception('Daily transfer limit exceeded. Remaining: ₦' . round($remaining / 100, 2));
        }

        return DB::transaction(function () use ($senderId, $recipientId, $amountInKobo, $description) {

            // Generate one shared reference for this transfer
            $reference = 'TRF_' . strtoupper(Str::random(16));

            // Lock BOTH wallets — lock in consistent order to prevent deadlocks
            // Always lock lower ID first
            $ids = [$senderId, $recipientId];
            sort($ids);

            $wallets = Wallet::whereIn('user_id', $ids)
                ->lockForUpdate()
                ->orderBy('user_id')
                ->get()
                ->keyBy('user_id');

            $senderWallet    = $wallets[$senderId]    ?? null;
            $recipientWallet = $wallets[$recipientId] ?? null;

            if (!$senderWallet || !$recipientWallet) {
                throw new Exception('One or both wallets not found.');
            }

            if ($senderWallet->status !== 'active' || $recipientWallet->status !== 'active') {
                throw new Exception('One or both wallets are not active.');
            }

            if ($senderWallet->balance < $amountInKobo) {
                throw new Exception('Insufficient balance.');
            }

            // Debit sender
            $senderWallet->decrement('balance', $amountInKobo);

            $debitTx = Transaction::create([
                'user_id'     => $senderId,
                'wallet_id'   => $senderWallet->id,
                'type'        => 'debit',
                'amount'      => $amountInKobo,
                'currency'    => $senderWallet->currency,
                'reference'   => $reference,
                'status'      => 'success',
                'description' => $description ?: 'Transfer sent',
                'recipient_id' => $recipientId,
            ]);

            LedgerEntry::create([
                'wallet_id'      => $senderWallet->id,
                'transaction_id' => $debitTx->id,
                'entry_type'     => 'debit',
                'amount'         => $amountInKobo,
            ]);

            // Credit recipient
            $recipientWallet->increment('balance', $amountInKobo);

            $creditTx = Transaction::create([
                'user_id'     => $recipientId,
                'wallet_id'   => $recipientWallet->id,
                'type'        => 'credit',
                'amount'      => $amountInKobo,
                'currency'    => $recipientWallet->currency,
                'reference'   => $reference . '_IN',
                'status'      => 'success',
                'description' => $description ?: 'Transfer received',
                'recipient_id' => $senderId,
            ]);

            LedgerEntry::create([
                'wallet_id'      => $recipientWallet->id,
                'transaction_id' => $creditTx->id,
                'entry_type'     => 'credit',
                'amount'         => $amountInKobo,
            ]);

            return [
                'reference'    => $reference,
                'debit_tx'     => $debitTx,
                'credit_tx'    => $creditTx,
            ];
        });
    }

    // Get balance in Naira (human-readable)
    public function getBalanceInNaira($userId): float
    {
        $wallet = $this->getWallet($userId);
        return $wallet->balance / 100;
    }
}