<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;


class BankTransferController extends Controller
{
    //
    public function transfer(Request $request)
{
    $validated = $request->validate([
        'bank_code' => 'required|string',
        'account_number' => 'required|string',
        'amount' => 'required|numeric|min:100',
    ]);

    $user = auth()->user();
    $wallet = $user->wallet;

    if($wallet->balance < $validated['amount']){
        return response()->json([
            'message' => 'Insufficient balance'
        ],400);
    }

    DB::beginTransaction();

    try {

        // debit wallet
        $wallet->balance -= $validated['amount'];
        $wallet->save();

        // create pending transaction
        $transaction = Transaction::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'amount' => -$validated['amount'],
            'transaction_type' => 'bank_transfer',
            'status' => 'pending'
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Transfer processing',
            'transaction' => $transaction
        ]);

    } catch (\Exception $e){
        DB::rollBack();
        throw $e;
    }
}
}
