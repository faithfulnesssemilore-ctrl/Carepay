<div class="container mt-5">
    <h3>Enter Transaction PIN</h3>

    <form wire:submit.prevent="verify">
        <input type="password"
               wire:model="pin"
               maxlength="4"
               class="form-control mb-3"
               placeholder="Enter 4-digit PIN">

        @error('pin') <span class="text-danger">{{ $message }}</span> @enderror

        <button class="btn btn-primary">Verify PIN</button>
    </form>
</div>