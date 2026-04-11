<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\VirtualAccount;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * AddMoney Livewire Component
 * Multi-step deposit workflow with multiple payment methods
 * Methods: bank-transfer, cash, card, ussd
 */
class AddMoney extends Component
{
    // ==================== Multi-Step Form State ====================
    public $step = 'select'; // select | details | success
    public $selectedMethod = null; // bank-transfer | cash | card | ussd
    
    // Card Payment
    public $cardAmount = '';
    public $selectedCard = '';
    
    // USSD Payment
    public $selectedBank = '';
    
    // UI State
    public $copiedField = null; // Tracks which field was copied
    
    // Status
    public $successMessage = '';
    public $errorMessage = '';
    public $isProcessing = false;
    public $currentBalance = 0;
    public $virtualAccount;

    /**
     * Initialize component - load balance and virtual account
     */
    public function mount()
    {
        $this->loadBalance();
        $this->virtualAccount = VirtualAccount::where('user_id', Auth::id())->first();
    }

    /**
     * Load current wallet balance
     */
    public function loadBalance()
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if ($wallet) {
                $this->currentBalance = $wallet->balance;
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Unable to load balance';
        }
    }

    /**
     * Handle back button navigation
     */
    public function handleBack()
    {
        if ($this->step === 'details') {
            $this->step = 'select';
            $this->selectedMethod = null;
            $this->resetDetails();
        } elseif ($this->step === 'success') {
            $this->redirect('/app/wallet', navigate: true);
        }
    }

    /**
     * Select a deposit method and move to details step
     */
    public function handleMethodSelect($method)
    {
        $validMethods = ['bank-transfer', 'cash', 'card', 'ussd'];
        
        if (in_array($method, $validMethods)) {
            $this->selectedMethod = $method;
            $this->step = 'details';
            $this->errorMessage = '';
        } else {
            $this->errorMessage = 'Invalid deposit method selected.';
        }
    }

    /**
     * Copy text to clipboard and show feedback
     */
    public function handleCopy($text, $field)
    {
        // In Livewire, we set the field and let JavaScript handle copying
        $this->copiedField = $field;
        
        // Reset the copied field after 2 seconds
        $this->dispatch('copy-to-clipboard', ['text' => $text]);
        
        // Reset visual feedback after 2 seconds
        $this->scheduleReset();
    }

    /**
     * Schedule reset of copied feedback
     */
    public function scheduleReset()
    {
        sleep(2);
        $this->copiedField = null;
    }

    /**
     * Handle deposit confirmation (move to success step)
     */
    public function handleConfirmTransfer()
    {
        // Validate based on payment method
        if ($this->selectedMethod === 'card') {
            if (empty($this->cardAmount) || floatval($this->cardAmount) <= 0) {
                $this->errorMessage = 'Please enter a valid amount.';
                return;
            }
            if (empty($this->selectedCard)) {
                $this->errorMessage = 'Please select a card.';
                return;
            }
        } elseif ($this->selectedMethod === 'ussd') {
            if (empty($this->selectedBank)) {
                $this->errorMessage = 'Please select a bank.';
                return;
            }
        }

        // Process the deposit
        $this->processDeposit();
    }

    /**
     * Process the deposit based on selected method
     */
    private function processDeposit()
    {
        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            $user = Auth::user();
            $amount = $this->selectedMethod === 'card' ? (float) $this->cardAmount : 0;

            // Log the deposit request
            DB::table('deposits')->insert([
                'user_id' => $user->id,
                'amount' => $amount,
                'method' => $this->selectedMethod,
                'card' => $this->selectedCard,
                'bank' => $this->selectedBank,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Move to success step
            $this->step = 'success';
            $this->successMessage = 'Your deposit request has been received.';
            $this->dispatch('depositProcessed');

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Reset details form fields
     */
    private function resetDetails()
    {
        $this->cardAmount = '';
        $this->selectedCard = '';
        $this->selectedBank = '';
        $this->copiedField = null;
    }

    /**
     * Reset entire form to initial state
     */
    public function resetForm()
    {
        $this->step = 'select';
        $this->selectedMethod = null;
        $this->resetDetails();
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->isProcessing = false;
        $this->loadBalance();
    }

    public function render()
    {
        return view('livewire.add-money', [
            'step' => $this->step,
            'selectedMethod' => $this->selectedMethod,
            'cardAmount' => $this->cardAmount,
            'selectedCard' => $this->selectedCard,
            'selectedBank' => $this->selectedBank,
            'copiedField' => $this->copiedField,
        ]);
    }
}


