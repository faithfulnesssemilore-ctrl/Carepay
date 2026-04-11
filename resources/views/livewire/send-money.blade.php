
{{-- 
    Send Money Component - Multi-step transfer flow
    Matches React SendMoney.tsx functionality with Livewire state management
    Steps: recipient -> amount -> method -> confirm -> success
    
    CSS Files Used:
    - resources/css/app.css - Bootstrap import
    - resources/css/custom.css - Custom utilities (gradient, shadows, hover effects, card-luxury, btn-gradient)
    - resources/css/bootstrap-custom.css (if exists) - Theme overrides
    
    UI Components Used:
    - x-ui.card - Reusable card component with luxury variant
    - x-ui.button - Button with variants and sizes
    - x-ui.input - Text input component
    - x-ui.textarea - Textarea component
    - x-ui.alert - Alert/notification component
    - x-ui.badge - Badge component for status
--}}

<div class="container" style="max-width: 900px;">
    <div class="d-flex flex-column gap-4">
        
        {{-- Header Section --}}
        <div>
            <h1 class="display-5 fw-bold mb-2">Send Money</h1>
            <p class="text-muted-custom">Transfer money to anyone, anywhere</p>
        </div>

        {{-- Progress Steps Indicator --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            {{-- Step 1: Recipient --}}
            <div class="d-flex align-items-center flex-fill">
                <div class="d-flex flex-column align-items-center flex-fill">
                    <div 
                        class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="
                            width: 40px; 
                            height: 40px; 
                            border: 2px solid;
                            border-color: {{ $this->getStepIndex($currentStep) >= 0 ? '#a855f7' : '#2a2a3a' }};
                            background-color: {{ $this->getStepIndex($currentStep) >= 0 ? '#a855f7' : 'transparent' }};
                            color: {{ $this->getStepIndex($currentStep) >= 0 ? 'white' : '#888' }};
                            transition: all 0.3s ease;
                        "
                    >{{ $this->getStepIndex($currentStep) >= 0 ? '✓' : '1' }}</div>
                    <span class="small mt-2 d-none d-sm-block text-muted-custom">Recipient</span>
                </div>
                <div class="flex-fill mx-2" style="height: 2px; background: {{ $this->getStepIndex($currentStep) > 0 ? '#a855f7' : '#2a2a3a' }}; transition: all 0.3s ease;"></div>
            </div>

            {{-- Step 2: Amount --}}
            <div class="d-flex align-items-center flex-fill">
                <div class="d-flex flex-column align-items-center flex-fill">
                    <div 
                        class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="
                            width: 40px; 
                            height: 40px; 
                            border: 2px solid;
                            border-color: {{ $this->getStepIndex($currentStep) >= 1 ? '#a855f7' : '#2a2a3a' }};
                            background-color: {{ $this->getStepIndex($currentStep) >= 1 ? '#a855f7' : 'transparent' }};
                            color: {{ $this->getStepIndex($currentStep) >= 1 ? 'white' : '#888' }};
                            transition: all 0.3s ease;
                        "
                    >{{ $this->getStepIndex($currentStep) >= 1 ? '✓' : '2' }}</div>
                    <span class="small mt-2 d-none d-sm-block text-muted-custom">Amount</span>
                </div>
                <div class="flex-fill mx-2" style="height: 2px; background: {{ $this->getStepIndex($currentStep) > 1 ? '#a855f7' : '#2a2a3a' }}; transition: all 0.3s ease;"></div>
            </div>

            {{-- Step 3: Method --}}
            <div class="d-flex align-items-center flex-fill">
                <div class="d-flex flex-column align-items-center flex-fill">
                    <div 
                        class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="
                            width: 40px; 
                            height: 40px; 
                            border: 2px solid;
                            border-color: {{ $this->getStepIndex($currentStep) >= 2 ? '#a855f7' : '#2a2a3a' }};
                            background-color: {{ $this->getStepIndex($currentStep) >= 2 ? '#a855f7' : 'transparent' }};
                            color: {{ $this->getStepIndex($currentStep) >= 2 ? 'white' : '#888' }};
                            transition: all 0.3s ease;
                        "
                    >{{ $this->getStepIndex($currentStep) >= 2 ? '✓' : '3' }}</div>
                    <span class="small mt-2 d-none d-sm-block text-muted-custom">Method</span>
                </div>
                <div class="flex-fill mx-2" style="height: 2px; background: {{ $this->getStepIndex($currentStep) > 2 ? '#a855f7' : '#2a2a3a' }}; transition: all 0.3s ease;"></div>
            </div>

            {{-- Step 4: Confirm --}}
            <div class="d-flex align-items-center flex-fill">
                <div class="d-flex flex-column align-items-center flex-fill">
                    <div 
                        class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="
                            width: 40px; 
                            height: 40px; 
                            border: 2px solid;
                            border-color: {{ $this->getStepIndex($currentStep) >= 3 ? '#a855f7' : '#2a2a3a' }};
                            background-color: {{ $this->getStepIndex($currentStep) >= 3 ? '#a855f7' : 'transparent' }};
                            color: {{ $this->getStepIndex($currentStep) >= 3 ? 'white' : '#888' }};
                            transition: all 0.3s ease;
                        "
                    >{{ $this->getStepIndex($currentStep) >= 3 ? '✓' : '4' }}</div>
                    <span class="small mt-2 d-none d-sm-block text-muted-custom">Confirm</span>
                </div>
            </div>
        </div>

        {{-- Main Content Card with Animation using custom CSS utility classes --}}
        <x-ui.card variant="luxury" hover="lift">
            <div class="card-body p-4 p-md-5">
                
                {{-- STEP 1: Recipient Selection --}}
                @if ($currentStep === 'recipient')
                    @include('livewire.steps.recipient-step', ['recentContacts' => $recentContacts])
                @endif

                {{-- STEP 2: Amount Input --}}
                @if ($currentStep === 'amount')
                    @include('livewire.steps.amount-step')
                @endif

                {{-- STEP 3: Transfer Method Selection --}}
                @if ($currentStep === 'method')
                    @include('livewire.steps.method-step')
                @endif

                {{-- STEP 4: Confirmation --}}
                @if ($currentStep === 'confirm')
                    @include('livewire.steps.confirm-step')
                @endif

                {{-- STEP 5: Success --}}
                @if ($currentStep === 'success')
                    @include('livewire.steps.success-step')
                @endif

            </div>
        </x-ui.card>
    </div>
</div>