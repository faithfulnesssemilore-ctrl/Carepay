<?php

use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API ROUTES
|--------------------------------------------------------------------------
|
these are the endpoints that the mobile app or frontend calls
they need auth tokens (sanctum) or webhook signatures to work
*/

// WALLET ENDPOINTS
// all need to be logged in

Route::middleware(['auth:sanctum'])->group(function () {
    // get wallet balance and info
    Route::get('/wallet', [WalletController::class, 'getBalance'])
        ->name('wallet.balance');

    // get transaction history
    Route::get('/transactions', [WalletController::class, 'getTransactions'])
        ->name('transactions.history');

    // transfer money to another user
    Route::post('/wallet/transfer', [WalletController::class, 'transfer'])
        ->middleware('throttle:5,1') // max 5 per minute
        ->name('wallet.transfer');

    // start a deposit from paystack
    Route::post('/wallet/deposit/initiate', [WalletController::class, 'initiateDeposit'])
        ->middleware('throttle:10,1') // max 10 per minute (might retry)
        ->name('wallet.deposit');

    // withdraw to bank account
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])
        ->middleware('throttle:5,1') // max 5 per minute
        ->name('wallet.withdraw');

    // test endpoint - add test money (development only)
    Route::post('/wallet/test/add-money', [WalletController::class, 'testAddMoney'])
        ->name('wallet.test.add');
});

/*
|--------------------------------------------------------------------------
| WEBHOOK ENDPOINTS
|--------------------------------------------------------------------------
|
these dont need auth - they use signatures instead
*/

// paystack webhook for deposit confirmation
Route::post('/webhooks/paystack', [PaystackWebhookController::class, 'handleEvent'])
    ->name('webhook.paystack');
