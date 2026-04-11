<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Transaction;
use App\Models\Wallet;

class TransactionController extends Controller
{
    /**
     * Transaction history
     */
    public function history(Request $request)
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->paginate($request->limit ?? 20);

        return response()->json([
            'success' => true,
            'transactions' => $transactions
        ]);
    }

    /**
     * Single transaction
     */
    public function show($id)
    {
        $transaction = Transaction::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'transaction' => $transaction
        ]);
    }

    /**
     * Create manual transaction (rarely used)
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'transaction_type' => 'required|in:deposit,withdrawal,transfer'
        ]);

        $wallet = Auth::user()->wallet;

        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'currency' => 'NGN',
            'transaction_type' => $request->transaction_type,
            'status' => 'completed',
            'reference' => 'TXN_' . Str::uuid(),
            'description' => $request->description
        ]);

        return response()->json([
            'success' => true,
            'transaction' => $transaction
        ]);
    }

    /**
     * Recent transactions
     */
    public function recent()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions
        ]);
    }
}