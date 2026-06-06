<?php

namespace App\Livewire;

use App\Models\ScheduledPayment;
use App\Models\Transaction;
use App\Models\Wallet as WalletModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Wallet extends Component
{
    // Wallet properties
    public $balance = 0;

    public $pendingBalance = 0;

    public $reservedBalance = 0;

    public $currency = 'NGN';

    public $walletStatus = 'active';

    public $walletId = null;

    // Balance data for cards
    public $balanceData = [];

    public $bphp = [];

    // Transaction history
    public $transactions = [];

    public $scheduledPayments = [];

    // UI properties
    public $balanceVisible = true;

    public $errorMessage = '';

    public $successMessage = '';

    public $activeTab = 'overview';

    public function mount()
    {
        $this->loadWalletData();
    }

    /**
     * Load wallet data from database
     */
    public function loadWalletData()
    {
        try {
            $user = Auth::user();

            if (! $user) {
                redirect()->route('login');

                return;
            }

            $wallet = $user->wallet;

            if (! $wallet) {
                // Create wallet if it doesn't exist
                $wallet = WalletModel::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'currency' => 'NGN',
                    'status' => 'active',
                ]);
            }

            $this->walletId = $wallet->id;
            $this->balance = round((float) $wallet->balance, 2);
            $this->currency = $wallet->currency;
            $this->walletStatus = $wallet->status;

            // Calculate pending and reserved balances
            $this->calculateBalances($wallet);

            // Load chart data
            $this->loadBalanceData();

            // Load recent transactions
            $this->transactions = Transaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Load scheduled payments
            $this->scheduledPayments = ScheduledPayment::where('user_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('scheduled_date', 'asc')
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to load wallet data: '.$e->getMessage();
        }
    }

    /**
     * Calculate pending and reserved balances
     */
    private function calculateBalances($wallet)
    {
        $userId = $wallet->user_id;

        // Pending = transactions in pending status
        // sum('amount') returns raw kobo from the database, so convert to naira
        $pendingKobo = Transaction::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('amount');

        $this->pendingBalance = round($pendingKobo / 100, 2);

        // Reserved = scheduled payments (amount stored in kobo in DB)
        $reservedKobo = ScheduledPayment::where('user_id', $userId)
            ->where('status', 'pending')
            ->sum('amount');

        $this->reservedBalance = round($reservedKobo / 100, 2);
    }

    /**
     * Load balance data for charts and display
     */
    private function loadBalanceData()
    {
        $this->balanceData = [
            [
                'name' => 'Available Balance',
                'value' => $this->balance,
                'color' => '#a855f7',
            ],
            [
                'name' => 'Pending Balance',
                'value' => $this->pendingBalance,
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Reserved',
                'value' => $this->reservedBalance,
                'color' => '#ef4444',
            ],
        ];

        // For balance distribution pie chart
        $this->bphp = $this->balanceData;
    }

    /**
     * Toggle balance visibility
     */
    public function toggleBalance()
    {
        $this->balanceVisible = ! $this->balanceVisible;
    }

    /**
     * Refresh wallet data
     */
    public function refresh()
    {
        $this->loadWalletData();
        $this->successMessage = 'Wallet data refreshed!';
        $this->dispatch('refresh-notification');
    }

    /**
     * Get formatted balance based on visibility
     */
    public function getFormattedBalance()
    {
        if ($this->balanceVisible) {
            return '₦'.number_format($this->balance, 2);
        }

        return '••••••••';
    }

    /**
     * Get total balance (available + pending + reserved)
     */
    public function getTotalBalance()
    {
        return round($this->balance + $this->pendingBalance + $this->reservedBalance, 2);
    }

    public function render()
    {
        return view('livewire.wallet', [
            'balanceData' => $this->balanceData,
            'bphp' => $this->bphp,
        ]);
    }
}
