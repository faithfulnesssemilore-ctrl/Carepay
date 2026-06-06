<?php

namespace App\Services;

use App\Models\VirtualAccount;
use Illuminate\Support\Facades\Http;
use Exception;

class VirtualAccountService
{
    public function create($user)
    {
        //  Sanitize and format phone number to international standard (+234...)
        $rawPhone = $user->phone ?? '08000000000';
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        
        if (str_starts_with($cleanPhone, '234')) {
            $cleanPhone = '0' . substr($cleanPhone, 3);
        }
        
        $paystackPhone = '+234' . substr($cleanPhone, -10);

        // 2. Request dedicated virtual account from Paystack
        $response = Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/dedicated_account', [
                'customer' => $user->paystack_customer_id ?? null, // Highly recommended if you create customers first
                'email' => $user->email,
                'first_name' => $user->first_name ?? $user->name,
                'last_name' => $user->last_name ?? 'User',
                'phone' => $paystackPhone,
                'preferred_bank' => 'wema-bank', // or 'titan-paystack'
            ]);

        if (!$response->successful()) {
            // Log the error message from Paystack to help you debug quickly
            logger()->error('Paystack Virtual Account Creation Failed', [
                'user_id' => $user->id,
                'response' => $response->json()
            ]);
            throw new Exception('Failed to create virtual account: ' . ($response->json()['message'] ?? 'Unknown Error'));
        }

        $data = $response->json()['data'];

        // 3. Store the generated bank details in your database
        return VirtualAccount::create([
            'user_id' => $user->id,
            'account_name' => $data['account_name'] ?? ($user->first_name . ' ' . $user->last_name),
            'account_number' => $data['account_number'],
            'bank_name' => $data['bank']['name'] ?? 'Wema Bank',
            'provider' => 'paystack',
        ]);
    }
}
