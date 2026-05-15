<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Transaction;
use App\Services\WalletService;
use App\Services\BankService;

/**
 * SendMoney Livewire Component
 * Multi-step transfer: account number, bank, amount, confirm, success
 */
class SendMoney extends Component
{
    // ==================== Multi-Step Form State ====================
    public $currentStep = 'recipient'; // recipient | amount | confirm | success
    
    // Recipient Step (Account Number & Bank Selection)
    public $accountNumber = '';
    public $selectedBankCode = '';
    public $banks = [];
    public $resolvedAccountName = '';
    public $accountResolutionError = '';
    
    // Amount Step
    public $amount = '';
    public $note = '';
    public $walletBalance = 0;
    public $dailyLimit = 0;
    public $dailyUsed = 0;
    public $singleLimit = 0;
    
    // PIN Modal
    public $showPinModal = false;
    public $pinInput = '';
    
    // Status
    public $successMessage = '';
    public $errorMessage = '';
    public $isProcessing = false;

    // ==================== Validation Rules ====================
    protected $rules = [
        'accountNumber' => 'required|string|digits:10',
        'selectedBankCode' => 'required|string',
        'amount' => 'required|numeric|min:0.01',
        'note' => 'nullable|string|max:500',
        'pinInput' => 'required|numeric|digits:4'
    ];

    protected $messages = [
        'accountNumber.required' => 'Account number is required.',
        'accountNumber.digits' => 'Account number must be exactly 10 digits.',
        'selectedBankCode.required' => 'Please select a bank.',
        'amount.required' => 'Please enter an amount.',
        'amount.min' => 'Amount must be greater than 0.',
        'pinInput.required' => 'PIN is required.',
        'pinInput.digits' => 'PIN must be exactly 4 digits.',
    ];

    protected $listeners = ['pinVerified'];
    
    /**
     * Initialize component - load banks and wallet limits
     */
    public function mount()
    {
        $bankService = new BankService();
        $this->banks = $bankService->getAllBanks();
        
        $user = Auth::user();
        if ($user && $user->wallet) {
            $this->walletBalance = $user->wallet->balance / 100;
        }
        
        // Load limits from user_limits table
        if ($user && $user->limits) {
            $this->dailyLimit = $user->limits->daily_transfer_limit;
            $this->singleLimit = $user->limits->single_transaction_limit;
            $this->dailyUsed = $user->limits->daily_transfer_used;
        }
    }
    
    /**
     * Resolve account name when bank is selected
     */
    public function updatedSelectedBankCode($value)
    {
        $this->resolvedAccountName = '';
        $this->accountResolutionError = '';
        
        if (empty($this->accountNumber) || strlen($this->accountNumber) !== 10) {
            return;
        }
        
        if (empty($value)) {
            return;
        }
        
        $this->resolveAccountName();
    }
    
    /**
     * Resolve account name when account number is entered
     */
    public function updatedAccountNumber($value)
    {
        $this->resolvedAccountName = '';
        $this->accountResolutionError = '';
        
        if (strlen($value) !== 10 || empty($this->selectedBankCode)) {
            return;
        }
        
        $this->resolveAccountName();
    }
    
    /**
     * Call BankService to resolve account name via Paystack API
     */
    private function resolveAccountName()
    {
        if (strlen($this->accountNumber) !== 10 || empty($this->selectedBankCode)) {
            return;
        }
        
        try {
            $bankService = new BankService();
            $accountName = $bankService->resolveAccountName($this->accountNumber, $this->selectedBankCode);
            
            if ($accountName) {
                $this->resolvedAccountName = $accountName;
            } else {
                $this->accountResolutionError = 'Unable to resolve account name. Check account number and bank.';
            }
        } catch (\Exception $e) {
            $this->accountResolutionError = 'Error resolving account: ' . $e->getMessage();
        }
    }


    /**
     * Proceed to amount step after account and bank validation
     */
    public function proceedToAmount()
    {
        $this->errorMessage = '';
        
        // Validate account number format
        if (strlen($this->accountNumber) !== 10) {
            $this->errorMessage = 'Account number must be exactly 10 digits.';
            return;
        }
        
        if (empty($this->selectedBankCode)) {
            $this->errorMessage = 'Please select a bank.';
            return;
        }
        
        if (empty($this->resolvedAccountName)) {
            $this->errorMessage = 'Unable to resolve account. Please check the account number and bank.';
            return;
        }
        
        $this->setStep('amount');
    }

    /**
     * Set the current step in the workflow
     */
    public function setStep($step)
    {
        $validSteps = ['recipient', 'amount', 'confirm', 'success'];
        
        if (in_array($step, $validSteps)) {
            $this->currentStep = $step;
            $this->errorMessage = '';
        }
    }

    /**
     * Validate amount and move to confirmation step
     */
    public function handleAmountSubmit()
    {
        $this->errorMessage = '';
        
        // Validate amount
        if (empty($this->amount) || floatval($this->amount) <= 0) {
            $this->errorMessage = 'Please enter a valid amount greater than 0.';
            return;
        }

        // Check user balance (in naira, not kobo)
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < floatval($this->amount) * 100) {
            $this->errorMessage = 'Insufficient balance for this transfer.';
            return;
        }
        
        // Check single transaction limit
        if (floatval($this->amount) > $this->singleLimit) {
            $remaining = $this->singleLimit - floatval($this->amount);
            $this->errorMessage = "Amount exceeds single transaction limit of ₦{$this->singleLimit}. Remaining: ₦{$remaining}";
            return;
        }
        
        // Check daily limit
        $dailyRemaining = $this->dailyLimit - $this->dailyUsed;
        if (floatval($this->amount) > $dailyRemaining) {
            $this->errorMessage = "Daily limit exceeded. Used: ₦{$this->dailyUsed}, Remaining: ₦{$dailyRemaining}";
            return;
        }

        $this->setStep('confirm');
    }

    /**
     * Show PIN verification modal before confirming transfer
     */
    public function showPinVerification()
    {
        $this->showPinModal = true;
        $this->pinInput = '';
    }
    
    /**
     * Verify PIN and process transfer
     */
    public function verifyPinAndTransfer()
    {
        $this->errorMessage = '';
        
        if (empty($this->pinInput)) {
            $this->errorMessage = 'PIN is required.';
            return;
        }
        
        if (strlen($this->pinInput) !== 4) {
            $this->errorMessage = 'PIN must be exactly 4 digits.';
            return;
        }
        
        $user = Auth::user();
        
        // Verify PIN against hashed PIN in database
        if (!Hash::check($this->pinInput, $user->pin)) {
            $this->errorMessage = 'Incorrect PIN. Please try again.';
            return;
        }
        
        // PIN is correct, process transfer
        $this->processTransfer();
    }
    
    /**
     * Close PIN modal without processing
     */
    public function closePinModal()
    {
        $this->showPinModal = false;
        $this->pinInput = '';
        $this->errorMessage = '';
    }

    /**
     * Process the actual transfer through external bank (Paystack or similar)
     */
    public function processTransfer()
    {
        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            // Validate wallet exists
            if (!$wallet) {
                throw new \Exception('Wallet not found. Please contact support.');
            }

            // Validate sufficient balance (in kobo)
            if ($wallet->balance < floatval($this->amount) * 100) {
                throw new \Exception('Insufficient balance for this transfer.');
            }

            // Create a transaction record for external bank transfer
            // This records the transfer but actual settlement happens with the bank
            $bankService = new BankService();
            $bank = $bankService->getBankByCode($this->selectedBankCode);
            
            $reference = 'TRN_' . strtoupper(uniqid());
            
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => (int) round(floatval($this->amount) * 100),
                'reference' => $reference,
                'status' => 'pending',
                'description' => 'Transfer to ' . ($bank['name'] ?? 'External Bank'),
                'metadata' => json_encode([
                    'account_number' => $this->accountNumber,
                    'bank_code' => $this->selectedBankCode,
                    'bank_name' => $bank['name'] ?? '',
                    'account_name' => $this->resolvedAccountName,
                ])
            ]);

            // Deduct from wallet immediately (funds on hold)
            $wallet->decrement('balance', (int) round(floatval($this->amount) * 100));

            $this->successMessage = 'Transfer initiated successfully! Your funds are on hold and will be delivered within 1-2 hours.';
            $this->showPinModal = false;
            $this->currentStep = 'success';
            $this->dispatch('moneyTransferred');

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Complete the transaction and redirect
     */
    public function handleComplete()
    {
        $this->resetForm();
        $this->redirect('/app/transactions', navigate: true);
    }

    /**
     * Reset the entire form to initial state
     */
    public function resetForm()
    {
        $this->currentStep = 'recipient';
        $this->accountNumber = '';
        $this->selectedBankCode = '';
        $this->resolvedAccountName = '';
        $this->accountResolutionError = '';
        $this->amount = '';
        $this->note = '';
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->isProcessing = false;
        $this->showPinModal = false;
        $this->pinInput = '';
    }

    /**
     * Get the index of a step in the workflow
     * Used for progress indicator styling
     */
    public function getStepIndex($step)
    {
        $steps = ['recipient', 'amount', 'confirm', 'success'];
        $index = array_search($step, $steps);
        return $index !== false ? $index : 0;
    }

    public function render()
    {
        return view('livewire.send-money', [
            'currentStep' => $this->currentStep,
            'banks' => $this->banks,
            'accountNumber' => $this->accountNumber,
            'selectedBankCode' => $this->selectedBankCode,
            'resolvedAccountName' => $this->resolvedAccountName,
        ]);

    }
}


