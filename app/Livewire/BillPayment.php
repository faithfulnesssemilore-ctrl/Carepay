<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\VtpassService;
use App\Services\WalletService;

class BillPayment extends Component
{
    // step flow: category -> provider -> details -> confirm -> success
    public string $currentStep    = 'category';
    public string $selectedCategory = '';
    public string $selectedProvider = '';

    // Nigerian network providers
    public array $airtimeProviders = [
        ['id' => 'mtn',       'name' => 'MTN',      'color' => '#ffcc00'],
        ['id' => 'airtel',    'name' => 'Airtel',   'color' => '#ef4444'],
        ['id' => 'glo',       'name' => 'Glo',      'color' => '#22c55e'],
        ['id' => 'etisalat',  'name' => '9mobile',  'color' => '#22c55e'],
    ];

    public array $dataProviders = [
        ['id' => 'mtn-data',      'name' => 'MTN Data',    'color' => '#ffcc00'],
        ['id' => 'airtel-data',   'name' => 'Airtel Data', 'color' => '#ef4444'],
        ['id' => 'glo-data',      'name' => 'Glo Data',    'color' => '#22c55e'],
        ['id' => 'etisalat-data', 'name' => '9mobile Data','color' => '#22c55e'],
    ];

    public array $electricityProviders = [
        ['id' => 'ikeja-electric', 'name' => 'Ikeja Electric (IKEDC)'],
        ['id' => 'eko-electric',   'name' => 'Eko Electric (EKEDC)'],
        ['id' => 'kano-electric',  'name' => 'Kano Electric (KEDCO)'],
        ['id' => 'phed',           'name' => 'Port Harcourt Electric (PHED)'],
        ['id' => 'eedc',           'name' => 'Enugu Electric (EEDC)'],
        ['id' => 'ibedc',          'name' => 'Ibadan Electric (IBEDC)'],
        ['id' => 'aedc',           'name' => 'Abuja Electric (AEDC)'],
        ['id' => 'jos-electric',   'name' => 'Jos Electric (JED)'],
        ['id' => 'kaduna-electric','name' => 'Kaduna Electric'],
        ['id' => 'benin-electric', 'name' => 'Benin Electric (BEDC)'],
    ];

    public array $cableProviders = [
        ['id' => 'dstv',      'name' => 'DSTV'],
        ['id' => 'gotv',      'name' => 'GOtv'],
        ['id' => 'startimes', 'name' => 'Startimes'],
    ];

    // form fields
    public string $phone       = '';
    public string $meterNumber = '';
    public string $meterType   = 'prepaid'; // prepaid or postpaid
    public string $smartcard   = '';
    public string $amount      = '';
    public string $dataPlan    = '';

    // available data plans (loaded when provider selected)
    public array $dataPlans = [];

    // result
    public string $successMessage   = '';
    public string $errorMessage     = '';
    public string $referenceNumber  = '';
    public string $token            = ''; // for electricity
    public bool   $isProcessing     = false;
    public float  $currentBalance   = 0;
     public $billCategories = [];
    // recent bills from transaction history
    public $recentBills;

    public function mount(): void
    {
        $this->recentBills = collect();
        $this->loadBalance();
        $this->loadRecentBills();
    }

    public function loadBalance(): void
    {
        $wallet = Auth::user()->wallet;
        $this->currentBalance = $wallet ? round($wallet->balance / 100, 2) : 0;
    }

    public function loadRecentBills(): void
    {
        $this->recentBills = Transaction::where('user_id', Auth::id())
            ->where('type', 'debit')
            ->whereNotNull('description')
            ->where('description', 'like', '%bill%')
            ->orWhere('description', 'like', '%airtime%')
            ->orWhere('description', 'like', '%data%')
            ->orWhere('description', 'like', '%electric%')
            ->orWhere('description', 'like', '%cable%')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();
    }

    // move to provider step
    public function selectCategory(string $category): void
    {
        $valid = ['airtime', 'data', 'electricity', 'cable'];
        if (!in_array($category, $valid)) return;

        $this->selectedCategory = $category;
        $this->selectedProvider = '';
        $this->resetFormFields();
        $this->currentStep = 'provider';
    }

    // move to details step
    public function selectProvider(string $provider): void
    {
        $this->selectedProvider = $provider;
        $this->errorMessage     = '';

        // load data plans if data category
        if ($this->selectedCategory === 'data') {
            $this->loadDataPlans();
        }

        $this->currentStep = 'details';
    }

    private function loadDataPlans(): void
    {
        // common data plans per network - in real app fetch from VTPass variations API
        $plans = [
            'mtn-data' => [
                ['code' => 'mtn-10mb-300', 'name' => '10MB - ₦300 (1 day)'],
                ['code' => 'mtn-750mb-500', 'name' => '750MB - ₦500 (2 weeks)'],
                ['code' => 'mtn-1gb-1000', 'name' => '1GB - ₦1,000 (30 days)'],
                ['code' => 'mtn-2gb-1200', 'name' => '2GB - ₦1,200 (30 days)'],
                ['code' => 'mtn-5gb-2500', 'name' => '5GB - ₦2,500 (30 days)'],
                ['code' => 'mtn-10gb-5000','name' => '10GB - ₦5,000 (30 days)'],
            ],
            'airtel-data' => [
                ['code' => 'airtel-100mb-200', 'name' => '100MB - ₦200 (3 days)'],
                ['code' => 'airtel-1gb-1000',  'name' => '1GB - ₦1,000 (30 days)'],
                ['code' => 'airtel-2gb-2000',  'name' => '2GB - ₦2,000 (30 days)'],
                ['code' => 'airtel-5gb-3500',  'name' => '5GB - ₦3,500 (30 days)'],
            ],
            'glo-data' => [
                ['code' => 'glo-1gb-1000',  'name' => '1GB - ₦1,000 (30 days)'],
                ['code' => 'glo-2gb-1500',  'name' => '2GB - ₦1,500 (30 days)'],
                ['code' => 'glo-5gb-2500',  'name' => '5GB - ₦2,500 (30 days)'],
                ['code' => 'glo-10gb-5000', 'name' => '10GB - ₦5,000 (30 days)'],
            ],
            'etisalat-data' => [
                ['code' => '9mobile-1gb-1000',  'name' => '1GB - ₦1,000 (30 days)'],
                ['code' => '9mobile-2gb-2000',  'name' => '2GB - ₦2,000 (30 days)'],
                ['code' => '9mobile-5gb-4000',  'name' => '5GB - ₦4,000 (30 days)'],
            ],
        ];

        $this->dataPlans = $plans[$this->selectedProvider] ?? [];
    }

    // validate details and move to confirm
    public function submitDetails(): void
    {
        $this->errorMessage = '';

        // validate based on category
        if ($this->selectedCategory === 'airtime') {
            if (strlen($this->phone) !== 11 || !str_starts_with($this->phone, '0')) {
                $this->errorMessage = 'Enter a valid 11-digit Nigerian phone number starting with 0.';
                return;
            }
            if (floatval($this->amount) < 50) {
                $this->errorMessage = 'Minimum airtime is ₦50.';
                return;
            }
        }

        if ($this->selectedCategory === 'data') {
            if (strlen($this->phone) !== 11 || !str_starts_with($this->phone, '0')) {
                $this->errorMessage = 'Enter a valid 11-digit Nigerian phone number.';
                return;
            }
            if (empty($this->dataPlan)) {
                $this->errorMessage = 'Please select a data plan.';
                return;
            }
        }

        if ($this->selectedCategory === 'electricity') {
            if (strlen($this->meterNumber) < 11 || strlen($this->meterNumber) > 13) {
                $this->errorMessage = 'Enter a valid meter number (11-13 digits).';
                return;
            }
            if (floatval($this->amount) < 500) {
                $this->errorMessage = 'Minimum electricity payment is ₦500.';
                return;
            }
        }

        if ($this->selectedCategory === 'cable') {
            if (empty($this->smartcard)) {
                $this->errorMessage = 'Enter your smartcard number.';
                return;
            }
        }

        // check balance
        $amountNaira = floatval($this->amount);
        if ($amountNaira > $this->currentBalance) {
            $this->errorMessage = 'Insufficient balance. Your balance is ₦' . number_format($this->currentBalance, 2);
            return;
        }

        $this->currentStep = 'confirm';
    }

    // process the actual payment
    public function confirmPayment(): void
    {
        $this->isProcessing = true;
        $this->errorMessage = '';

        try {
            $user         = Auth::user();
            $amountNaira  = floatval($this->amount);
            $amountKobo   = (int) round($amountNaira * 100);
            $walletService = new WalletService();
            $vtpass        = new VtpassService();
            $requestId     = $vtpass->generateRequestId();

            // check balance one more time before deducting
            $wallet = $user->wallet;
            if (!$wallet || $wallet->balance < $amountKobo) {
                throw new \Exception('Insufficient balance.');
            }

            // build VTPass payload based on category
            $payload = $this->buildVtpassPayload($requestId, $amountNaira);

            // debit wallet BEFORE calling VTPass
            $reference   = 'BILL_' . strtoupper(\Illuminate\Support\Str::random(12));
            $description = $this->buildDescription();

            $walletService->debit(
                userId:      $user->id,
                amountKobo:  $amountKobo,
                reference:   $reference,
                description: $description
            );

            // call VTPass
            $result = $vtpass->processPayment(
                serviceId:      $this->selectedProvider,
                amount:         $amountNaira,
                phone:          $this->phone ?: $user->phone,
                additionalData: $payload
            );

            $vtpassCode = $result['code'] ?? '999';

            // VTPass success codes: 000 = success
            if ($vtpassCode !== '000') {
                // VTPass failed - refund the wallet
                $refundRef = 'REFUND_' . $reference;
                $walletService->credit(
                    userId:      $user->id,
                    amountKobo:  $amountKobo,
                    reference:   $refundRef,
                    description: 'Refund: ' . $description
                );

                $errorMsg = $result['response_description'] ?? $result['message'] ?? 'Payment failed. Please try again.';
                throw new \Exception($errorMsg);
            }

            // success - update transaction to completed
            Transaction::where('reference', $reference)
                ->update(['status' => 'success']);

            // extract token for electricity
            $this->token = $result['Token'] ??
                $result['token'] ??
                $result['content']['transactions']['token'] ?? '';

            $this->referenceNumber = $requestId;
            $this->currentBalance  = round(($wallet->balance - $amountKobo) / 100, 2);
            $this->currentStep     = 'success';
            $this->successMessage  = 'Payment successful!';

            $this->dispatch('toast', type: 'success', message: 'Bill payment successful!');

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    private function buildVtpassPayload(string $requestId, float $amount): array
    {
        $base = ['request_id' => $requestId];

        return match ($this->selectedCategory) {
            'electricity' => array_merge($base, [
                'billersCode' => $this->meterNumber,
                'variation_code' => $this->meterType, // prepaid or postpaid
                'amount' => $amount,
                'phone'  => $this->phone ?: Auth::user()->phone ?? '',
            ]),
            'cable' => array_merge($base, [
                'billersCode'    => $this->smartcard,
                'variation_code' => $this->dataPlan,
                'phone'          => Auth::user()->phone ?? '',
            ]),
            'data' => array_merge($base, [
                'billersCode'    => $this->phone,
                'variation_code' => $this->dataPlan,
                'phone'          => $this->phone,
            ]),
            default => $base, // airtime just needs phone and amount
        };
    }

    private function buildDescription(): string
    {
        return match ($this->selectedCategory) {
            'airtime'     => 'Airtime - ' . $this->selectedProvider . ' - ' . $this->phone,
            'data'        => 'Data - ' . $this->selectedProvider . ' - ' . $this->phone,
            'electricity' => 'Electricity - ' . $this->selectedProvider . ' - ' . $this->meterNumber,
            'cable'       => 'Cable TV - ' . $this->selectedProvider . ' - ' . $this->smartcard,
            default       => 'Bill payment - ' . $this->selectedProvider,
        };
    }

    public function goBack(): void
    {
        $this->errorMessage = '';

        $this->currentStep = match ($this->currentStep) {
            'provider' => 'category',
            'details'  => 'provider',
            'confirm'  => 'details',
            default    => 'category',
        };
    }

    public function resetForm(): void
    {
        $this->currentStep      = 'category';
        $this->selectedCategory = '';
        $this->selectedProvider = '';
        $this->resetFormFields();
        $this->successMessage   = '';
        $this->errorMessage     = '';
        $this->referenceNumber  = '';
        $this->token            = '';
        $this->loadBalance();
    }

    private function resetFormFields(): void
    {
        $this->phone       = '';
        $this->meterNumber = '';
        $this->meterType   = 'prepaid';
        $this->smartcard   = '';
        $this->amount      = '';
        $this->dataPlan    = '';
        $this->dataPlans   = [];
    }

    public function render()
    {
        return view('livewire.bill-payment', [
            'currentStep'          => $this->currentStep,
            'selectedCategory'     => $this->selectedCategory,
            'selectedProvider'     => $this->selectedProvider,
            'currentBalance'       => $this->currentBalance,
            'airtimeProviders'     => $this->airtimeProviders,
            'dataProviders'        => $this->dataProviders,
            'electricityProviders' => $this->electricityProviders,
            'cableProviders'       => $this->cableProviders,
            'dataPlans'            => $this->dataPlans,
            'recentBills'          => $this->recentBills,
            'referenceNumber'      => $this->referenceNumber,
            'token'                => $this->token,
            'successMessage'       => $this->successMessage,
            'errorMessage'         => $this->errorMessage,
            'isProcessing'         => $this->isProcessing,
        ]);
    }
}
