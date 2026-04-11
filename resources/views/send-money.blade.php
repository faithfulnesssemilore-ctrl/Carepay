<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money - CarePay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/bootstrap.css'])
    @vite(['resources/css/custom.css'])
    @livewireStyles
</head>
<body>
    @livewire('send-money')
    @livewireScripts
</body>
</html>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                        {{ $errorMessage }}
                        <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
                    </div>
                    @endif

                    <form wire:submit.prevent="sendMoney">
                        {{-- Recipient Email --}}
                        <div class="mb-3">
                            <label class="form-label text-white">Recipient Email</label>
                            <input type="email" class="form-control @error('recipientEmail') is-invalid @enderror"
                                wire:model.defer="recipientEmail" placeholder="recipient@example.com"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 12px; border-radius: 10px;">
                            @error('recipientEmail') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label text-white">Amount (NGN)</label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                wire:model.defer="amount" min="0.01" step="0.01" placeholder="0.00"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 12px; border-radius: 10px;">
                            @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-4">
                            <label class="form-label text-white">Description (Optional)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                wire:model.defer="description" rows="3" placeholder="Add a note..."
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 12px; border-radius: 10px;"></textarea>
                            @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #e94560 0%, #f17c5c 100%); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 600;"
                            @if($isProcessing) disabled @endif>
                            @if($isProcessing)
                            <span class="spinner-border spinner-border-sm me-2"></span> Processing...
                            @else
                            <i class="fas fa-arrow-right me-2"></i> Send Money
                            @endif
                        </button>
                    </form>

                    <div class="mt-3">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: #e94560;">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
