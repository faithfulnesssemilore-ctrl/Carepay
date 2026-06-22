<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Admin',
                'last_name' => 'CarePay',
                'email' => 'admin@carepay.com',

                'phone' => '08000000001',
                'password' => bcrypt('Admin@12345'),
                'role' => 1,
                'status' => 'active',
                'kyc_verified' => true,
                'pin' => bcrypt('1234'),
                'email_verified_at' => now(),
                'registration_complete' => true,
                'terms_accepted' => true,
            ],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'user@carepay.com',

                'phone' => '08000000002',
                'password' => bcrypt('User@12345'),
                'role' => 0,
                'status' => 'active',
                'kyc_verified' => true,
                'pin' => bcrypt('1234'),
                'email_verified_at' => now(),
                'registration_complete' => true,
                'terms_accepted' => true,
            ],
            [
                'first_name' => 'Demo',
                'last_name' => 'User',
                'email' => 'demo@carepay.com',

                'phone' => '08000000003',
                'password' => bcrypt('Demo@12345'),
                'role' => 0,
                'status' => 'active',
                'kyc_verified' => true,
                'pin' => bcrypt('1234'),
                'email_verified_at' => now(),
                'registration_complete' => true,
                'terms_accepted' => true,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Get or create wallet, then update via model so MoneyCast is triggered
            $wallet = $user->wallet;
            if (! $wallet) {
                // User::booted() should have created it, but if not, create it
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'currency' => 'NGN',
                    'status' => 'active',
                    'balance' => 0,
                ]);
            }

            // Set naira value (50000) — MoneyCast.set() will multiply by 100 for storage
            $wallet->balance = 50000;
            $wallet->save();

            $user->limits()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'single_transaction_limit' => 100000,
                    'daily_transfer_limit' => 500000,
                    'daily_transfer_used' => 0,
                    'limit_reset_date' => today(),
                ]
            );

            $this->command->line("✓ {$user->email} — wallet ₦50,000");
        }

        $this->command->info('Test users ready.');
    }
}
