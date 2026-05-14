<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Transaction;
use App\Services\WalletService;

/**
 * SendMoney Livewire Component
 * Multi-step wallet-to-wallet transfer: recipient -> amount -> confirm -> success
 */
class SendMoney extends Component
{
    // ==================== Multi-Step Form State ====================
    public $currentStep = 'recipient'; // recipient | amount | confirm | success
    
    // Recipient Step
    public $searchQuery = '';
    public $searchResults = [];
    public $selectedRecipient = null;
    public $recentContacts = [];
    
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
        'selectedRecipient' => 'required|array',
        'amount' => 'required|numeric|min:0.01',
        'note' => 'nullable|string|max:500',
        'pinInput' => 'required|numeric|digits:4'
    ];

    protected $messages = [
        'selectedRecipient.required' => 'Please select a recipient.',
        'amount.required' => 'Please enter an amount.',
        'amount.min' => 'Amount must be greater than 0.',
        'pinInput.required' => 'PIN is required.',
        'pinInput.digits' => 'PIN must be exactly 4 digits.',
    ];

    protected $listeners = ['pinVerified'];
    /**
     * Initialize component - load recent contacts and wallet limits
     */
    public function mount()
    {
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
        
        $this->loadRecentContacts();
    }

    /**
     * Load real recent contacts from transaction history
     */
    public function loadRecentContacts()
    {
        $user = Auth::user();
        
        // Get unique recipients from recent transactions (last 30 days)
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->where('created_at', '>=', now()->subDays(30))
            ->with('recipient:id,first_name,last_name,email')
            ->latest()
            ->get();
        
        // Map to unique recipients with initials
        $seenIds = [];
        $this->recentContacts = [];
        
        foreach ($recentTransactions as $transaction) {
            if ($transaction->recipient && !in_array($transaction->recipient->id, $seenIds)) {
                $seenIds[] = $transaction->recipient->id;
                $name = $transaction->recipient->first_name . ' ' . $transaction->recipient->last_name;
                $this->recentContacts[] = [
                    'id' => $transaction->recipient->id,
                    'name' => trim($name),
                    'email' => $transaction->recipient->email,
                ];
                
                if (count($this->recentContacts) >= 4) break;
            }
        }
    }
    
    /**
     * Search for recipient by username or email
     */
    public function searchRecipient()
    {
        $this->errorMessage = '';
        
        if (empty($this->searchQuery)) {
            $this->searchResults = [];
            return;
        }
        
        $user = Auth::user();
        
        // Search by username (case-insensitive) or email
        $this->searchResults = User::where('id', '!=', $user->id)
            ->where(function ($query) {
                $query->where('username', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
            })
            ->select('id', 'first_name', 'last_name', 'email', 'username')
            ->limit(5)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => trim($u->first_name . ' ' . $u->last_name),
                'email' => $u->email,
                'username' => $u->username,
            ])
            ->toArray();
    }

    /**
     * Set the current step in the workflow
     */
    public function setStep($step)
    {
        $validSteps = ['recipient', 'amount', 'method', 'confirm', 'success'];
        
        if (in_array($step, $validSteps)) {
            $this->currentStep = $step;
            $this->errorMessage = '';
        }
    }

    /**
     * Select a recipient and move to amount step
     */
    public function selectRecipient($contactId)
    {
        // Try recent contacts first
        $contact = collect($this->recentContacts)->firstWhere('id', $contactId);
        
        // If not found, try search results
        if (!$contact) {
            $contact = collect($this->searchResults)->firstWhere('id', $contactId);
        }
        
        if ($contact) {
            $this->selectedRecipient = $contact;
            $this->searchQuery = '';
            $this->searchResults = [];
            $this->setStep('amount');
        } else {
            $this->errorMessage = 'Invalid recipient selected.';
        }
    }
    
    /**
     * Confirm recipient selection
     */
    public function confirmRecipient($contactId)
    {
        $this->selectRecipient($contactId);
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
     * Process the actual transfer through WalletService
     */
    public function processTransfer()
    {
        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            // Final validation
            $this->validate();

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

            // Process the transfer through WalletService with locking and limits
            $walletService = new WalletService();
            
            $result = $walletService->transfer(
                senderId: $user->id,
                recipientId: $this->selectedRecipient['id'] ?? null,
                amountInKobo: (int) round(floatval($this->amount) * 100),
                description: $this->note ?: 'Transfer to ' . $this->selectedRecipient['name']
            );

            $this->successMessage = 'Transfer completed successfully!';
            $this->showPinModal = false;
            $this->currentStep = 'success';
            $this->dispatch('moneyTransferred');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = 'Validation error: ' . collect($e->errors())->flatten()->first();
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
        // Reset form and return to initial step
        $this->resetForm();
        $this->redirect('/app/transactions', navigate: true);
    }

    /**
     * Reset the entire form to initial state
     */
    public function resetForm()
    {
        $this->currentStep = 'recipient';
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->selectedRecipient = null;
        $this->amount = '';
        $this->note = '';
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->isProcessing = false;
        $this->showPinModal = false;
        $this->pinInput = '';
        $this->loadRecentContacts();
    }

    /**
     * Get the index of a step in the workflow
     * Used for progress indicator styling
     * 
     * @param string $step Step identifier
     * @return int Step index (0-3)
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
            'selectedRecipient' => $this->selectedRecipient,
            'amount' => $this->amount,
            'note' => $this->note,
            'method' => $this->method,
            'recentContacts' => $this->recentContacts,
            'searchQuery' => $this->searchQuery,
        ]);
    }
}


