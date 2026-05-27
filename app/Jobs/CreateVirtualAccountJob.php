<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\VirtualAccount;
use App\Services\VirtualAccountService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateVirtualAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        try {
            if (VirtualAccount::where('user_id', $this->user->id)->exists()) {
                return;
            }

            $service = new VirtualAccountService;
            $service->create($this->user);

            Log::info('Virtual account created for user: '.$this->user->id);
        } catch (\Exception $e) {
            Log::error('Virtual account creation failed for user '.$this->user->id.': '.$e->getMessage());
        }
    }
}
