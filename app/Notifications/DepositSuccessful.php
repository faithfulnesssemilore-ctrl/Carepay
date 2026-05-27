<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositSuccessful extends Notification
{
    use Queueable;

    protected $amount;

    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Deposit Successful')
            ->greeting('Hello '.$notifiable->first_name)
            ->line('Your wallet has been credited successfully.')
            ->line('Amount: ₦'.number_format($this->amount, 2))
            ->line('Thank you for using our platform.');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Wallet credited',
            'amount' => $this->amount,
        ];
    }
}
