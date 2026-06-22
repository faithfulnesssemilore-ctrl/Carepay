<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class StatementFailed extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Carbon $startDate,
        public Carbon $endDate,
        public string $errorMessage,
    ) {}

    public function build()
    {
        return $this->subject('Statement Export Failed')
            ->markdown('emails.statement-failed');
    }
}
