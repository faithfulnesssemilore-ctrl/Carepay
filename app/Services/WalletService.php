<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Transaction;
use App\Models\UserLimit;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    public function getWallet(int $userId): Wallet
    {
        return Wallet::where('user_id', $userId)->firstOrFail();
    }

    public function createForUser(int $userId, string $currency = 'NGN'): Wallet
    {
        if (Wallet::where('user_id', $userId)->exists()) {
            throw new Exception('Wallet already exists for this user.');
        }

        return Wallet::create([
            'user_id' => $userId,
            'balance' => 0,
            'currency' => $currency,
            'status' => 'active',
        ]);
    }

    public function credit(int $userId, int $amountKobo, string $reference, string $description = 'Credit'): Transaction
    {
        if ($amountKobo <= 0) {
            throw new Exception('Credit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($userId, $amountKobo, $reference, $description) {
            if (Transaction::where('reference', $reference)->exists()) {
                throw new Exception('This transaction has already been processed.');
            }

            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new Exception('Wallet is not active.');
            }

            $wallet->increment('balance', $amountKobo);

            $transaction = Transaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amountKobo,
                'currency' => $wallet->currency,
                'reference' => $reference,
                'status' => 'success',
                'description' => $description,
            ]);

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'entry_type' => 'credit',
                'amount' => $amountKobo,
            ]);

            return $transaction;
        });
    }

    public function debit(int $userId, int $amountKobo, string $reference, string $description = 'Debit'): Transaction
    {
        if ($amountKobo <= 0) {
            throw new Exception('Debit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($userId, $amountKobo, $reference, $description) {
            if (Transaction::where('reference', $reference)->exists()) {
                throw new Exception('This transaction has already been processed.');
            }

            $wallet = Wallet::where('user_id', $userId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->status !== 'active') {
                throw new Exception('Wallet is not active.');
            }

            if ($wallet->balance < $amountKobo) {
                throw new Exception('Insufficient balance.');
            }

            $wallet->decrement('balance', $amountKobo);

            $transaction = Transaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'amount' => $amountKobo,
                'currency' => $wallet->currency,
                'reference' => $reference,
                'status' => 'success',
                'description' => $description,
            ]);

            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'entry_type' => 'debit',
                'amount' => $amountKobo,
            ]);

            return $transaction;
        });
    }

    public function transfer(int $senderId, int $recipientId, int $amountKobo, string $description = ''): array
    {
        if ($senderId === $recipientId) {
            throw new Exception('You cannot send money to yourself.');
        }

        if ($amountKobo < 100) {
            throw new Exception('Minimum transfer amount is ₦1.');
        }

        $limits = UserLimit::where('user_id', $senderId)->first();

        if (! $limits) {
            $limits = UserLimit::create([
                'user_id' => $senderId,
                'single_transaction_limit' => 100000,
                'daily_transfer_limit' => 500000,
                'daily_transfer_used' => 0,
                'limit_reset_date' => now()->toDateString(),
            ]);
        }

        if ($amountKobo > $limits->singleLimitInKobo()) {
            throw new Exception('Transfer exceeds single transaction limit of ₦'.$limits->single_transaction_limit);
        }

        $todaySpent = Transaction::where('user_id', $senderId)
            ->where('type', 'debit')
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        $dailyLimitKobo = $limits->dailyLimitInKobo();

        if (($todaySpent + $amountKobo) > $dailyLimitKobo) {
            $remaining = $dailyLimitKobo - $todaySpent;

            throw new Exception('Daily transfer limit exceeded. Remaining: ₦'.round($remaining / 100, 2));
        }

        return DB::transaction(function () use ($senderId, $recipientId, $amountKobo, $description) {
            $reference = 'TRF_'.strtoupper(Str::random(16));

            $ids = [$senderId, $recipientId];
            sort($ids);

            $wallets = Wallet::whereIn('user_id', $ids)
                ->lockForUpdate()
                ->orderBy('user_id')
                ->get()
                ->keyBy('user_id');

            $senderWallet = $wallets[$senderId] ?? null;
            $recipientWallet = $wallets[$recipientId] ?? null;

            if (! $senderWallet || ! $recipientWallet) {
                throw new Exception('One or both wallets not found.');
            }

            if ($senderWallet->status !== 'active' || $recipientWallet->status !== 'active') {
                throw new Exception('One or both wallets are not active.');
            }

            if ($senderWallet->balance < $amountKobo) {
                throw new Exception('Insufficient balance.');
            }

            $senderWallet->decrement('balance', $amountKobo);

            $debitTx = Transaction::create([
                'user_id' => $senderId,
                'wallet_id' => $senderWallet->id,
                'type' => 'debit',
                'amount' => $amountKobo,
                'currency' => $senderWallet->currency,
                'reference' => $reference,
                'status' => 'success',
                'description' => $description ?: 'Transfer sent',
                'recipient_id' => $recipientId,
            ]);

            LedgerEntry::create([
                'wallet_id' => $senderWallet->id,
                'transaction_id' => $debitTx->id,
                'entry_type' => 'debit',
                'amount' => $amountKobo,
            ]);

            $recipientWallet->increment('balance', $amountKobo);

            $creditTx = Transaction::create([
                'user_id' => $recipientId,
                'wallet_id' => $recipientWallet->id,
                'type' => 'credit',
                'amount' => $amountKobo,
                'currency' => $recipientWallet->currency,
                'reference' => $reference.'_IN',
                'status' => 'success',
                'description' => $description ?: 'Transfer received',
                'recipient_id' => $senderId,
            ]);

            LedgerEntry::create([
                'wallet_id' => $recipientWallet->id,
                'transaction_id' => $creditTx->id,
                'entry_type' => 'credit',
                'amount' => $amountKobo,
            ]);

            return [
                'reference' => $reference,
                'debit_tx' => $debitTx,
                'credit_tx' => $creditTx,
            ];
        });
    }

    public function getBalanceInNaira(int $userId): float
    {
        $wallet = $this->getWallet($userId);

        return $wallet->balance / 100;
    }
}