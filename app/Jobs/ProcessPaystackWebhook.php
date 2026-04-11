<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\VirtualAccount;
use App\Notifications\DepositSuccessful;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaystackWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $data = $this->data;

        $accountNumber = $data['authorization']['receiver_bank_account_number'] ?? null;

        if (!$accountNumber) {
            return;
        }

        DB::transaction(function () use ($data, $accountNumber) {

            $virtual = VirtualAccount::where('account_number', $accountNumber)->first();

            if (!$virtual) return;

            $wallet = Wallet::where('user_id', $virtual->user_id)->first();

            $amount = $data['amount'] / 100;

            // Prevent duplicate
            if (Transaction::where('reference', $data['reference'])->exists()) {
                return;
            }

            // Credit wallet
            $wallet->increment('balance', $amount);

            // Save transaction
            Transaction::create([
                'user_id' => $virtual->user_id,
                'type' => 'credit',
                'amount' => $amount,
                'reference' => $data['reference'],
                'description' => 'Bank Transfer Deposit',
                'status' => 'success'
            ]);

            // Notify user
            $user = User::find($virtual->user_id);
            $user->notify(new DepositSuccessful($amount));
        });
    }
}