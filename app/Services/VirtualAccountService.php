<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\VirtualAccount;

class VirtualAccountService
{
    public function create($user)
    {
        $response = Http::withToken(config('services.paystack.secret'))
            ->post('https://api.paystack.co/dedicated_account', [
                "email" => $user->email,
                "first_name" => $user->first_name ?? $user->name,
                "last_name" => $user->last_name ?? 'User',
                "phone" => $user->phone ?? '8000000000',
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to create virtual account');
        }

        $data = $response['data'];

        return VirtualAccount::create([
            'user_id' => $user->id,
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'bank_name' => $data['bank']['name'],
            'provider' => 'paystack'
        ]);
    }
}