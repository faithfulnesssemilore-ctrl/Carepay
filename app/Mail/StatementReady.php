<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class StatementReady extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $fileName,
        public string $downloadUrl,
        public Carbon $startDate,
        public Carbon $endDate,
    ) {}

    public function build()
    {
        return $this->subject('Your Statement of Account is Ready')
            ->markdown('emails.statement-ready');
    }
}
