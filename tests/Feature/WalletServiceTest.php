<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithWallet(int $balance = 0): User
    {
        $user = User::factory()->create();
        Wallet::create([
            'user_id'  => $user->id,
            'balance'  => $balance,
            'currency' => 'NGN',
            'status'   => 'active',
        ]);
        return $user;
    }

    public function test_credit_adds_to_wallet_balance(): void
    {
        $user = $this->createUserWithWallet(0);

        (new WalletService())->credit($user->id, 10000, 'REF-001', 'Test credit');

        $this->assertDatabaseHas('wallet', [
            'user_id' => $user->id,
            'balance' => 10000,
        ]);
    }

    public function test_debit_reduces_wallet_balance(): void
    {
        $user = $this->createUserWithWallet(20000);

        (new WalletService())->debit($user->id, 5000, 'REF-002', 'Test debit');

        $this->assertDatabaseHas('wallet', [
            'user_id' => $user->id,
            'balance' => 15000,
        ]);
    }

    public function test_debit_fails_when_balance_is_insufficient(): void
    {
        $user = $this->createUserWithWallet(5000);

        $this->expectException(\Exception::class);

        (new WalletService())->debit($user->id, 10000, 'REF-003', 'Test debit');
    }

    public function test_duplicate_credit_does_not_double_credit(): void
    {
        $user = $this->createUserWithWallet(0);

        (new WalletService())->credit($user->id, 10000, 'SAME-REF', 'First');
        (new WalletService())->credit($user->id, 10000, 'SAME-REF', 'Second attempt');

        $this->assertDatabaseHas('wallet', [
            'user_id' => $user->id,
            'balance' => 10000,
        ]);
    }

    public function test_transfer_debits_sender_and_credits_recipient(): void
    {
        $sender    = $this->createUserWithWallet(100000);
        $recipient = $this->createUserWithWallet(0);

        (new WalletService())->transfer(
            senderId:     $sender->id,
            recipientId:  $recipient->id,
            amountKobo:   50000,
            description:  'Test transfer'
        );

        $this->assertDatabaseHas('wallet', ['user_id' => $sender->id,    'balance' => 50000]);
        $this->assertDatabaseHas('wallet', ['user_id' => $recipient->id, 'balance' => 50000]);
    }

    public function test_dashboard_requires_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_logged_in_user_can_see_dashboard(): void
    {
        $user = $this->createUserWithWallet(0);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }
}
