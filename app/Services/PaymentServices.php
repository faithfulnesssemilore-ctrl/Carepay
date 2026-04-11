<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $secret;//This is to 

    public function __construct()
    {
        $this->secret = config('services.paystack.secret');
    }

    // Initialize Paystack payment
    public function initialize($email, $amount, $reference, $callbackUrl)
    {
        $response = Http::withToken($this->secret)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $email,
                'amount' => $amount,
                'reference' => $reference,
                'callback_url' => $callbackUrl
            ]);

        if (!$response->successful()) {
            Log::error('Paystack init failed', [
                'response' => $response->body()
            ]);

            throw new \Exception('Payment initialization failed');
        }

        return $response->json();
    }

    // Verify webhook signature
    public function verifyWebhook($payload, $signature)
    {
        $computed = hash_hmac(
            'sha512',
            $payload,
            $this->secret
        );

        return hash_equals($computed, $signature);
    }
}