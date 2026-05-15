<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BankService
{
    // Nigerian banks with their codes for account name resolution
    private $banks = [
        ['code' => '007', 'name' => 'Zenith Bank', 'ussd' => '*966*{amount}*{account}#'],
        ['code' => '009', 'name' => 'First Bank', 'ussd' => '*894*{amount}*{account}#'],
        ['code' => '010', 'name' => 'GTBank', 'ussd' => '*737*50*{amount}*{account}#'],
        ['code' => '011', 'name' => 'First City Monument Bank', 'ussd' => '*901*{amount}*{account}#'],
        ['code' => '012', 'name' => 'UBA', 'ussd' => '*919*{amount}*{account}#'],
        ['code' => '014', 'name' => 'African Bank', 'ussd' => '*991*{amount}*{account}#'],
        ['code' => '015', 'name' => 'Standard Chartered Bank', 'ussd' => ''],
        ['code' => '017', 'name' => 'Guaranty Trust Bank', 'ussd' => ''],
        ['code' => '019', 'name' => 'Zenith Bank', 'ussd' => '*966*{amount}*{account}#'],
        ['code' => '022', 'name' => 'Union Bank of Nigeria', 'ussd' => '*826*{amount}*{account}#'],
        ['code' => '023', 'name' => 'United Bank for Africa', 'ussd' => '*919*{amount}*{account}#'],
        ['code' => '024', 'name' => 'Access Bank', 'ussd' => '*901*{amount}*{account}#'],
        ['code' => '025', 'name' => 'Ecobank', 'ussd' => '*326*{amount}*{account}#'],
        ['code' => '026', 'name' => 'Diamondbank', 'ussd' => '*710*{amount}*{account}#'],
        ['code' => '027', 'name' => 'Intercontinental Bank', 'ussd' => ''],
        ['code' => '028', 'name' => 'Verifone', 'ussd' => ''],
        ['code' => '030', 'name' => 'HeavensBank', 'ussd' => ''],
        ['code' => '031', 'name' => 'Jaiz Bank', 'ussd' => '*915*{amount}*{account}#'],
        ['code' => '032', 'name' => 'FCMB', 'ussd' => '*901*{amount}*{account}#'],
        ['code' => '033', 'name' => 'Fidelity Bank', 'ussd' => '*770*{amount}*{account}#'],
        ['code' => '035', 'name' => 'Wema Bank', 'ussd' => '*945*{amount}*{account}#'],
        ['code' => '036', 'name' => 'Access Bank', 'ussd' => '*901*{amount}*{account}#'],
        ['code' => '037', 'name' => 'Guaranty Trust Bank', 'ussd' => '*737*{amount}*{account}#'],
        ['code' => '040', 'name' => 'Zenith Bank Plc', 'ussd' => '*966*{amount}*{account}#'],
        ['code' => '044', 'name' => 'Afribank', 'ussd' => ''],
        ['code' => '045', 'name' => 'Citibank Nigeria', 'ussd' => ''],
        ['code' => '046', 'name' => 'Globus Bank', 'ussd' => ''],
        ['code' => '050', 'name' => 'Ecobank Nigeria', 'ussd' => '*326*{amount}*{account}#'],
        ['code' => '051', 'name' => 'Suntrust Bank', 'ussd' => '*907*{amount}*{account}#'],
        ['code' => '052', 'name' => 'Cobiz Bank', 'ussd' => ''],
        ['code' => '053', 'name' => 'Access Bank', 'ussd' => '*901*{amount}*{account}#'],
        ['code' => '054', 'name' => 'IEBank', 'ussd' => ''],
        ['code' => '055', 'name' => 'Stanbic IBTC Bank', 'ussd' => '*909*{amount}*{account}#'],
        ['code' => '056', 'name' => 'Noor Islamic Bank', 'ussd' => '*997*{amount}*{account}#'],
        ['code' => '057', 'name' => 'Providus Bank', 'ussd' => ''],
        ['code' => '058', 'name' => 'GTBank', 'ussd' => '*737*50*{amount}*{account}#'],
        ['code' => '059', 'name' => 'UBA', 'ussd' => '*919*{amount}*{account}#'],
        ['code' => '060', 'name' => 'Fidelity Bank', 'ussd' => '*770*{amount}*{account}#'],
        ['code' => '061', 'name' => 'Standard Chartered Bank', 'ussd' => ''],
        ['code' => '062', 'name' => 'Zenith Bank', 'ussd' => '*966*{amount}*{account}#'],
        ['code' => '063', 'name' => 'Wema Bank', 'ussd' => '*945*{amount}*{account}#'],
        ['code' => '064', 'name' => 'Accion Microfinance Bank', 'ussd' => ''],
        ['code' => '065', 'name' => 'Copy Money Bank', 'ussd' => ''],
        ['code' => '066', 'name' => 'First City Monument Bank', 'ussd' => '*901*{amount}*{account}#'],
        ['code' => '067', 'name' => 'Access Bank', 'ussd' => '*901*{amount}*{account}#'],
        ['code' => '068', 'name' => 'GTCO', 'ussd' => '*737*50*{amount}*{account}#'],
        ['code' => '069', 'name' => 'IBTC', 'ussd' => '*909*{amount}*{account}#'],
        ['code' => '070', 'name' => 'UBA', 'ussd' => '*919*{amount}*{account}#'],
        ['code' => '501', 'name' => 'Fortis Mobile Money', 'ussd' => ''],
        ['code' => '502', 'name' => 'mPharma', 'ussd' => ''],
        ['code' => '503', 'name' => 'Eartholeum Limited', 'ussd' => ''],
    ];

    // Get all banks
    public function getAllBanks()
    {
        return array_map(fn($bank) => [
            'code' => $bank['code'],
            'name' => $bank['name']
        ], $this->banks);
    }

    // Get bank by code
    public function getBankByCode($code)
    {
        foreach ($this->banks as $bank) {
            if ($bank['code'] === $code) {
                return $bank;
            }
        }
        return null;
    }

    // Get USSD code for a bank
    public function getUssdCode($bankCode, $amount, $accountNumber)
    {
        $bank = $this->getBankByCode($bankCode);
        if (!$bank || empty($bank['ussd'])) {
            return null;
        }

        return str_replace(
            ['{amount}', '{account}'],
            [$amount, $accountNumber],
            $bank['ussd']
        );
    }

    // Resolve account name using Paystack API or other provider
    public function resolveAccountName($accountNumber, $bankCode)
    {
        try {
            $response = Http::withToken(config('services.paystack.secret'))
                ->get('https://api.paystack.co/bank/resolve', [
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                ]);

            if ($response->successful() && isset($response['data']['account_name'])) {
                return $response['data']['account_name'];
            }
        } catch (\Exception $e) {
            \Log::error('Account name resolution failed: ' . $e->getMessage());
        }

        return null;
    }
}
