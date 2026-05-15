<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

// database seeder - creates test data for development
// helpful for testing without manually creating users/wallets/transactions
// run with: php artisan db:seed

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // create test admin user
        User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'phone' => encrypt('09000000000'),
            'role' => 1, // 1 = admin
            'email_verified_at' => now(),
            'pin' => bcrypt('1234'), // test PIN: 1234
        ]);
              // create test  user
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'testuser@test.com',
            'phone' => encrypt('09000000000'),
            'role' => 0, // 0 = user
            'email_verified_at' => now(),
            'pin' => bcrypt('1234'), // test PIN: 1234
        ]);

        // create 5 regular test users
        User::factory()
            ->count(5)
            ->create([
                'email_verified_at' => now(),
                'pin' => bcrypt('1234'), // all have PIN: 1234 for testing
            ])
            ->each(function ($user) {
                // create wallet with starting balance
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 100000, // start with 1000 naira
                    'currency' => 'NGN',
                ]);

                // create bank accounts for withdrawals
                BankAccount::factory()
                    ->count(2)
                    ->create([
                        'user_id' => $user->id,
                    ]);

                // set up transaction limits
                // these prevent users from transferring too much too fast
                $user->limits()->create([
                    'single_transaction_limit' => 100000,
                    'daily_transfer_limit' => 500000,
                ]);

                // create sample transactions for testing
                Transaction::factory()
                    ->count(3)
                    ->create([
                        'user_id' => $user->id,
                        'wallet_id' => $wallet->id,
                    ]);
            });
    }
}
