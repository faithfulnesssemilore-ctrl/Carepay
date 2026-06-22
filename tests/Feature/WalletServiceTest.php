<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithWallet(int $balance = 0): User
    {
        $user = User::factory()->create([
            'status' => 'active',
            'kyc_verified' => true,
            'email_verified_at' => now(),
        ]);

        // User boot creates wallet, update balance via model instance to apply casts
        $wallet = $user->wallet;
        $wallet->balance = $balance; // This applies MoneyCast set (multiply by 100)
        $wallet->save();

        // Reload to refresh the wallet after save
        $user->refresh();

        return $user;
    }

    public function test_credit_adds_to_wallet_balance(): void
    {
        $user = $this->createUserWithWallet(0);

        (new WalletService)->credit(
            $user->id,
            1000000, // 10000 naira in kobo
            'REF-001',
            'Test credit'
        );

        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertEquals(10000, $wallet->balance); // casted to naira
    }

    public function test_debit_reduces_wallet_balance(): void
    {
        $user = $this->createUserWithWallet(20000); // 20000 naira (will be stored as 2M kobo)

        // reload wallet to confirm balance was set correctly
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertEquals(20000, $wallet->balance); // casted to naira

        (new WalletService)->debit(
            $user->id,
            500000, // 5000 naira in kobo
            'REF-002',
            'Test debit'
        );

        $wallet->refresh();
        $this->assertEquals(15000, $wallet->balance); // 20000 - 5000 = 15000 naira
    }

    public function test_debit_fails_when_balance_is_insufficient(): void
    {
        $user = $this->createUserWithWallet(5000); // 5000 naira

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient balance');

        (new WalletService)->debit(
            $user->id,
            1000000, // 10000 naira in kobo
            'REF-003',
            'Test debit'
        );
    }

    public function test_duplicate_credit_does_not_double_credit(): void
    {
        $user = $this->createUserWithWallet(0);

        (new WalletService)->credit($user->id, 1000000, 'SAME-REF', 'First'); // 10000 naira

        // second call with same reference should throw but NOT credit again
        try {
            (new WalletService)->credit($user->id, 1000000, 'SAME-REF', 'Duplicate');
        } catch (\Exception $e) {
            // expected - duplicate reference is rejected
        }

        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertEquals(10000, $wallet->balance); // still 10000 naira
    }

    public function test_transfer_debits_sender_and_credits_recipient(): void
    {
        $sender = $this->createUserWithWallet(100000); // 100000 naira
        $recipient = $this->createUserWithWallet(0);

        // use correct parameter name from WalletService::transfer()
        (new WalletService)->transfer(
            $sender->id,
            $recipient->id,
            5000000, // 50000 naira in kobo
            'Test transfer'
        );

        // Reload wallets from database
        $senderWallet = Wallet::where('user_id', $sender->id)->first();
        $recipientWallet = Wallet::where('user_id', $recipient->id)->first();

        $this->assertEquals(50000, $senderWallet->balance); // 100000 - 50000 = 50000 naira
        $this->assertEquals(50000, $recipientWallet->balance); // 0 + 50000 = 50000 naira
    }

    public function test_dashboard_requires_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_logged_in_user_can_access_dashboard(): void
    {
        $user = $this->createUserWithWallet(0);

        $response = $this->actingAs($user)->get('/dashboard');

        // 200 means loaded, 302 means redirected elsewhere
        // accept both 200 and a redirect to another authenticated page
        $this->assertContains($response->status(), [200, 302]);

        // but it must NOT redirect to login
        if ($response->status() === 302) {
            $this->assertStringNotContainsString('/login', $response->headers->get('Location'));
        }
    }
}
