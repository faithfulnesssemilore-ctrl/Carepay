<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'admin@carepay.com')->first();
Illuminate\Support\Facades\Auth::login($user);
try {
    echo "auth=" . (Illuminate\Support\Facades\Auth::check() ? 'yes' : 'no') . "\n";
    echo "user=" . Illuminate\Support\Facades\Auth::user()->email . "\n";
    $wallet = App\Models\Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'currency' => 'NGN', 'status' => 'active']);
    echo "wallet id=" . $wallet->id . " balance=" . $wallet->balance . "\n";
    echo "converted=" . round($wallet->balance / 100, 2) . "\n";
    $recentTransactions = App\Models\Transaction::where('user_id', $user->id)
        ->select('id','user_id','amount','type','status','created_at','description')
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get();
    echo "recent count=" . $recentTransactions->count() . "\n";
    $upcomingPayments = App\Models\ScheduledPayment::where('user_id', $user->id)
        ->where('status', 'pending')
        ->where('scheduled_date', '>=', now()->startOfDay())
        ->select('id','user_id','amount','scheduled_date','description')
        ->orderBy('scheduled_date','asc')
        ->take(3)
        ->get();
    echo "upcoming count=" . $upcomingPayments->count() . "\n";
    $monthStart = now()->startOfMonth();
    $monthEnd = now()->endOfMonth();
    $monthlyTx = App\Models\Transaction::where('user_id', $user->id)
        ->whereBetween('created_at', [$monthStart, $monthEnd])
        ->get();
    echo "monthlyTx count=" . $monthlyTx->count() . "\n";
    echo "monthlyIncome=" . round($monthlyTx->where('type', 'credit')->sum('amount') / 100, 2) . "\n";
    echo "monthlyExpenses=" . round($monthlyTx->where('type', 'debit')->sum('amount') / 100, 2) . "\n";
} catch (Throwable $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
