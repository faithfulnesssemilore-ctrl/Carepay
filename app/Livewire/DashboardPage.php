<?php

namespace App\Livewire;

use App\Models\ScheduledPayment;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DashboardPage extends Component
{
    public float $balance = 0;

    public bool $balanceVisible = true;

    public int $notificationCount = 0;

    // stat cards
    public float $monthlyIncome = 0;

    public float $monthlyExpenses = 0;

    public int $transactionCount = 0;

    public float $billsPaid = 0;

    public float $incomePercentage = 0;

    public float $expensePercentage = 0;

    // daily limit progress
    public float $dailyLimitUsedPercent = 0;

    public float $dailyLimitTotal = 0;

    public float $dailyLimitUsed = 0;

    // chart data json for Chart.js
    public string $chartData = '{}';

    // collections
    public $recentTransactions;

    public $upcomingPayments;

    public function mount(): void
    {
        $this->recentTransactions = collect();
        $this->upcomingPayments = collect();
        $this->loadData();
    }

    public function loadData(): void
    {
        try {
            $user = Auth::user();
            $userId = $user->id;

            // get or create wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => 'NGN', 'status' => 'active']
            );

            // Balance is cast to naira by MoneyCast
            $this->balance = round($wallet->balance, 2);

            // recent transactions
            $this->recentTransactions = Transaction::where('user_id', $userId)
                ->select('id', 'user_id', 'amount', 'type', 'status', 'created_at', 'description')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // upcoming scheduled payments
            $this->upcomingPayments = ScheduledPayment::where('user_id', $userId)
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now()->startOfDay())
                ->select('id', 'user_id', 'amount', 'scheduled_date', 'description')
                ->orderBy('scheduled_date', 'asc')
                ->take(3)
                ->get();

            // notification count
            $recentCount = $this->recentTransactions
                ->where('created_at', '>=', now()->subHours(24))
                ->count();
            $this->notificationCount = $this->upcomingPayments->count() + $recentCount;

            // this month stats
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $monthlyTx = Transaction::where('user_id', $userId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get();

            // Transaction models cast `amount` to naira via MoneyCast, so sums on the collection are already in naira
            $this->monthlyIncome = round($monthlyTx->where('type', 'credit')->sum('amount'), 2);
            $this->monthlyExpenses = round($monthlyTx->where('type', 'debit')->sum('amount'), 2);
            $this->transactionCount = $monthlyTx->count();

            // amount field is cast to naira, don't divide
            $this->billsPaid = round(
                ScheduledPayment::where('user_id', $userId)
                    ->where('status', 'completed')
                    ->whereBetween('scheduled_date', [$monthStart, $monthEnd])
                    ->sum('amount'),
                2
            );

            // percentage change vs last month
            $prevStart = now()->subMonth()->startOfMonth();
            $prevEnd = now()->subMonth()->endOfMonth();

            // amount is cast to naira
            $prevIncome = Transaction::where('user_id', $userId)
                ->where('type', 'credit')
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->sum('amount');

            $prevExpenses = Transaction::where('user_id', $userId)
                ->where('type', 'debit')
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->sum('amount');

            $this->incomePercentage = $prevIncome > 0
                ? round((($this->monthlyIncome - $prevIncome) / $prevIncome) * 100, 1)
                : ($this->monthlyIncome > 0 ? 100 : 0);

            $this->expensePercentage = $prevExpenses > 0
                ? round((($this->monthlyExpenses - $prevExpenses) / $prevExpenses) * 100, 1)
                : ($this->monthlyExpenses > 0 ? 100 : 0);

            // daily limit progress
            $limits = $user->limits;
            if ($limits) {
                // daily_transfer_limit is in naira, amount is cast to naira
                $dailyLimit = $limits->daily_transfer_limit;
                $todaySpent = Transaction::where('user_id', $userId)
                    ->where('type', 'debit')
                    ->whereDate('created_at', today())
                    ->sum('amount');

                $this->dailyLimitTotal = round($dailyLimit, 2);
                $this->dailyLimitUsed = round($todaySpent, 2);
                $this->dailyLimitUsedPercent = $dailyLimit > 0
                    ? min(100, round(($todaySpent / $dailyLimit) * 100))
                    : 0;
            }

            // chart data for last 6 months
            $chartMonths = [];
            $chartIncome = [];
            $chartExpense = [];

            for ($i = 5; $i >= 0; $i--) {
                $m = now()->subMonths($i);
                $chartMonths[] = $m->format('M');

                // amount is cast to naira
                $chartIncome[] = round(
                    Transaction::where('user_id', $userId)
                        ->where('type', 'credit')
                        ->whereYear('created_at', $m->year)
                        ->whereMonth('created_at', $m->month)
                        ->sum('amount'),
                    2
                );

                $chartExpense[] = round(
                    Transaction::where('user_id', $userId)
                        ->where('type', 'debit')
                        ->whereYear('created_at', $m->year)
                        ->whereMonth('created_at', $m->month)
                        ->sum('amount'),
                    2
                );
            }

            $this->chartData = json_encode([
                'labels' => $chartMonths,
                'income' => $chartIncome,
                'expenses' => $chartExpense,
            ]);

        } catch (\Exception $e) {
            // Log the exception and preserve any successfully loaded balance
            Log::error('DashboardPage loadData failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            $this->recentTransactions = $this->recentTransactions ?? collect();
            $this->upcomingPayments = $this->upcomingPayments ?? collect();
            $this->notificationCount = $this->notificationCount ?? 0;
            $this->monthlyIncome = $this->monthlyIncome ?? 0;
            $this->monthlyExpenses = $this->monthlyExpenses ?? 0;
            $this->transactionCount = $this->transactionCount ?? 0;
            $this->billsPaid = $this->billsPaid ?? 0;
            $this->chartData = $this->chartData ?? '{"labels":[],"income":[],"expenses":[]}';
        }
    }

    public function toggleBalance(): void
    {
        $this->balanceVisible = ! $this->balanceVisible;
    }

    public function refresh(): void
    {
        $this->loadData();
        $this->dispatch('toast', type: 'info', message: 'Dashboard refreshed');
    }

    public function render()
    {
        return view('livewire.dashboard-page', [
            'balance' => $this->balance,
            'balanceVisible' => $this->balanceVisible,
            'monthlyIncome' => $this->monthlyIncome,
            'monthlyExpenses' => $this->monthlyExpenses,
            'transactionCount' => $this->transactionCount,
            'billsPaid' => $this->billsPaid,
            'incomePercentage' => $this->incomePercentage,
            'expensePercentage' => $this->expensePercentage,
            'recentTransactions' => $this->recentTransactions,
            'upcomingPayments' => $this->upcomingPayments,
            'notificationCount' => $this->notificationCount,
            'chartData' => json_decode($this->chartData, true),
            'dailyLimitUsedPercent' => $this->dailyLimitUsedPercent,
            'dailyLimitTotal' => $this->dailyLimitTotal,
            'dailyLimitUsed' => $this->dailyLimitUsed,
        ])->layout('components.layouts.app');
    }
}
