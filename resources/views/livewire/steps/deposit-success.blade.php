{{--
    Deposit Success Step
    Displays success message and processing time information
--}}

<x-ui.card variant="default" hover="lift" class="border">
    <div class="card-body p-5 text-center">
        {{-- Success Icon --}}
        <div
            class="rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center"
            style="
                width: 80px;
                height: 80px;
                background: rgba(168, 85, 247, 0.1);
            "
        >
            <i class="fas fa-check-circle text-primary-custom" style="font-size: 40px;"></i>
        </div>

        {{-- Success Message --}}
        <h2 class="display-6 fw-bold mb-2">Request Received!</h2>
        <p class="text-muted-custom mb-4">
            Your deposit request has been received. Your wallet will be credited shortly.
        </p>

        {{-- Processing Time Card --}}
        <x-ui.card variant="default" class="bg-secondary-custom border-0 mb-4">
            <div class="card-body">
                <div class="small text-muted-custom mb-1">Processing Time</div>
                <div class="fw-semibold">
                    @if ($selectedMethod === 'bank-transfer')
                        5-10 minutes
                    @elseif ($selectedMethod === 'cash')
                        Instant
                    @elseif ($selectedMethod === 'card')
                        Instant
                    @elseif ($selectedMethod === 'ussd')
                        Instant
                    @else
                        Processing...
                    @endif
                </div>
            </div>
        </x-ui.card>

        {{-- Action Button --}}
        <button
            type="button"
            class="btn btn-gradient w-100 py-3"
            wire:click="$navigate('/app/wallet')"
        >
            <i class="fas fa-wallet me-2"></i>
            Go to Wallet
        </button>
    </div>
</x-ui.card>
