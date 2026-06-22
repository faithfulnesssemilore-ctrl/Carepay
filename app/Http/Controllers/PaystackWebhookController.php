<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\VirtualAccount;
use App\Notifications\DepositSuccessful;
use App\Services\WalletService;
use App\TransactionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function handleEvent(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('x-paystack-signature');

        // verify the webhook is actually from Paystack
        if ($signature !== hash_hmac('sha512', $payload, config('services.paystack.secret'))) {
            Log::warning('Invalid Paystack webhook signature');

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'charge.success') {
            $this->handleChargeSuccess($data);
        }

        if ($event === 'transfer.success') {
            $this->handleTransferSuccess($data);
        }

        if ($event === 'transfer.failed') {
            $this->handleTransferFailed($data);
        }

        // always return 200 so Paystack stops retrying
        return response()->json(['status' => 'ok']);
    }

    private function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;
        $amountInKobo = (int) ($data['amount'] ?? 0);
        $accountNumber = $data['authorization']['receiver_bank_account_number'] ?? null;

        if (! $reference || $amountInKobo <= 0) {
            Log::warning('Paystack charge.success missing reference or amount', $data);

            return;
        }

        $walletService = new WalletService;

        // check if this is a card deposit (reference starts with DEP_)
        if (str_starts_with($reference, 'DEP_')) {
            $deposit = DB::table('deposits')->where('reference_id', $reference)->first();

            if (! $deposit) {
                Log::warning('Paystack webhook: deposit not found for reference', ['reference' => $reference]);

                return;
            }

            if ($deposit->status === 'completed') {
                // already processed - Paystack is retrying, ignore it
                return;
            }

            try {
                $walletService->credit(
                    userId: $deposit->user_id,
                    amountKobo: $amountInKobo,
                    reference: $reference,
                    description: 'Card deposit'
                );

                DB::table('deposits')
                    ->where('reference_id', $reference)
                    ->update(['status' => 'completed', 'updated_at' => now()]);

                $user = User::find($deposit->user_id);
                if ($user) {
                    $user->notify(new DepositSuccessful($amountInKobo / 100));
                }

            } catch (\Exception $e) {
                Log::error('Failed to credit wallet for card deposit', [
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);
            }

            return;
        }

        // virtual account deposit (bank transfer)
        if ($accountNumber) {
            $virtual = VirtualAccount::where('account_number', $accountNumber)->first();

            if (! $virtual) {
                Log::warning('Paystack webhook: virtual account not found', ['account_number' => $accountNumber]);

                return;
            }

            try {
                $walletService->credit(
                    userId: $virtual->user_id,
                    amountKobo: $amountInKobo,
                    reference: $reference,
                    description: 'Bank transfer deposit'
                );

                $user = User::find($virtual->user_id);
                if ($user) {
                    $user->notify(new DepositSuccessful($amountInKobo / 100));
                }

            } catch (\Exception $e) {
                Log::error('Failed to credit wallet for bank transfer', [
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function handleTransferSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;
        if (! $reference) {
            return;
        }

        // mark the transaction as completed
        Transaction::where('reference', $reference)
            ->update(['status' => TransactionStatus::Completed]);

        Log::info('Transfer successful', ['reference' => $reference]);
    }

    private function handleTransferFailed(array $data): void
    {
        $reference = $data['reference'] ?? null;
        if (! $reference) {
            return;
        }

        // mark the transaction as failed and refund the wallet
        $tx = Transaction::where('reference', $reference)->first();

        if ($tx && $tx->status !== TransactionStatus::Failed) {
            $tx->update(['status' => TransactionStatus::Failed]);

            // refund the sender
            try {
                $refundRef = 'REFUND_'.$reference;
                (new WalletService)->credit(
                    userId: $tx->user_id,
                    amountKobo: (int) round($tx->amount * 100),
                    reference: $refundRef,
                    description: 'Transfer failed - refund'
                );

                Log::info('Transfer refunded', ['reference' => $reference, 'user_id' => $tx->user_id]);
            } catch (\Exception $e) {
                Log::error('Failed to refund transfer', ['reference' => $reference, 'error' => $e->getMessage()]);
            }
        }
    }
}
