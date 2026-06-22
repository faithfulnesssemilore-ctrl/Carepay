<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(), // Generates a unique UUID for the wallet
            'user_id' => User::factory(), // Connects to a user factory
            'balance' => 5000000, // Starts test accounts with NGN 50,000 (stored in kobo/cents)
            'currency' => 'NGN',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
