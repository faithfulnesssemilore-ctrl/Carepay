<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::all()->each(function ($user) {
            \App\Models\Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'currency' => 'NGN',
            ]);
        });
    }
}
