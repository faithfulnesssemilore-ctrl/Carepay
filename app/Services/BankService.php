<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BankService
{
    private array $ussdCodes = [
        '057' => '*745*{amount}*{account}#',
        '011' => '*901*{amount}*{account}#',
        '058' => '*737*50*{amount}*{account}#',
        '033' => '*770*{amount}*{account}#',
        '044' => '*901*{amount}*{account}#',
        '050' => '*326*{amount}*{account}#',
        '070' => '*919*{amount}*{account}#',
        '035' => '*945*{amount}*{account}#',
        '032' => '*329*{amount}*{account}#',
        '076' => '*322*{amount}*{account}#',
        '023' => '*826*{amount}*{account}#',
        '039' => '*822*{amount}*{account}#',
        '009' => '*894*{amount}*{account}#',
        '007' => '*966*{amount}*{account}#',
        '055' => '*909*{amount}*{account}#',
    ];

    // get all banks - tries Paystack first, falls back to hardcoded list
    public function getAllBanks(): array
    {
        return Cache::remember('paystack_banks', 86400, function () {
            try {
                $secret = config('services.paystack.secret');
                $response = Http::withToken($secret)
                    ->get('https://api.paystack.co/bank', [
                        'country' => 'nigeria',
                        'use_cursor' => false,
                        'perPage' => 100,
                    ]);

                if ($response->successful() && ! empty($response->json('data'))) {
                    return array_map(fn ($b) => [
                        'code' => $b['code'],
                        'name' => $b['name'],
                    ], $response->json('data'));
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch banks from Paystack: '.$e->getMessage());
            }

            // fallback if Paystack is down or key not set
            return [
                ['code' => '057', 'name' => 'Zenith Bank'],
                ['code' => '011', 'name' => 'First Bank'],
                ['code' => '058', 'name' => 'GTBank'],
                ['code' => '033', 'name' => 'Fidelity Bank'],
                ['code' => '044', 'name' => 'Access Bank'],
                ['code' => '050', 'name' => 'Ecobank'],
                ['code' => '070', 'name' => 'UBA'],
                ['code' => '035', 'name' => 'Wema Bank'],
                ['code' => '032', 'name' => 'Union Bank'],
                ['code' => '076', 'name' => 'Polaris Bank'],
                ['code' => '023', 'name' => 'Keystone Bank'],
                ['code' => '039', 'name' => 'Stanbic IBTC'],
                ['code' => '009', 'name' => 'FCMB'],
                ['code' => '007', 'name' => 'Sterling Bank'],
                ['code' => '055', 'name' => 'Opay'],
                ['code' => '090175', 'name' => 'Kuda Bank'],
                ['code' => '999992', 'name' => 'PalmPay'],
                ['code' => '100004', 'name' => 'Moniepoint'],
            ];
        });
    }

    // get a single bank by its code
    public function getBankByCode(string $code): ?array
    {
        foreach ($this->getAllBanks() as $bank) {
            if ($bank['code'] === $code) {
                return $bank;
            }
        }

        return null;
    }

    // resolve the real account name using Paystack API directly
    public function resolveAccountName(string $accountNumber, string $bankCode): ?string
    {
        try {
            $secret = config('services.paystack.secret');
            $response = Http::withToken($secret)
                ->get('https://api.paystack.co/bank/resolve', [
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                ]);

            if ($response->successful()) {
                return $response->json('data.account_name');
            }

            Log::warning('Account resolution failed', [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
                'response' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Account resolution error: '.$e->getMessage());
        }

        return null;
    }

    // generate USSD dial code for bank transfer
    public function getUssdCode(string $bankCode, $amount, string $accountNumber): ?string
    {
        $template = $this->ussdCodes[$bankCode] ?? null;
        if (! $template) {
            return null;
        }

        return str_replace(
            ['{amount}', '{account}'],
            [$amount, $accountNumber],
            $template
        );
    }
}
