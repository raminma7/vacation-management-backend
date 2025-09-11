<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class VacationRequestCreated extends Notification
{
    use Queueable;

    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Vacation Request')
                    ->line("User {$this->request->user->name} has submitted a new vacation request.")
                    ->action('View Request', url('/requests'))
                    ->line('Please review it as soon as possible.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'request_id' => $this->request->id,
            'user_id' => $this->request->user->id,
            'status' => $this->request->status,
            'note' => $this->request->note,
        ];
    }
}
