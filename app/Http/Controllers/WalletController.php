<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\AuditLog;
use App\Models\Transaction;
use App\TransactionStatus;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// wallet controller - handles all money movement
// transfers, deposits, withdrawals all go through here
// this controller checks that users own their wallets before doing anything

class WalletController extends Controller
{
    protected $pinService;

    public function __construct(PinService $pinService)
    {
        $this->pinService = $pinService;
        $this->middleware('auth:sanctum');
    }

    // get wallet balance for logged in user
    public function getBalance()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        // make sure wallet exists
        if (! $wallet) {
            return response()->json(['error' => 'wallet not found'], 404);
        }

        // verify they own this wallet (extra security check)
        if ($wallet->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        return response()->json([
            'wallet' => [
                'id' => $wallet->id,
                'balance' => round($wallet->balance, 2),
                'currency' => $wallet->currency,
                'user_id' => $wallet->user_id,
            ],
        ]);
    }

    // get transaction history for user
    public function getTransactions(Request $request)
    {
        $user = Auth::user();

        // only get their transactions (cant see other users)
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($transactions);
    }

    // transfer money to another user
    public function transfer(TransferRequest $request)
    {
        $user = Auth::user();

        // check email is verified (security requirement)
        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'error' => 'verify your email first before transferring',
            ], 403);
        }

        // verify they own their wallet
        $wallet = $user->wallet;
        if ($wallet->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        // verify pin
        try {
            $this->pinService->verifyPin($request->pin, $user->id);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 401);
        }

        // check daily limits
        if (! $user->limits->canTransferAmount($request->amount)) {
            return response()->json([
                'error' => 'exceeds your daily limit',
                'remaining' => $user->limits->getRemainingDailyTransfer(),
            ], 422);
        }

        // check balance
        if ($wallet->balance < $request->amount) {
            // record failed attempt for audit/tracking
            $failed = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'category' => 'transfer',
                'status' => TransactionStatus::Failed,
                'reference' => 'TRF_FAIL-'.time().'-'.$user->id,
                'description' => 'Failed transfer - insufficient balance',
            ]);

            AuditLog::record(
                $user->id,
                'transfer_failed_insufficient_funds',
                'Transaction',
                $failed->id,
                ['amount' => $request->amount]
            );

            return response()->json([
                'error' => 'not enough money',
                'balance' => round($wallet->balance, 2),
            ], 422);
        }

        $recipient = User::findOrFail($request->recipient_id);

        // use database transaction to make sure both sides succeed or both fail
        DB::transaction(function () use ($user, $wallet, $recipient, $request) {
            // reduce sender balance
            $wallet->decrement('balance', $request->amount);

            // increase recipient balance
            $recipient->wallet->increment('balance', $request->amount);

            // create sender transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'category' => 'transfer',
                'status' => TransactionStatus::Completed,
                'description' => $request->description ?? 'transfer sent',
                'reference' => 'TRF-'.time().'-'.$user->id,
                'recipient_id' => $recipient->id,
            ]);

            // create recipient transaction record
            Transaction::create([
                'user_id' => $recipient->id,
                'wallet_id' => $recipient->wallet->id,
                'amount' => $request->amount,
                'type' => 'credit',
                'category' => 'transfer',
                'status' => TransactionStatus::Completed,
                'description' => $request->description ?? 'transfer received',
                'reference' => $transaction->reference.'_IN',
                'recipient_id' => $user->id,
            ]);

            // update daily limit tracking
            $user->limits->addToDaily($request->amount);

            // log this for audit trail (security/compliance)
            AuditLog::record(
                userId: $user->id,
                action: 'transfer_sent',
                entityType: 'Transaction',
                entityId: $transaction->id,
                changes: [
                    'from' => $user->id,
                    'to' => $recipient->id,
                    'amount' => $request->amount,
                ]
            );

            AuditLog::record(
                userId: $recipient->id,
                action: 'transfer_received',
                entityType: 'Transaction',
                entityId: $transaction->id,
                changes: [
                    'from' => $user->id,
                    'to' => $recipient->id,
                    'amount' => $request->amount,
                ]
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'transferred successfully',
            'balance' => round($wallet->balance, 2),
        ]);
    }

    // start deposit from paystack
    public function initiateDeposit(DepositRequest $request)
    {
        $user = Auth::user();

        // make sure email is verified
        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'error' => 'verify your email first',
            ], 403);
        }

        // convert to kobo (paystack uses kobo internally)
        $amountInKobo = $request->amount * 100;

        // create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'amount' => $amountInKobo,
            'type' => 'credit',
            'category' => 'deposit',
            'status' => TransactionStatus::Pending,
            'reference' => 'DEP-'.time().'-'.$user->id,
        ]);

        // log the attempt
        AuditLog::record(
            userId: $user->id,
            action: 'deposit_initiated',
            entityType: 'Transaction',
            entityId: $transaction->id,
            changes: ['amount' => $request->amount]
        );

        // return info to start paystack checkout
        return response()->json([
            'success' => true,
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference,
            'amount' => $amountInKobo,
            'email' => $user->email,
            'public_key' => config('services.paystack.public_key'),
            'callback_url' => route('payment.callback'),
        ]);
    }

    // withdraw money to bank account
    public function withdraw(WithdrawRequest $request)
    {
        $user = Auth::user();

        // check email verified
        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'error' => 'verify your email first',
            ], 403);
        }

        // get their wallet
        $wallet = $user->wallet;
        if ($wallet->user_id !== $user->id) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        // verify pin
        try {
            $this->pinService->verifyPin($request->pin, $user->id);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 401);
        }

        // check balance
        if ($wallet->balance < $request->amount) {
            return response()->json([
                'error' => 'not enough money',
                'balance' => round($wallet->balance, 2),
            ], 422);
        }

        // get bank account (verify they own it)
        $bankAccount = $user->bankAccounts()->findOrFail($request->bank_account_id);

        // use transaction for atomicity
        DB::transaction(function () use ($user, $wallet, $bankAccount, $request) {
            // reduce balance
            $wallet->decrement('balance', $request->amount);

            // create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'debit',
                'category' => 'withdrawal',
                'status' => 'processing', // bank transfer takes time
                'reference' => 'WID-'.time().'-'.$user->id,
                'bank_account_id' => $bankAccount->id,
            ]);

            // log for audit
            AuditLog::record(
                userId: $user->id,
                action: 'withdrawal_initiated',
                entityType: 'Transaction',
                entityId: $transaction->id,
                changes: [
                    'amount' => $request->amount,
                    'bank' => $bankAccount->bank_name,
                    'account' => $bankAccount->account_number,
                ]
            );

            // TODO: trigger actual bank transfer here
            // for now just mark as pending
        });

        return response()->json([
            'success' => true,
            'message' => 'withdrawal initiated. will process in 24 hours',
            'balance' => round($wallet->balance, 2),
        ]);
    }

    // test route to add money (for testing only - remove in production)
    public function testAddMoney(Request $request)
    {
        if (app()->environment('production')) {
            abort(404);
        }

        $user = Auth::user();
        $amount = $request->amount ?? 10000;

        $user->wallet->increment('balance', $amount);

        return response()->json([
            'success' => true,
            'message' => 'test money added',
            'balance' => round($user->wallet->balance, 2),
        ]);
    }
}
