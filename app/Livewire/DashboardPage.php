<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\ScheduledPayment;

class DashboardPage extends Component
{
    public $balance = 0;
    public $recentTransactions;
    public $upcomingPayments;
    public $notificationCount = 0;
    public $balanceVisible = true;
    
    // Stats
    public $monthlyIncome = 0;
    public $monthlyExpenses = 0;
    public $transactionCount = 0;
    public $billsPaid = 0;
    public $incomePercentage = 0;
    public $expensePercentage = 0;

    public function mount()
    {
        $this->loadData();
    }

    /**
     * Load user data including wallet balance, transactions, and payments
     */
    public function loadData()
    {
        try {
            $user = Auth::user();
            $userId = $user->id;

            // Get or create wallet (with fallback balance)
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $userId],
                [
                    'balance' => 0,
                    'currency' => 'NGN',
                    'status' => 'active'
                ]
            );

            $this->balance = (float) $wallet->balance;

            // Get recent transactions (optimized query)
            $this->recentTransactions = Transaction::where('user_id', $userId)
                ->select('id', 'user_id', 'amount', 'transaction_type', 'status', 'created_at', 'description')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Get upcoming payments (optimized query)
            $this->upcomingPayments = ScheduledPayment::where('user_id', $userId)
                ->where('status', 'pending')
                ->where('scheduled_date', '>=', now()->startOfDay())
                ->select('id', 'user_id', 'amount', 'scheduled_date', 'description')
                ->orderBy('scheduled_date', 'asc')
                ->take(3)
                ->get();

            // Calculate notification count (combined with 24-hour recent transactions)
            $recentTransactionCount = $this->recentTransactions
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            $this->notificationCount = $this->upcomingPayments->count() + $recentTransactionCount;

            // Calculate stats for this month
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            // Monthly transactions
            $monthlyTransactions = Transaction::where('user_id', $userId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get();

            // Income and Expenses
            $this->monthlyIncome = (float) $monthlyTransactions
                ->where('transaction_type', 'in')
                ->sum('amount');

            $this->monthlyExpenses = (float) $monthlyTransactions
                ->where('transaction_type', 'out')
                ->sum('amount');

            // Transaction count
            $this->transactionCount = $monthlyTransactions->count();

            // Bills paid (scheduled payments completed this month)
            $this->billsPaid = (float) ScheduledPayment::where('user_id', $userId)
                ->where('status', 'completed')
                ->whereBetween('scheduled_date', [$monthStart, $monthEnd])
                ->sum('amount');

            // Calculate percentage changes
            if ($this->monthlyIncome > 0) {
                $previousMonthIncome = (float) Transaction::where('user_id', $userId)
                    ->where('transaction_type', 'in')
                    ->whereBetween('created_at', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ])
                    ->sum('amount');

                if ($previousMonthIncome > 0) {
                    $this->incomePercentage = (($this->monthlyIncome - $previousMonthIncome) / $previousMonthIncome) * 100;
                } else {
                    $this->incomePercentage = 100; // 100% increase if prev was 0
                }
            }

            if ($this->monthlyExpenses > 0) {
                $previousMonthExpenses = (float) Transaction::where('user_id', $userId)
                    ->where('transaction_type', 'out')
                    ->whereBetween('created_at', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ])
                    ->sum('amount');

                if ($previousMonthExpenses > 0) {
                    $this->expensePercentage = (($this->monthlyExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100;
                } else {
                    $this->expensePercentage = 100;
                }
            }

        } catch (\Exception $e) {
            // Graceful fallback
            $this->balance = 0;
            $this->recentTransactions = collect();
            $this->upcomingPayments = collect();
            $this->notificationCount = 0;
            $this->monthlyIncome = 0;
            $this->monthlyExpenses = 0;
            $this->transactionCount = 0;
            $this->billsPaid = 0;
        }
    }

    /**
     * Toggle balance visibility
     */
    public function toggleBalance()
    {
        $this->balanceVisible = !$this->balanceVisible;
    }

    /**
     * Refresh dashboard data (for real-time updates)
     */
    public function refresh()
    {
        $this->loadData();
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
        ]);
    }
}

