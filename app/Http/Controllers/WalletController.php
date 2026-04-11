<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\User;

class WalletController extends Controller
{
    /**
     * Get the authenticated user's wallet
     */
    public function getWallet()
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                // Create wallet if it doesn't exist
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'currency' => 'USD',
                    'status' => 'active'
                ]);
            }

            return response()->json([
                'success' => true,
                'wallet' => $wallet,
                'balance' => $wallet->balance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching wallet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deposit funds into wallet
     */
    public function deposit(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'nullable|string',
                'description' => 'nullable|string'
            ]);

            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'currency' => 'USD',
                    'status' => 'active'
                ]);
            }

            DB::beginTransaction();

            try {
                // Update wallet balance
                $wallet->balance += $validated['amount'];
                $wallet->save();

                // Create transaction record
                $transaction = Transaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'currency' => 'USD',
                    'transaction_type' => 'deposit',
                    'status' => 'completed',
                    'description' => $validated['description'] ?? 'Wallet deposit',
                    'idempotency_key' => $request->header('Idempotency-Key')
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Deposit successful',
                    'transaction' => $transaction,
                    'new_balance' => $wallet->balance
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing deposit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Withdraw funds from wallet
     */
    public function withdraw(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string'
            ]);

            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            if ($wallet->balance < $validated['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Update wallet balance
                $wallet->balance -= $validated['amount'];
                $wallet->save();

                // Create transaction record
                $transaction = Transaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $user->id,
                    'amount' => -$validated['amount'],
                    'currency' => 'NGN',
                    'transaction_type' => 'withdrawal',
                    'status' => 'completed',
                    'description' => $validated['description'] ?? 'Wallet withdrawal',
                    'idempotency_key' => $request->header('Idempotency-Key')
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Withdrawal successful',
                    'transaction' => $transaction,
                    'new_balance' => $wallet->balance
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transfer funds to another user
     */
    public function transfer(Request $request)
    {
        try {
            $validated = $request->validate([
                'recipient_phone' => 'required|string|exists:users,phone',
                 'recipient' => 'required|string',
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string'
            ]);

            $sender = Auth::user();
            $senderWallet = $sender->wallet;

            if (!$senderWallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sender wallet not found'
                ], 404);
            }

            if ($senderWallet->balance < $validated['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            $recipient = User::where('phone', $validated['recipient_phone'])->first();
            $recipientWallet = $recipient->wallet;

            if (!$recipientWallet) {
                $recipientWallet = Wallet::create([
                    'user_id' => $recipient->id,
                    'balance' => 0,
                    'currency' => 'USD',
                    'status' => 'active'
                ]);
            }

            DB::beginTransaction();

            try {
                // Deduct from sender
                $senderWallet->balance -= $validated['amount'];
                $senderWallet->save();

                // Add to recipient
                $recipientWallet->balance += $validated['amount'];
                $recipientWallet->save();

                // Create transaction record for sender
                $transaction = Transaction::create([
                    'wallet_id' => $senderWallet->id,
                    'user_id' => $sender->id,
                    'recipient_id' => $recipient->id,
                    'amount' => -$validated['amount'],
                    'currency' => 'USD',
                    'transaction_type' => 'transfer',
                    'status' => 'completed',
                    'description' => $validated['description'] ?? "Transfer to {$recipient->first_name} {$recipient->last_name}",
                    'idempotency_key' => $request->header('Idempotency-Key')
                ]);

                // Create transaction record for recipient
                Transaction::create([
                    'wallet_id' => $recipientWallet->id,
                    'user_id' => $recipient->id,
                    'recipient_id' => $sender->id,
                    'amount' => $validated['amount'],
                    'currency' => 'USD',
                    'transaction_type' => 'transfer',
                    'status' => 'completed',
                    'description' => "Transfer from {$sender->first_name} {$sender->last_name}"
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Transfer successful',
                    'transaction' => $transaction,
                    'new_balance' => $senderWallet->balance
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing transfer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
