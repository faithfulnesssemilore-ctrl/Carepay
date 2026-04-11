<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Transaction;

class Admin extends Component
{
    public $totalUsers = 0;
    public $totalVolume = 0;
    public $totalTransactions = 0;
    public $totalRevenue = 0;
    
    public $revenueData = [];
    public $transactionData = [];
    public $userDistribution = [];
    public $recentActivity = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        try {
            // Get total users
            $this->totalUsers = User::where('role', 'user')->count();
            
            // Get transactions and volume
            $transactions = Transaction::all();
            $this->totalTransactions = $transactions->count();
            $this->totalVolume = $transactions->sum('amount');
            
            // Sample revenue data
            $this->totalRevenue = 48200;
            
            // Revenue data for chart
            $this->revenueData = [
                ['name' => 'Jan', 'value' => 42000],
                ['name' => 'Feb', 'value' => 38000],
                ['name' => 'Mar', 'value' => 51000],
                ['name' => 'Apr', 'value' => 48000],
                ['name' => 'May', 'value' => 62000],
                ['name' => 'Jun', 'value' => 58000],
            ];
            
            // Transaction data by day
            $this->transactionData = [
                ['name' => 'Mon', 'value' => 245],
                ['name' => 'Tue', 'value' => 312],
                ['name' => 'Wed', 'value' => 278],
                ['name' => 'Thu', 'value' => 398],
                ['name' => 'Fri', 'value' => 445],
                ['name' => 'Sat', 'value' => 267],
                ['name' => 'Sun', 'value' => 198],
            ];
            
            // User distribution
            $this->userDistribution = [
                ['name' => 'Tier 1', 'value' => 12450, 'color' => '#a855f7'],
                ['name' => 'Tier 2', 'value' => 3200, 'color' => '#7c3aed'],
                ['name' => 'Tier 3', 'value' => 850, 'color' => '#6366f1'],
            ];
            
            // Recent activity
            $this->recentActivity = [
                ['id' => 1, 'user' => 'John Doe', 'action' => 'New registration', 'type' => 'user', 'time' => '2 min ago'],
                ['id' => 2, 'user' => 'Sarah Wilson', 'action' => 'Transaction completed', 'type' => 'transaction', 'time' => '5 min ago'],
                ['id' => 3, 'user' => 'Michael Brown', 'action' => 'KYC submitted', 'type' => 'kyc', 'time' => '12 min ago'],
                ['id' => 4, 'user' => 'Jennifer Lee', 'action' => 'Account upgraded to Tier 2', 'type' => 'user', 'time' => '23 min ago'],
                ['id' => 5, 'user' => 'David Chen', 'action' => 'Failed transaction', 'type' => 'alert', 'time' => '45 min ago'],
            ];
        } catch (\Exception $e) {
            Log::error('Error loading admin dashboard data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.admin');
    }
}
