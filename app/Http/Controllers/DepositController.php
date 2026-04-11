<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\LedgerEntry;

class DepositController extends Controller
{
    /**
     * Initialize Paystack Payment
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'currency' => 'required|string'
        ]);

        $user = Auth::user();

        $reference = 'DEP_' . Str::uuid();

        $response = Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $user->email,
                'amount' => $request->amount, // already in kobo
                'reference' => $reference,
                'callback_url' => route('payment.callback'),
                'currency' => $request->currency,
                'metadata' => [
                    'user_id' => $user->id,
                    'type' => 'deposit'
                ]
            ]);

        $data = $response->json();

        if (!$data['status']) {
            return response()->json([
                'message' => 'Payment initialization failed'
            ], 500);
        }

        return response()->json([
            'payment_url' => $data['data']['authorization_url'],
            'reference' => $reference
        ]);
    }

    /**
     * Paystack Webhook (Credits wallet)
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();

        // Verify event
        if ($payload['event'] !== 'charge.success') {
            return response()->json(['status' => 'ignored']);
        }

        $data = $payload['data'];

        $reference = $data['reference'];
        $amount = $data['amount'] / 100;
        $userId = $data['metadata']['user_id'];

        DB::beginTransaction();

        try {

            $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            // Prevent duplicate credit
            $exists = Transaction::where('reference', $reference)->exists();
            if ($exists) {
                return response()->json(['status' => 'already processed']);
            }

            // Credit wallet
            $wallet->balance += $amount;
            $wallet->save();

            // Save transaction
            Transaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'amount' => $amount,
                'currency' => 'NGN',
                'transaction_type' => 'deposit',
                'status' => 'completed',
                'reference' => $reference,
                'description' => 'Wallet funding via Paystack'
            ]);

            // Ledger entry (accounting)
            LedgerEntry::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => 'Deposit via Paystack',
                'reference' => $reference
            ]);

            DB::commit();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}