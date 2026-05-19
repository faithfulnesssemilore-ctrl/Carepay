<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserLimit;
use App\Services\WalletService;
use App\Services\BankService;
use App\Services\PaymentService;
use Illuminate\Support\Collection;

class SendMoney extends Component
{
    // step flow: recipient -> amount -> confirm -> success
    public string $currentStep = 'recipient';

    // recipient step
    public string $accountNumber    = '';
    public string $selectedBankCode = '';
    public string $selectedBankName = '';
    public array  $banks            = [];
    public string $resolvedAccountName   = '';
    public string $accountResolutionError = '';
    public bool   $isResolvingAccount    = false;

    // recent contacts from past transfers
    public array $recentContacts = [];

    // amount step
    public string $amount        = '';
    public string $note          = '';
    public float  $walletBalance = 0;
    public float  $dailyLimit    = 500000;
    public float  $dailyUsed     = 0;
    public float  $singleLimit   = 100000;

    // pin modal
    public bool   $showPinModal = false;
    public string $pinInput     = '';

    // transfer result
    public string $successMessage   = '';
    public string $errorMessage     = '';
    public string $transferReference = '';
    public bool   $isProcessing     = false;

    protected $rules = [
        'accountNumber'    => 'required|string|digits:10',
        'selectedBankCode' => 'required|string',
        'amount'           => 'required|numeric|min:1',
        'note'             => 'nullable|string|max:500',
        'pinInput'         => 'required|digits:4',
    ];

    protected $messages = [
        'accountNumber.required'    => 'Account number is required.',
        'accountNumber.digits'      => 'Account number must be exactly 10 digits.',
        'selectedBankCode.required' => 'Please select a bank.',
        'amount.required'           => 'Please enter an amount.',
        'amount.min'                => 'Minimum transfer is ₦1.',
        'pinInput.required'         => 'PIN is required.',
        'pinInput.digits'           => 'PIN must be exactly 4 digits.',
    ];

    public function mount(): void
    {
        $user = Auth::user();

        // load banks from Paystack via BankService
        try {
            $bankService  = new BankService();
            $this->banks  = $bankService->getAllBanks();
        } catch (\Exception $e) {
            $this->banks = [];
        }

        // Always include an internal test bank option for development/demo transfers.
        $this->banks = collect($this->banks)
            ->push(['code' => 'CAREPAY', 'name' => 'CarePay'])
            ->push(['code' => 'OPAY', 'name' => 'Opay'])
            ->unique('code')
            ->values()
            ->toArray();

        // load wallet balance in naira
        $this->walletBalance = $user->wallet
            ? round($user->wallet->balance / 100, 2)
            : 0;

        // load limits
        $limits = $user->limits;
        if ($limits) {
            $this->dailyLimit  = (float) $limits->daily_transfer_limit;
            $this->singleLimit = (float) $limits->single_transaction_limit;
        }

        // how much has been sent today in naira
        $this->dailyUsed = round(
            Transaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->whereDate('created_at', today())
                ->sum('amount') / 100,
            2
        );

        $this->loadRecentContacts();
    }

    public function loadRecentContacts(): void
    {
        // get last 6 unique recipients from transaction history
        $this->recentContacts = Transaction::where('user_id', Auth::id())
            ->where('type', 'debit')
            ->whereNotNull('metadata')
            ->latest()
            ->take(20)
            ->get()
            ->filter(function ($tx) {
                $meta = is_array($tx->metadata) ? $tx->metadata : json_decode($tx->metadata, true);
                return !empty($meta['account_number']);
            })
            ->unique(function ($tx) {
                $meta = is_array($tx->metadata) ? $tx->metadata : json_decode($tx->metadata, true);
                return $meta['account_number'] ?? '';
            })
            ->take(6)
            ->map(function ($tx) {
                $meta = is_array($tx->metadata) ? $tx->metadata : json_decode($tx->metadata, true);
                return [
                    'account_number' => $meta['account_number'] ?? '',
                    'bank_code'      => $meta['bank_code']      ?? '',
                    'bank_name'      => $meta['bank_name']      ?? '',
                    'account_name'   => $meta['account_name']   ?? 'Unknown',
                    'initials'       => strtoupper(substr($meta['account_name'] ?? 'U', 0, 1)),
                ];
            })
            ->values()
            ->toArray();
    }

    // auto-resolve account name when bank is selected
    public function updatedSelectedBankCode(string $value): void
    {
        $this->resolvedAccountName    = '';
        $this->accountResolutionError = '';

        // find bank name from the banks list
        foreach ($this->banks as $bank) {
            if (($bank['code'] ?? '') === $value) {
                $this->selectedBankName = $bank['name'] ?? '';
                break;
            }
        }

        if (strlen($this->accountNumber) === 10 && !empty($value)) {
            $this->resolveAccountName();
        }
    }

    // auto-resolve when account number is complete
    public function updatedAccountNumber(string $value): void
    {
        $this->resolvedAccountName    = '';
        $this->accountResolutionError = '';

        if (strlen($value) === 10 && !empty($this->selectedBankCode)) {
            $this->resolveAccountName();
        }
    }

    private function resolveAccountName(): void
    {
        $this->isResolvingAccount = true;

        try {
            // Internal test accounts bypass external resolution.
            if ($this->selectedBankCode === 'CAREPAY' && $this->accountNumber === '9026446100') {
                $this->resolvedAccountName = 'CarePay Test Account';
                return;
            }

            if ($this->selectedBankCode === 'OPAY' && $this->accountNumber === '9026446100') {
                $this->resolvedAccountName = 'Opay Test Account';
                return;
            }

            $bankService = new BankService();
            $name        = $bankService->resolveAccountName(
                $this->accountNumber,
                $this->selectedBankCode
            );

            if ($name) {
                $this->resolvedAccountName = $name;
            } else {
                $this->accountResolutionError = 'Could not find account. Check the number and bank.';
            }
        } catch (\Exception $e) {
            $this->accountResolutionError = 'Error: ' . $e->getMessage();
        } finally {
            $this->isResolvingAccount = false;
        }
    }

    // select a recent contact to prefill the form
    public function selectRecentContact(array $contact): void
    {
        $this->accountNumber    = $contact['account_number'];
        $this->selectedBankCode = $contact['bank_code'];
        $this->selectedBankName = $contact['bank_name'];
        $this->resolvedAccountName = $contact['account_name'];
        $this->accountResolutionError = '';
    }

    // move from recipient step to amount step
    public function proceedToAmount(): void
    {
        $this->errorMessage = '';

        if (strlen($this->accountNumber) !== 10) {
            $this->errorMessage = 'Account number must be exactly 10 digits.';
            return;
        }

        if (empty($this->selectedBankCode)) {
            $this->errorMessage = 'Please select a bank.';
            return;
        }

        if (empty($this->resolvedAccountName)) {
            $this->errorMessage = 'Account not verified. Check the account number and bank.';
            return;
        }

        $this->setStep('amount');
    }

    public function setStep(string $step): void
    {
        $valid = ['recipient', 'amount', 'confirm', 'success'];
        if (in_array($step, $valid)) {
            $this->currentStep  = $step;
            $this->errorMessage = '';
        }
    }

    // validate amount and move to confirm
    public function handleAmountSubmit(): void
    {
        $this->errorMessage = '';

        $amount = floatval($this->amount);

        if ($amount <= 0) {
            $this->errorMessage = 'Please enter a valid amount.';
            return;
        }

        // check balance
        if ($amount > $this->walletBalance) {
            $this->errorMessage = 'Insufficient balance. Your balance is ₦' . number_format($this->walletBalance, 2);
            return;
        }

        // check single transaction limit
        if ($amount > $this->singleLimit) {
            $this->errorMessage = 'Amount exceeds your single transaction limit of ₦' . number_format($this->singleLimit, 2);
            return;
        }

        // check daily limit
        $remaining = $this->dailyLimit - $this->dailyUsed;
        if ($amount > $remaining) {
            $this->errorMessage = 'This would exceed your daily limit. You can send ₦' . number_format($remaining, 2) . ' more today.';
            return;
        }

        $this->setStep('confirm');
    }

    // open PIN modal from confirm step
    public function showPinVerification(): void
    {
        $this->showPinModal = true;
        $this->pinInput     = '';
        $this->errorMessage = '';
    }

    public function closePinModal(): void
    {
        $this->showPinModal = false;
        $this->pinInput     = '';
        $this->errorMessage = '';
    }

    // check PIN then process transfer
    public function verifyPinAndTransfer(): void
    {
        $this->errorMessage = '';

        $user = Auth::user();

        if (!$user->pin) {
            $this->errorMessage = 'You have not set a transaction PIN. Go to Settings to create one.';
            return;
        }

        if (!Hash::check($this->pinInput, $user->pin)) {
            $this->errorMessage = 'Wrong PIN. Try again.';
            return;
        }

        $this->processTransfer();
    }

    public function processTransfer(): void
    {
        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            $user      = Auth::user();
            $wallet    = $user->wallet;
            $amountNaira = floatval($this->amount);
            $amountKobo  = (int) round($amountNaira * 100);

            if (!$wallet) {
                throw new \Exception('Wallet not found. Contact support.');
            }

            if ($wallet->balance < $amountKobo) {
                throw new \Exception('Insufficient balance.');
            }

            // generate unique reference for this transfer
            $reference = 'TRF_' . strtoupper(Str::random(16));

            // try to use Paystack transfer API to send to real bank account
            // if PaymentService or Paystack transfer fails, we record it as pending
            try {
                $paymentService = new PaymentService();

                // first create a transfer recipient on Paystack
                $recipient = $paymentService->createTransferRecipient(
                    name:        $this->resolvedAccountName,
                    accountNumber: $this->accountNumber,
                    bankCode:    $this->selectedBankCode
                );

                $recipientCode = $recipient['recipient_code'] ?? null;

                if ($recipientCode) {
                    // initiate the actual Paystack transfer
                    $transfer = $paymentService->initiateTransfer(
                        amount:        $amountKobo,
                        recipient:     $recipientCode,
                        reference:     $reference,
                        reason:        $this->note ?: 'Transfer from CarePay'
                    );

                    $status = $transfer['status'] ?? 'pending';
                } else {
                    $status = 'pending';
                }
            } catch (\Exception $paystackError) {
                // Paystack failed - still record the transaction but mark as pending
                // admin can manually process it
                $status = 'pending';
            }

            // deduct from wallet regardless - funds are now reserved
            $wallet->decrement('balance', $amountKobo);

            // record the transaction
            Transaction::create([
                'user_id'     => $user->id,
                'type'        => 'debit',
                'amount'      => $amountKobo,
                'reference'   => $reference,
                'status'      => $status,
                'description' => 'Transfer to ' . $this->resolvedAccountName,
                'metadata'    => json_encode([
                    'account_number' => $this->accountNumber,
                    'bank_code'      => $this->selectedBankCode,
                    'bank_name'      => $this->selectedBankName,
                    'account_name'   => $this->resolvedAccountName,
                    'note'           => $this->note,
                ]),
            ]);

            $this->transferReference = $reference;
            $this->showPinModal      = false;
            $this->successMessage    = 'Transfer of ₦' . number_format($amountNaira, 2) . ' to ' . $this->resolvedAccountName . ' is being processed.';

            // update daily used amount
            $this->dailyUsed += $amountNaira;

            // update wallet balance display
            $this->walletBalance = round(($wallet->balance) / 100, 2);

            $this->setStep('success');
            $this->dispatch('toast', type: 'success', message: 'Transfer successful!');

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function handleComplete(): void
    {
        $this->resetForm();
        $this->redirect(route('transactions'), navigate: true);
    }

    public function resetForm(): void
    {
        $this->currentStep           = 'recipient';
        $this->accountNumber         = '';
        $this->selectedBankCode      = '';
        $this->selectedBankName      = '';
        $this->resolvedAccountName   = '';
        $this->accountResolutionError = '';
        $this->amount                = '';
        $this->note                  = '';
        $this->successMessage        = '';
        $this->errorMessage          = '';
        $this->transferReference     = '';
        $this->isProcessing          = false;
        $this->showPinModal          = false;
        $this->pinInput              = '';
    }

    public function getStepIndex(string $step): int
    {
        $steps = ['recipient', 'amount', 'confirm', 'success'];
        $index = array_search($step, $steps);
        return $index !== false ? (int) $index : 0;
    }

    public function render()
    {
        return view('livewire.send-money', [
            'currentStep'          => $this->currentStep,
            'banks'                => $this->banks,
            'recentContacts'       => $this->recentContacts,
            'walletBalance'        => $this->walletBalance,
            'dailyLimit'           => $this->dailyLimit,
            'dailyUsed'            => $this->dailyUsed,
            'singleLimit'          => $this->singleLimit,
            'resolvedAccountName'  => $this->resolvedAccountName,
            'accountResolutionError' => $this->accountResolutionError,
            'isResolvingAccount'   => $this->isResolvingAccount,
            'successMessage'       => $this->successMessage,
            'errorMessage'         => $this->errorMessage,
            'transferReference'    => $this->transferReference,
            'showPinModal'         => $this->showPinModal,
            'searchResults'        => [], // kept for blade compatibility
        ]);
    }
}