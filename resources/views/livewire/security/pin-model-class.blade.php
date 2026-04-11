
    {{-- The best athlete wants his opponent at his best. --}}
    @if($show)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    
    <div class="bg-white rounded-2xl p-6 w-80 shadow-xl">
        
        <h5 class="text-center font-semibold mb-3">
            Enter Transaction PIN
        </h5>

        <input 
            type="password"
            maxlength="4"
            wire:model="pin"
            class="form-control text-center text-2xl tracking-widest mb-4"
            placeholder="****"
        />

        <div class="grid grid-cols-3 gap-2 mb-4">
            @foreach(range(1,9) as $num)
                <button wire:click="$set('pin', pin + '{{ $num }}')" class="btn btn-light">
                    {{ $num }}
                </button>
            @endforeach

            <button wire:click="$set('pin', '')" class="btn btn-danger">
                Clear
            </button>

            <button wire:click="$set('pin', pin + '0')" class="btn btn-light">
                0
            </button>

            <button wire:click="verifyPin" class="btn btn-primary">
                OK
            </button>
        </div>

        <button wire:click="$set('show', false)" class="btn btn-secondary w-100">
            Cancel
        </button>

    </div>

</div>
@endif
