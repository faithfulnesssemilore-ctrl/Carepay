<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VtpassService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $publicKey;

    protected string $secretKey;

    public function __construct()
    {
        $this->baseUrl = config('services.vtpass.base_url', 'https://sandbox.vtpass.com/api');
        $this->apiKey = config('services.vtpass.api_key', '');
        $this->publicKey = config('services.vtpass.public_key', '');
        $this->secretKey = config('services.vtpass.secret_key', '');
    }

    // GET requests use api-key + public-key
    protected function getRequest(string $endpoint, array $params = []): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'public-key' => $this->publicKey,
            ])->get($this->baseUrl.$endpoint, $params);

            return $response->json() ?? ['code' => '999', 'response_description' => 'Empty response'];
        } catch (\Exception $e) {
            Log::error('VTPass GET error: '.$e->getMessage());

            return ['code' => '999', 'response_description' => $e->getMessage()];
        }
    }

    // POST requests use api-key + secret-key
    protected function postRequest(string $endpoint, array $payload): array
    {
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'secret-key' => $this->secretKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.$endpoint, $payload);

            return $response->json() ?? ['code' => '999', 'response_description' => 'Empty response'];
        } catch (\Exception $e) {
            Log::error('VTPass POST error: '.$e->getMessage());

            return ['code' => '999', 'response_description' => $e->getMessage()];
        }
    }

    // generate unique request ID required by VTPass
    public function generateRequestId(): string
    {
        return now()->format('YmdHi').substr(md5(uniqid(mt_rand(), true)), 0, 8);
    }

    // check wallet balance on VTPass
    public function getBalance(): array
    {
        return $this->getRequest('/balance');
    }

    // process any VTPass payment
    public function processPayment(string $serviceId, float $amount, string $phone, array $additionalData = []): array
    {
        $payload = array_merge([
            'request_id' => $this->generateRequestId(),
            'serviceID' => $serviceId,
            'amount' => $amount,
            'phone' => $phone,
        ], $additionalData);

        return $this->postRequest('/pay', $payload);
    }

    // check status of a previous transaction
    public function checkTransactionStatus(string $requestId): array
    {
        return $this->postRequest('/requery', ['request_id' => $requestId]);
    }

    // get service variations (data plans, cable bouquets, etc)
    public function getVariations(string $serviceId): array
    {
        return $this->getRequest('/service-variations', ['serviceID' => $serviceId]);
    }

    // verify electricity meter number before payment
    public function verifyMeter(string $serviceId, string $meterNumber, string $meterType): array
    {
        return $this->getRequest('/merchant-verify', [
            'billersCode' => $meterNumber,
            'serviceID' => $serviceId,
            'type' => $meterType,
        ]);
    }

    // verify cable TV smartcard number
    public function verifySmartcard(string $serviceId, string $smartcard): array
    {
        return $this->getRequest('/merchant-verify', [
            'billersCode' => $smartcard,
            'serviceID' => $serviceId,
        ]);
    }
}
