<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['credit', 'debit']);
        $category = $type === 'credit' ? 'deposit' : 'withdrawal';

        return [
            'wallet_id' => null,
            'user_id' => null,
            'amount' => $this->faker->numberBetween(100, 100000),
            'currency' => 'NGN',
            'type' => $type,
            'category' => $category,
            'status' => $this->faker->randomElement(['completed', 'pending', 'failed']),
            'reference' => (string) Str::uuid(),
            'description' => $this->faker->sentence(4),
            'recipient_id' => null,
            'metadata' => [],
            'payment_method' => null,
            'gateway' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
