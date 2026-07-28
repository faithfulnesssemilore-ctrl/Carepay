<?php

use App\Models\User;
use App\Services\PaymentService;
use App\Services\VtpassService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('uses sandbox transfer responses when no live payment credentials are configured', function () {
    config(['services.paystack.secret' => null]);

    $service = new PaymentService;

    $recipient = $service->createTransferRecipient('Sandbox Test', '9026446100', 'CAREPAY');
    $transfer = $service->initiateTransfer(5000, 'sandbox-recipient', 'sandbox-reference', 'Sandbox transfer');

    expect($recipient['recipient_code'])->toBeString()
        ->and($recipient['status'])->toBe('sandbox')
        ->and($transfer['status'])->toBe('success')
        ->and($transfer['message'])->toContain('sandbox');
});

it('uses sandbox bill payment responses when vtpass credentials are missing', function () {
    config(['services.vtpass.api_key' => null, 'services.vtpass.public_key' => null, 'services.vtpass.secret_key' => null]);

    $service = new VtpassService;

    $result = $service->processPayment('mtn', 100, '08012345678', ['request_id' => 'sandbox-request']);

    expect($result['code'])->toBe('000')
        ->and($result['response_description'])->toContain('Sandbox');
});
