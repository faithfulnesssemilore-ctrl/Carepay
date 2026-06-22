<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class TransactionReceiptController extends Controller
{
    public function download(Transaction $transaction)
    {
        if (Auth::id() !== $transaction->user_id) {
            abort(403);
        }

        $pdf = Pdf::loadView('receipts.transaction-receipt', [
            'transaction' => $transaction,
        ]);

        $filename = sprintf('receipt-%s.pdf', strtolower($transaction->reference));

        return $pdf->download($filename);
    }
}
