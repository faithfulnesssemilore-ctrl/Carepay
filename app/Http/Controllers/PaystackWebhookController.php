<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VirtualAccount;
use App\Models\Wallet;
            use App\Jobs\ProcessPaystackWebhook;


use App\Models\Transaction;
use App\Models\User;
use App\Notifications\DepositSuccessful;

class PaystackWebhookController extends Controller
{
    public function handleEvent(Request $request)
    {
        // Verify Paystack signature
        $signature = $request->header('x-paystack-signature');

        if ($signature !== hash_hmac('sha512', $request->getContent(), config('services.paystack.secret'))) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');

        if ($event === 'charge.success') {

            $data = $request->input('data');

            $accountNumber = $data['authorization']['receiver_bank_account_number'] ?? null;

        ProcessPaystackWebhook::dispatch(
            $request->input('data')
        );
   

            if (!$accountNumber) {
                return response()->json(['message' => 'No account number'], 200);
            }

            DB::transaction(function () use ($data, $accountNumber) {

                $virtual = VirtualAccount::where('account_number', $accountNumber)->first();

                if (!$virtual) return;

                $wallet = Wallet::where('user_id', $virtual->user_id)->first();

                $amount = $data['amount'] / 100;

                // Prevent duplicate credit
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

                // Send notification
                $user = User::find($virtual->user_id);
                $user->notify(new DepositSuccessful($amount));
            });
        }

        return response()->json(['status' => 'queued']);
    }
}