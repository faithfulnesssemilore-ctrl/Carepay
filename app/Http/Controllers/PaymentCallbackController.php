<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PaymentService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (! $reference) {
            return redirect()->route('add-money')
                ->with('error', 'Invalid payment reference.');
        }

        try {
            // verify the payment with Paystack
            $paymentService = new PaymentService;
            $data = $paymentService->verify($reference);

            if ($data['status'] !== 'success') {
                return redirect()->route('add-money')
                    ->with('error', 'Payment was not successful. Please try again.');
            }

            $amountKobo = (int) $data['amount'];
            $email = $data['customer']['email'];

            // find the user by email
            $user = User::where('email', $email)->first();

            if (! $user) {
                Log::error('Payment callback: user not found', ['email' => $email, 'reference' => $reference]);

                return redirect()->route('add-money')
                    ->with('error', 'Account not found. Contact support.');
            }

            // credit the wallet — idempotent so double callback is safe
            try {
                (new WalletService)->credit(
                    userId: $user->id,
                    amountKobo: $amountKobo,
                    reference: $reference,
                    description: 'Card deposit'
                );

                // mark deposit as completed
                DB::table('deposits')
                    ->where('reference_id', $reference)
                    ->update(['status' => 'completed', 'updated_at' => now()]);

            } catch (\Exception $e) {
                // if reference already processed that is fine — just redirect to success
                if (! str_contains($e->getMessage(), 'already been processed')) {
                    Log::error('Payment callback credit failed', [
                        'reference' => $reference,
                        'error' => $e->getMessage(),
                    ]);

                    return redirect()->route('add-money')
                        ->with('error', 'Payment received but wallet credit failed. Contact support with ref: '.$reference);
                }
            }

            return redirect()->route('wallet')
                ->with('success', '₦'.number_format($amountKobo / 100, 2).' has been added to your wallet.');

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('add-money')
                ->with('error', 'Could not verify payment. If money was deducted contact support with ref: '.$reference);
        }
    }
}
