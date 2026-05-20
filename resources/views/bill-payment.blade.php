<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarePay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/bootstrap.css'])
    @vite(['resources/css/custom.css'])
    @livewireStyles
</head>
<body>
    @livewire('bill-payment')
    @livewireScripts
</body>
</html>
                        {{ $successMessage }}
                        <button type="button" class="btn-close" wire:click="resetForm"></button>
                    </div>
                    @endif

                    @if ($errorMessage)
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
                        {{ $errorMessage }}
                        <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
                    </div>
                    @endif

                    <form wire:submit.prevent="payBill">
                        {{-- Bill Type --}}
                        <div class="mb-3">
                            <label class="form-label text-white">Bill Type</label>
                            <select class="form-select @error('billType') is-invalid @enderror"
                                wire:model.defer="billType"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 12px; border-radius: 10px;">
                                <option value="" style="background: #1a1a2e;">Select a bill type...</option>
                                @foreach($billTypes as $key => $label)
                                <option value="{{ $key }}" style="background: #1a1a2e;">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('billType') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Biller Name --}}
                        <div class="mb-3">
                            <label class="form-label text-white">Biller Name</label>
                            <input type="text" class="form-control @error('billerName') is-invalid @enderror"
                                wire:model.defer="billerName" placeholder="Company or Service Name"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 12px; border-radius: 10px;">
                            @error('billerName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="mb-3">
                            <label class="form-label text-white">Amount (NGN)</label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                wire:model.defer="amount" min="0.01" step="0.01" placeholder="0.00"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 12px; border-radius: 10px;">
                            @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Reference Number --}}
                        <div class="mb-4">
                            <label class="form-label text-white">Reference Number (Optional)</label>
                            <input type="text" class="form-control @error('referenceNumber') is-invalid @enderror"
                                wire:model.defer="referenceNumber" placeholder="Account or Reference number"
                                style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; padding: 12px; border-radius: 10px;">
                            @error('referenceNumber') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn w-100" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 600;"
                            @if($isProcessing) disabled @endif>
                            @if($isProcessing)
                            <span class="spinner-border spinner-border-sm me-2"></span> Processing...
                            @else
                            <i class="fas fa-check-circle me-2"></i> Pay Bill
                            @endif
                        </button>
                    </form>

                    <div class="mt-3">
                        <a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: #2196F3;">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
