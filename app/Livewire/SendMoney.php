<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\TransferService;

/**
 * SendMoney Livewire Component
 * Multi-step money transfer workflow with recipient selection, amount input, method selection, and confirmation
 */
class SendMoney extends Component
{
    // ==================== Multi-Step Form State ====================
    public $currentStep = 'recipient'; // recipient | amount | method | confirm | success
    
    // Recipient Step
    public $searchQuery = '';
    public $selectedRecipient = null;
    public $recentContacts = [];
    
    // Amount Step
    public $amount = '';
    public $note = '';
    
    // Method Step
    public $method = 'wallet'; // wallet | bank | card
    
    // Status
    public $successMessage = '';
    public $errorMessage = '';
    public $isProcessing = false;

    // ==================== Validation Rules ====================
    protected $rules = [
        'selectedRecipient' => 'required|array',
        'amount' => 'required|numeric|min:0.01',
        'method' => 'required|in:wallet,bank,card',
        'note' => 'nullable|string|max:500'
    ];

    protected $messages = [
        'selectedRecipient.required' => 'Please select a recipient.',
        'amount.required' => 'Please enter an amount.',
        'amount.min' => 'Amount must be greater than $0.',
        'method.required' => 'Please select a transfer method.',
    ];
 protected $listeners = ['pinVerified'];

public function pinVerified($action, $payload)
{
    if ($action !== 'transfer') return;

    $this->processTransfer($payload);
}
    /**
     * Initialize component - load recent contacts
     */
    public function mount()
    {
        $this->loadRecentContacts();
    }

    /**
     * Load recent contacts for the current user
     */
    public function loadRecentContacts()
    {
        $user = Auth::user();
        
        // Get users that current user has recently transferred to
        // For now, returning sample data - replace with actual database query
        $this->recentContacts = [
            [
                'id' => 1,
                'name' => 'Sarah Wilson',
                'email' => 'sarah@example.com',
                'avatar' => 'SW'
            ],
            [
                'id' => 2,
                'name' => 'Michael Brown',
                'email' => 'michael@example.com',
                'avatar' => 'MB'
            ],
            [
                'id' => 3,
                'name' => 'Jennifer Lee',
                'email' => 'jennifer@example.com',
                'avatar' => 'JL'
            ],
            [
                'id' => 4,
                'name' => 'David Chen',
                'email' => 'david@example.com',
                'avatar' => 'DC'
            ],
        ];

        // TODO: Replace with actual query:
        // $this->recentContacts = $user->sentTransfers()
        //     ->with('recipient')
        //     ->latest()
        //     ->distinctBy('recipient_id')
        //     ->take(4)
        //     ->get()
        //     ->map(fn($transfer) => [
        //         'id' => $transfer->recipient->id,
        //         'name' => $transfer->recipient->name,
        //         'email' => $transfer->recipient->email,
        //     ])
        //     ->toArray();
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
        $contact = collect($this->recentContacts)->firstWhere('id', $contactId);
        
        if ($contact) {
            $this->selectedRecipient = $contact;
            $this->setStep('amount');
        } else {
            $this->errorMessage = 'Invalid recipient selected.';
        }
    }

    /**
     * Validate amount and move to method selection step
     */
    public function handleAmountSubmit()
    {
        // Validate amount
        if (empty($this->amount) || floatval($this->amount) <= 0) {
            $this->errorMessage = 'Please enter a valid amount greater than $0.';
            return;
        }

        // Check user balance (example)
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < floatval($this->amount)) {
            $this->errorMessage = 'Insufficient balance for this transfer.';
            return;
        }

        $this->errorMessage = '';
        $this->setStep('method');
    }

    /**
     * Select transfer method and move to confirmation
     */
    public function setMethod($selectedMethod)
    {
        $validMethods = ['wallet', 'bank', 'card'];
        
        if (in_array($selectedMethod, $validMethods)) {
            $this->method = $selectedMethod;
            $this->setStep('confirm');
        } else {
            $this->errorMessage = 'Invalid transfer method selected.';
        }
    }

    /**
     * Handle transfer confirmation and process payment
     */
    public function handleConfirm()
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

            // Validate sufficient balance
            if ($wallet->balance < floatval($this->amount)) {
                throw new \Exception('Insufficient balance for this transfer.');
            }

            // Process the transfer through the service
            $transferService = new TransferService();
            
            $result = $transferService->transfer(
                user: $user,
                recipientId: $this->selectedRecipient['id'] ?? null,
                amount: (float) $this->amount,
                method: $this->method,
                description: $this->note,
            );

            if ($result['success']) {
                $this->successMessage = 'Transfer completed successfully!';
                $this->currentStep = 'success';
                $this->dispatch('moneyTransferred');
            } else {
                throw new \Exception($result['message'] ?? 'Transfer failed.');
            }

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


    public function processTransfer($payload)
{
    $amount = $payload['amount'];
    $recipient = $payload['recipient'];

    // your wallet debit logic here
}

    /**
     * Reset the entire form to initial state
     */
    public function resetForm()
    {
        $this->currentStep = 'recipient';
        $this->searchQuery = '';
        $this->selectedRecipient = null;
        $this->amount = '';
        $this->note = '';
        $this->method = 'wallet';
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->isProcessing = false;
        $this->loadRecentContacts();
    }

    /**
     * Get the index of a step in the workflow
     * Used for progress indicator styling
     * 
     * @param string $step Step identifier
     * @return int Step index (0-4)
     */
    public function getStepIndex($step)
    {
        $steps = ['recipient', 'amount', 'method', 'confirm', 'success'];
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


