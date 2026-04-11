<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ScheduledPaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Middleware\IdempotencyMiddleware;
use App\Http\Controllers\DepositController;
// WALLET ENDPOINTS


// Get user's wallet
Route::middleware(['auth:sanctum'])->get('/wallet', [WalletController::class, 'getWallet'])->name('wallet.get');

// Deposit funds with idempotency protection
Route::middleware(['auth:sanctum', IdempotencyMiddleware::class])->post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');

// Withdraw funds with idempotency protection
Route::middleware(['auth:sanctum', IdempotencyMiddleware::class])->post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');

// Transfer funds with idempotency protection
Route::middleware(['auth:sanctum', IdempotencyMiddleware::class])->post('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer');

// TRANSACTION ENDPOINTS

// Get transaction history
Route::middleware(['auth:sanctum'])->get('/transactions', [TransactionController::class, 'history'])->name('transactions.history');

// Get transaction history (legacy route)
Route::middleware(['auth:sanctum'])->get('/transactions/history', [TransactionController::class, 'history'])->name('transactions.history.legacy');

// Get recent transactions
Route::middleware(['auth:sanctum'])->get('/transactions/recent', [TransactionController::class, 'recent'])->name('transactions.recent');

// Get single transaction
Route::middleware(['auth:sanctum'])->get('/transactions/{id}', [TransactionController::class, 'show'])->name('transactions.show');

// Create transaction
Route::middleware(['auth:sanctum', IdempotencyMiddleware::class])->post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

// SCHEDULED PAYMENTS ENDPOINTS


// Get all scheduled payments
Route::middleware(['auth:sanctum'])->get('/scheduled-payments', [ScheduledPaymentController::class, 'index'])->name('scheduled-payments.index');

// Get upcoming scheduled payments
Route::middleware(['auth:sanctum'])->get('/scheduled-payments/upcoming', [ScheduledPaymentController::class, 'upcoming'])->name('scheduled-payments.upcoming');

// Create scheduled payment
Route::middleware(['auth:sanctum'])->post('/scheduled-payments', [ScheduledPaymentController::class, 'store'])->name('scheduled-payments.store');

// Update scheduled payment
Route::middleware(['auth:sanctum'])->put('/scheduled-payments/{id}', [ScheduledPaymentController::class, 'update'])->name('scheduled-payments.update');

// Cancel scheduled payment
Route::middleware(['auth:sanctum'])->delete('/scheduled-payments/{id}', [ScheduledPaymentController::class, 'cancel'])->name('scheduled-payments.cancel');

// ============================================
// NOTIFICATIONS ENDPOINTS
// ============================================

// Get all notifications
Route::middleware(['auth:sanctum'])->get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

// Get notification count
Route::middleware(['auth:sanctum'])->get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');

// ============================================
// USER ENDPOINTS
// ============================================

// Get current user information
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'user' => $request->user(),
        'wallet' => $request->user()->wallet
    ]);
})->name('user.show');

//Deposit endpoints
Route::post('/deposits/initialize', [DepositController::class, 'initialize']);
Route::post('/payments/webhook', [DepositController::class, 'webhook']);
Route::get('/transactions/{reference}/verify', [DepositController::class, 'verify']);
Route::get('/payments/callback', function () {
    return view('payment.success');
})->name('payment.callback');
