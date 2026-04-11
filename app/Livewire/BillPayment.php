<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class BillPayment extends Component
{
    // Step management
    public $currentStep = 'category'; // category, details, confirm, success
    public $selectedCategory = null;

    // Form properties
    public $provider = '';
    public $accountNumber = '';
    public $amount = '';
    
    // UI properties
    public $successMessage = '';
    public $errorMessage = '';
    public $isProcessing = false;
    public $currentBalance = 0;
    public $referenceNumber = '';

    // Bill categories
    public $billCategories = [];
    public $recentBills = [];

    protected $rules = [
        'provider' => 'required|string|max:255',
        'accountNumber' => 'required|string|max:100',
        'amount' => 'required|numeric|min:0.01',
    ];

    public function mount()
    {
        $this->loadBalance();
        $this->initializeCategories();
        $this->loadRecentBills();
    }

    public function initializeCategories()
    {
        $this->billCategories = [
            ['id' => 'electricity', 'name' => 'Electricity', 'icon' => 'zap', 'providers' => ['PowerCo', 'ElectricPlus', 'City Electric']],
            ['id' => 'airtime', 'name' => 'Airtime', 'icon' => 'smartphone', 'providers' => ['Verizon', 'AT&T', 'T-Mobile', 'Sprint']],
            ['id' => 'data', 'name' => 'Internet Data', 'icon' => 'wifi', 'providers' => ['Comcast', 'Spectrum', 'Cox', 'Verizon Fios']],
            ['id' => 'tv', 'name' => 'TV Subscription', 'icon' => 'tv', 'providers' => ['Netflix', 'Hulu', 'Disney+', 'HBO Max', 'Amazon Prime']],
        ];
    }

    public function loadRecentBills()
    {
        $this->recentBills = [
            ['id' => 1, 'category' => 'Electricity', 'provider' => 'PowerCo', 'amount' => 85.50, 'date' => 'Feb 25, 2026'],
            ['id' => 2, 'category' => 'TV Subscription', 'provider' => 'Netflix', 'amount' => 15.99, 'date' => 'Feb 23, 2026'],
            ['id' => 3, 'category' => 'Internet Data', 'provider' => 'Comcast', 'amount' => 59.99, 'date' => 'Feb 22, 2026'],
        ];
    }

    public function loadBalance()
    {
        try {
            $user = Auth::user();
            $wallet = Wallet::where('user_id', $user->id)->first();

            if ($wallet) {
                $this->currentBalance = (float) $wallet->balance;
            }
        } catch (\Exception $e) {
            Log::error('Error loading balance: ' . $e->getMessage());
        }
    }

    public function selectCategory($categoryId)
    {
        $category = collect($this->billCategories)->firstWhere('id', $categoryId);
        if ($category) {
            $this->selectedCategory = $category;
            $this->currentStep = 'details';
        }
    }

    public function goBack()
    {
        if ($this->currentStep === 'details') {
            $this->currentStep = 'category';
            $this->selectedCategory = null;
        } elseif ($this->currentStep === 'confirm') {
            $this->currentStep = 'details';
        }
    }

    public function submitDetails()
    {
        $this->validate();
        $this->currentStep = 'confirm';
    }

    public function confirmPayment()
    {
        $this->validate();
        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            $user = Auth::user();
            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                $this->errorMessage = 'Wallet not found. Please contact support.';
                $this->isProcessing = false;
                return;
            }

            if ($wallet->balance < $this->amount) {
                $this->errorMessage = 'Insufficient balance to pay this bill.';
                $this->isProcessing = false;
                return;
            }

            DB::beginTransaction();

            try {
                $wallet->balance -= (float)$this->amount;
                $wallet->save();

                $this->referenceNumber = 'BPI' . rand(100000, 999999);

                Transaction::create([
                    'user_id' => $user->id,
                    'amount' => -$this->amount,
                    'transaction_type' => 'sent',
                    'status' => 'completed',
                    'description' => $this->provider . ' - ' . $this->selectedCategory['name'],
                    'receiver_name' => $this->provider,
                    'category' => $this->selectedCategory['name']
                ]);

                DB::commit();

                $this->currentBalance = (float)$wallet->balance;
                $this->currentStep = 'success';

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Bill payment failed: ' . $e->getMessage();
        }

        $this->isProcessing = false;
    }

    public function completePayment()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->currentStep = 'category';
        $this->selectedCategory = null;
        $this->provider = '';
        $this->accountNumber = '';
        $this->amount = '';
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.bill-payment');
    }
}
