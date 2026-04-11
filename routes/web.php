<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Livewire\Security\VerifyPin;
use App\Livewire\UserAuth\Register;
use App\Livewire\UserAuth\Login;
use App\Http\Controllers\PaystackWebhookController;
use App\Livewire\DashboardPage;
use App\Livewire\SendMoney;
use App\Livewire\AddMoney;
use App\Livewire\BillPayment;
use App\Livewire\Profile;
use App\Livewire\Settings;
use App\Livewire\Wallet as WalletComponent;
use App\Livewire\Transactions;

use App\Livewire\Admin\Admin;
use App\Livewire\Admin\AdminKYC;
use App\Livewire\Admin\AdminTransactions;
use App\Livewire\Admin\AdminUsers;

use App\Models\User;

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
Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');


//LOGOUT

Route::get('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect('/login');
});

   Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

//USER DASHBOARD (Protected)

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


//Admin ROute 
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
| PAYMENT CALLBACK
|--------------------------------------------------------------------------
*/
Route::get('/payment/callback', function () {
    return redirect()->route('dashboard')
        ->with('success', 'Payment successful');
})->name('payment.callback');

/*
|--------------------------------------------------------------------------
| TEST ROUTE (REMOVE IN PRODUCTION)
|--------------------------------------------------------------------------
*/
Route::get('/test', function () {
    return User::first();
});

Route::post('/webhook/paystack', [PaystackWebhookController::class, 'handle']);

Route::get('verify-pin', VerifyPin::class)->name('pin-verify');
Route::get('/payment/callback', function () {
    return redirect()->route('dashboard')->with('success', 'Payment successful');
});
