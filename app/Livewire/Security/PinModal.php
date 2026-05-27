<?php

namespace App\Livewire\Security;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class PinModal extends Component
{
    public $pin = '';

    public $action = null;

    public $payload = [];

    public $show = false;

    protected $listeners = ['openPinModal'];

    public function openPinModal($action, $payload = [])
    {
        $this->action = $action;
        $this->payload = $payload;
        $this->show = true;
        $this->pin = '';
    }

    public function verifyPin()
    {
        $this->validate([
            'pin' => 'required|digits:4',
        ]);

        $user = auth()->user();

        if (! Hash::check($this->pin, $user->transaction_pin)) {
            session()->flash('error', 'Incorrect PIN');

            return;
        }

        // Emit event after success
        $this->dispatch('pinVerified', action: $this->action, payload: $this->payload);

        $this->reset(['pin', 'show']);
    }

    public function render()
    {

        return view('livewire.security.pin-model-class');

    }
}
