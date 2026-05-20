<?php

use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'status'            => 'active',
        'email_verified_at' => now(),
    ]);

    Wallet::create([
        'user_id'  => $this->user->id,
        'balance'  => 0,
        'currency' => 'NGN',
        'status'   => 'active',
    ]);
});

test('wallet balance starts at zero', function () {
    $wallet = Wallet::where('user_id', $this->user->id)->first();
    expect($wallet->balance)->toBe(0);
});

test('crediting wallet increases balance', function () {
    (new WalletService())->credit($this->user->id, 50000, 'REF-001', 'Test');

    $balance = Wallet::where('user_id', $this->user->id)->first()->balance;
    expect($balance)->toBe(50000);
});

test('same reference cannot credit wallet twice', function () {
    (new WalletService())->credit($this->user->id, 50000, 'SAME-REF', 'First');
    (new WalletService())->credit($this->user->id, 50000, 'SAME-REF', 'Duplicate');

    $balance = Wallet::where('user_id', $this->user->id)->first()->balance;
    expect($balance)->toBe(50000); // not 100000
});

test('cannot debit more than wallet balance', function () {
    expect(fn () => (new WalletService())->debit($this->user->id, 10000, 'REF-002', 'Test'))
        ->toThrow(Exception::class, 'Insufficient balance');
});

test('unauthenticated user cannot access dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('unauthenticated user cannot access wallet API', function () {
    $this->getJson('/api/wallet/balance')->assertStatus(401);
});
