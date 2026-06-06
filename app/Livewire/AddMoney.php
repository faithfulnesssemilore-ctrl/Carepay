<?php

namespace App\Livewire;

use App\Models\Wallet;
use App\Services\BankService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * AddMoney Livewire Component
 * Multi-step deposit workflow with multiple payment methods
 * Methods: bank-transfer, cash, card, ussd
 */
class AddMoney extends Component
{
    public $step = 'select';

    public $selectedMethod = null;

    public $accountNumber = '';

    public $accountName = '';

    public $bankName = '';

    public $hasVirtualAccount = false;

    public $cardAmount = '';

    public $selectedCard = '';

    public $selectedBank = '';

    public $ussdAmount = '';

    public $ussdCode = '';

    public $banks = [];

    public $copiedField = null;

    public $successMessage = '';

    public $errorMessage = '';

    public $isProcessing = false;

    public $currentBalance = 0;

    public $virtualAccount;

    public function mount()
    {
        $this->loadBalance();
        $this->loadVirtualAccount();

        $this->banks = (new BankService)->getAllBanks();
    }

    public function loadVirtualAccount()
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $va = $user->virtualAccount;

        if (! $va) {
            return;
        }

        $this->accountNumber = $va->account_number;
        $this->accountName = $va->account_name;
        $this->bankName = $va->bank_name;
        $this->hasVirtualAccount = true;
        $this->virtualAccount = $va;
    }

    public function loadBalance()
    {
        $user = Auth::user();

        if (! $user || ! $user->wallet) {
            return;
        }

        // Wallet cast exposes balance in naira
        $this->currentBalance = round($user->wallet->balance, 2);
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
     * Update USSD code when bank is selected
     */
    public function updatedSelectedBank($value)
    {
        $this->ussdCode = '';
        if (empty($value) || empty($this->ussdAmount)) {
            return;
        }
        $this->generateUssdCode();
    }

    /**
     * Update USSD code when amount is entered
     */
    public function updatedUssdAmount($value)
    {
        $this->ussdCode = '';
        if (empty($value) || empty($this->selectedBank)) {
            return;
        }
        $this->generateUssdCode();
    }

    /**
     * Generate USSD code for the selected bank and amount
     */
    private function generateUssdCode()
    {
        if (empty($this->selectedBank) || empty($this->ussdAmount)) {
            return;
        }

        try {
            $bankService = new BankService;
            $ussdCode = $bankService->getUssdCode(
                $this->selectedBank,
                (int) $this->ussdAmount,
                $this->accountNumber
            );

            if ($ussdCode) {
                $this->ussdCode = $ussdCode;
            } else {
                $this->errorMessage = 'USSD code not available for this bank.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Error generating USSD code: '.$e->getMessage();
        }
    }

    public function handleCopy($text, $field)
    {
        $this->copiedField = $field;
        $this->dispatch('copy-to-clipboard', ['text' => $text]);
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
            if (floatval($this->cardAmount) < 100) {
                $this->errorMessage = 'Minimum deposit is ₦100.';

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
        return $this->processDeposit();
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

            if ($this->selectedMethod === 'card') {
                // Handle card payment with Paystack
                return $this->processCardPayment();
            }

            $amount = (float) $this->cardAmount;

            // Log the deposit request for non-card methods
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
     * Process card payment with Paystack
     */
    private function processCardPayment()
    {
        $user = Auth::user();
        $amountInKobo = (int) ($this->cardAmount * 100);

        // Minimum ₦100
        if ($amountInKobo < 10000) {
            throw new \Exception('Minimum deposit amount is ₦100.');
        }

        // Generate reference
        $reference = 'DEP_'.strtoupper(Str::random(16));

        // Store pending deposit in database
        DB::table('deposits')->insert([
            'user_id' => $user->id,
            'amount' => $this->cardAmount,
            'method' => 'card',
            'status' => 'pending',
            'reference_id' => $reference,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $paymentService = new \App\Services\PaymentService;
            $response = $paymentService->initialize(
                email: $user->email,
                amount: $amountInKobo,
                reference: $reference,
                callbackUrl: route('payment.callback')
            );

            if (empty($response['authorization_url'])) {
                throw new \Exception('Failed to initialize payment.');
            }

            $this->dispatch('paystack:init', [
                'publicKey' => config('services.paystack.public'),
                'email' => $user->email,
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callbackUrl' => route('payment.callback'),
            ]);

            return;

        } catch (\Exception $e) {
            DB::table('deposits')
                ->where('reference_id', $reference)
                ->delete();

            throw $e;
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
            'accountNumber' => $this->accountNumber,
            'accountName' => $this->accountName,
            'bankName' => $this->bankName,
            'hasVirtualAccount' => $this->hasVirtualAccount,
            'banks' => $this->banks,
            'ussdAmount' => $this->ussdAmount,
            'ussdCode' => $this->ussdCode,
        ]);
    }
}
