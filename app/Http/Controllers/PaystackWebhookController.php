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
use App\Services\WalletService;

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

            // Use WalletService for idempotency - same reference = single credit
            $virtual = VirtualAccount::where('account_number', $accountNumber)->first();

            if ($virtual) {
                $amountInKobo = (int) $data['amount'];
                $reference = $data['reference'];
                
                try {
                    (new WalletService())->credit(
                        $virtual->user_id,
                        $amountInKobo,
                        $reference,
                        'Paystack Deposit'
                    );
                    
                    $user = User::find($virtual->user_id);
                    $user->notify(new DepositSuccessful($amountInKobo / 100));
                } catch (\Exception $e) {
                    // Log error but return 200 so Paystack doesn't retry
                }
            }
        }

        return response()->json(['status' => 'queued']);
    }
}