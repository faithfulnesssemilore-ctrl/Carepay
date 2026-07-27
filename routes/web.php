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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

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
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/admin/login', function () {
        return view('auth.admin-login');
    })->name('admin.login');
    Route::redirect('/admin', '/admin/login');
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
    Route::patch('/profile', [Profile::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [Profile::class, 'deleteAccount'])->name('profile.delete');

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
Route::get('/verify-email', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware('auth')
    ->name('password.confirm');

Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware('auth');

Route::put('/password', [PasswordController::class, 'update'])
    ->middleware('auth')
    ->name('password.update');

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
