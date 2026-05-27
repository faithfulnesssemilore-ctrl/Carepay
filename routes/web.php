<?php

use App\Http\Controllers\PaystackWebhookController;
use App\Livewire\AddMoney;
use App\Livewire\Admin\Admin;
use App\Livewire\Admin\AdminKYC;
use App\Livewire\Admin\AdminTransactions;
use App\Livewire\Admin\AdminUsers;
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

});

// Admin ROute
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', Admin::class)->name('dashboard');

        Route::get('/kyc', AdminKYC::class)->name('kyc');

        Route::get('/transactions', AdminTransactions::class)->name('transactions');

        Route::get('/users', AdminUsers::class)->name('users');

    });

/*
|--------------------------------------------------------------------------
| SUPER ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')
    ->middleware(['auth', 'role:super_admin'])
    ->name('super_admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return view('super_admin.dashboard');
        })->name('dashboard');

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

// user clicks verification link in email
// link looks like: /email/verify/user-id/hash
Route::get('/email/verify/{user}/{hash}', [
    \App\Http\Controllers\Auth\VerifyEmailController::class,
    'verify',
])->name('verification.verify');

// user can request new verification email if they didn't get first one
Route::post('/email/verification-notification', [
    \App\Http\Controllers\Auth\VerifyEmailController::class,
    'resend',
])->middleware('auth')
    ->name('verification.resend');
