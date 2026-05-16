<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserLimit;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name'   => 'Admin',
                'last_name'    => 'CarePay',
                'email'        => 'admin@carepay.com',
                'username'     => 'carepay_admin',
                'phone'        => '08000000001',
                'password'     => bcrypt('Admin@12345'),
                'role'         => 1,
                'status'       => 'active',
                'kyc_verified' => true,
            ],
            [
                'first_name'   => 'Test',
                'last_name'    => 'User',
                'email'        => 'user@carepay.com',
                'username'     => 'testuser',
                'phone'        => '08000000002',
                'password'     => bcrypt('User@12345'),
                'role'         => 0,
                'status'       => 'active',
                'kyc_verified' => true,
            ],
            [
                'first_name'   => 'Demo',
                'last_name'    => 'User',
                'email'        => 'demo@carepay.com',
                'username'     => 'demouser',
                'phone'        => '08000000003',
                'password'     => bcrypt('Demo@12345'),
                'role'         => 0,
                'status'       => 'active',
                'kyc_verified' => true,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 5000000, 'currency' => 'NGN', 'status' => 'active']
            );

            UserLimit::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'single_transaction_limit' => 100000,
                    'daily_transfer_limit'     => 500000,
                    'limit_reset_date'         => today(),
                ]
            );
        }

        $this->command->info('Test users created successfully');
    }
}
