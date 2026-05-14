<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService
{
    public function transfer($sender, $recipientPhone, $amount, $description = null)
    {
        return DB::transaction(function () use ($sender, $recipientPhone, $amount, $description) {

            $recipient = User::where('phone', $recipientPhone)->first();

            if (!$recipient) {
                throw new \Exception('Recipient not found');
            }

            if ($sender->id === $recipient->id) {
                throw new \Exception('Cannot transfer to yourself');
            }

            $senderWallet = $sender->wallet;
            $recipientWallet = $recipient->wallet;

            if (!$senderWallet || !$recipientWallet) {
                throw new \Exception('Wallet not found');
            }

            if ($senderWallet->balance < $amount) {
                throw new \Exception('Insufficient balance');
            }

            $reference = 'TRF_' . Str::uuid();

            // Debit sender
            $senderWallet->decrement('balance', $amount);

            Transaction::create([
                'wallet_id' => $senderWallet->id,
                'user_id' => $sender->id,
                'amount' => $amount,
                'currency' => 'NGN',
                'type' => 'transfer',
                'status' => 'completed',
                'reference' => $reference,
                'description' => $description ?? "Transfer to {$recipient->phone}"
            ]);

            // Credit receiver
            $recipientWallet->increment('balance', $amount);

            Transaction::create([
                'wallet_id' => $recipientWallet->id,
                'user_id' => $recipient->id,
                'amount' => $amount,
                'currency' => 'NGN',
                'type' => 'transfer',
                'status' => 'completed',
                'reference' => $reference,
                'description' => "Received from {$sender->phone}"
            ]);

            return [
                'success' => true,
                'reference' => $reference
            ];
        });
    }
}