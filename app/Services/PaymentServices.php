<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected string $secret;
    protected string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secret = config('services.paystack.secret');
    }

    // initialize a payment - used for card deposits
    public function initialize(string $email, int $amount, string $reference, string $callbackUrl): array
    {
        $response = Http::withToken($this->secret)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'email'        => $email,
                'amount'       => $amount,
                'reference'    => $reference,
                'callback_url' => $callbackUrl,
            ]);

        if (!$response->successful()) {
            Log::error('Paystack init failed', ['response' => $response->body()]);
            throw new \Exception('Payment initialization failed: ' . $response->json('message', 'Unknown error'));
        }

        return $response->json('data', []);
    }

    // verify a transaction after payment callback
    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secret)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        if (!$response->successful()) {
            Log::error('Paystack verify failed', ['reference' => $reference, 'response' => $response->body()]);
            throw new \Exception('Payment verification failed');
        }

        return $response->json('data', []);
    }

    // create a transfer recipient on Paystack before sending money to them
    public function createTransferRecipient(string $name, string $accountNumber, string $bankCode): array
    {
        $response = Http::withToken($this->secret)
            ->post("{$this->baseUrl}/transferrecipient", [
                'type'           => 'nuban',
                'name'           => $name,
                'account_number' => $accountNumber,
                'bank_code'      => $bankCode,
                'currency'       => 'NGN',
            ]);

        if (!$response->successful()) {
            Log::error('Paystack create recipient failed', [
                'account_number' => $accountNumber,
                'bank_code'      => $bankCode,
                'response'       => $response->body(),
            ]);
            throw new \Exception('Could not create transfer recipient: ' . $response->json('message', 'Unknown error'));
        }

        return $response->json('data', []);
    }

    // send money to a bank account via Paystack transfers
    // amount must be in kobo
    public function initiateTransfer(int $amount, string $recipient, string $reference, string $reason = 'CarePay Transfer'): array
    {
        $response = Http::withToken($this->secret)
            ->post("{$this->baseUrl}/transfer", [
                'source'    => 'balance',
                'amount'    => $amount,
                'recipient' => $recipient,
                'reference' => $reference,
                'reason'    => $reason,
            ]);

        if (!$response->successful()) {
            Log::error('Paystack transfer failed', [
                'reference' => $reference,
                'amount'    => $amount,
                'response'  => $response->body(),
            ]);
            throw new \Exception('Transfer failed: ' . $response->json('message', 'Unknown error'));
        }

        return $response->json('data', []);
    }

    // get list of Nigerian banks from Paystack
    public function getBanks(): array
    {
        $response = Http::withToken($this->secret)
            ->get("{$this->baseUrl}/bank", [
                'currency' => 'NGN',
                'perPage'  => 100,
            ]);

        if (!$response->successful()) {
            Log::error('Paystack get banks failed', ['response' => $response->body()]);
            return [];
        }

        return $response->json('data', []);
    }

    // resolve account name from account number and bank code
    public function resolveAccount(string $accountNumber, string $bankCode): ?string
    {
        $response = Http::withToken($this->secret)
            ->get("{$this->baseUrl}/bank/resolve", [
                'account_number' => $accountNumber,
                'bank_code'      => $bankCode,
            ]);

        if (!$response->successful()) {
            Log::warning('Paystack account resolve failed', [
                'account_number' => $accountNumber,
                'bank_code'      => $bankCode,
                'response'       => $response->body(),
            ]);
            return null;
        }

        return $response->json('data.account_name');
    }

    // verify webhook signature from Paystack
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $computed = hash_hmac('sha512', $payload, $this->secret);
        return hash_equals($computed, $signature);
    }
}