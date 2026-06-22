<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'bank_name' => $this->faker->randomElement(['GTBank', 'Zenith Bank', 'Access Bank', 'UBA']),
            'bank_code' => $this->faker->bothify('????'),
            'account_number' => $this->faker->numerify('##########'), // Generates a 10-digit number
            'account_name' => $this->faker->name(),
            'user_id' => User::factory(), // Create a user and associate
        ];
    }
}
