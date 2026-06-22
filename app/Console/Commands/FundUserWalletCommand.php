<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FundUserWalletCommand extends Command
{
    protected $signature = 'wallet:fund {email} {amount}';

    protected $description = 'Add funds to a specific user wallet';

    public function handle()
    {
        $email = $this->argument('email');
        $amount = (float) $this->argument('amount');

        if ($amount <= 0) {
            $this->error('The deposit amount must be greater than zero.');

            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found!");

            return Command::FAILURE;
        }

        try {
            $wallet = DB::transaction(function () use ($user, $amount) {
                $wallet = Wallet::where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $user->id, // Fixed: Pass user_id to assign foreign key properly
                        'currency' => 'NGN',
                        'status' => 'active',
                        'balance' => 0,
                    ]);
                }

                $wallet->increment('balance', $amount);

                // Create transaction with all required structural values mapping your model fields
                $user->transactions()->create([
                    'wallet_id' => $wallet->id, // Links transaction directly to the wallet instance
                    'amount' => $amount,
                    'type' => 'credit', // Maps direction check in your model logic
                    'currency' => $wallet->currency ?? 'NGN',
                    'category' => 'deposit',
                    'reference' => 'CMD_FUND_'.strtoupper(Str::random(10)),
                    'description' => 'Manual Admin wallet funding',
                ]);

                return $wallet;
            });

            $wallet->refresh();

            $this->info('✓ Successfully added ₦'.number_format($amount, 2)." to {$email}. New Balance: ₦".number_format($wallet->balance, 2));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to fund wallet safely: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
