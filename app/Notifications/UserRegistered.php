<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->view('emails.welcome', ['user' => $notifiable])
            ->subject('Welcome to Carepay');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Welcome to Carepay',
        ];
    }
}
