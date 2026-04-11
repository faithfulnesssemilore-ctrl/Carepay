<?php

namespace App\Jobs;

        use Illuminate\Contracts\Queue\ShouldQueue;
        use Illuminate\Foundation\Queue\Queueable;
        use Illuminate\Queue\InteractsWithQueue;
        use Illuminate\Queue\SerializesModels;
        use App\Notifications\TransferCompleted;
        use App\Models\AuditLog;
        
class ProcessTransferJob implements ShouldQueue
{
    use Queueable;
    
    use InteractsWithQueue, SerializesModels;

    public $transaction;
    /**
     * Create a new job instance.
     */
    public function __construct($transaction)
    {
        //
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
         // Send notification
        $this->transaction->user->notify(
            new TransferCompleted($this->transaction)
        );

        // Audit log
        AuditLog::create([
            'user_id' => $this->transaction->user_id,
            'action' => 'transfer_completed',
            'data' => json_encode([
                'amount' => $this->transaction->amount
            ]),
            'ip_address' => request()->ip()
        ]);

         // Update transaction status
         $this->transaction->update([
            'status' => 'completed'
        ]);
    }
}
