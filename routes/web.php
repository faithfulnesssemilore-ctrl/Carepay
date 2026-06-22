<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\NotificationStreamController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\TransactionReceiptController;
use App\Livewire\AddMoney;
use App\Livewire\BillPayment;
use App\Livewire\DashboardPage;
use App\Livewire\Profile;
use App\Livewire\Security\VerifyPin;
use App\Livewire\SendMoney;
use App\Livewire\Settings;
use App\Livewire\Transactions;
use App\Livewire\UserAuth\Login;
use App\Livewire\UserAuth\Register;
use App\Livewire\Wallet as WalletComponent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('home');
})->name('home');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
});

// LOGOUT - Use POST to prevent CSRF attacks
Route::middleware('auth')->post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/login');
})->name('logout');

// USER DASHBOARD (Protected)

Route::middleware(['auth', 'active'])->group(function () {

    Route::get('/dashboard', DashboardPage::class)->name('dashboard');

    Route::get('/wallet', WalletComponent::class)->name('wallet');

    Route::get('/send-money', SendMoney::class)->name('send-money');

    Route::get('/add-money', AddMoney::class)->name('add-money');

    Route::get('/deposit', AddMoney::class)->name('deposit');

    Route::get('/bill-payment', BillPayment::class)->name('bill-payment');

    Route::get('/profile', Profile::class)->name('profile');

    Route::get('/settings', Settings::class)->name('settings');

    Route::get('/transactions', Transactions::class)->name('transactions');

    Route::get('/transactions/{transaction}/receipt', [TransactionReceiptController::class, 'download'])
        ->name('transaction.receipt.download');

});

/*
|--------------------------------------------------------------------------
/*
|--------------------------------------------------------------------------
| PAYMENT CALLBACK
|--------------------------------------------------------------------------
*/
Route::get('/payment/callback', function () {
    return redirect()->route('dashboard')
        ->with('success', 'Payment successful');
})->name('payment.callback');

// Paystack webhook route - NO AUTH because Paystack calls it from their servers
// webhook is verified by signature, not by auth token
Route::post('/webhook/paystack', [PaystackWebhookController::class, 'handleEvent']);

Route::get('verify-pin', VerifyPin::class)->name('pin-verify');

/*
|--------------------------------------------------------------------------
| EMAIL VERIFICATION ROUTES
|--------------------------------------------------------------------------
*/

// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/notifications/stream', NotificationStreamController::class);

    // Statement of Account routes
    Route::post('/statement/export', [StatementController::class, 'export'])->name('statement.export');
    Route::get('/statement/download/{file}', [StatementController::class, 'download'])->name('statement.download');
});

// user clicks verification link in email
// link looks like: /email/verify/user-id/hash
Route::get('/email/verify/{user}/{hash}', [
    VerifyEmailController::class,
    'verify',
])->name('verification.verify');

// user can request new verification email if they didn't get first one
Route::post('/email/verification-notification', [
    VerifyEmailController::class,
    'resend',
])->middleware('auth')
    ->name('verification.resend');
