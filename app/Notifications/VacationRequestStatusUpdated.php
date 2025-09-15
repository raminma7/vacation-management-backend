<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VacationRequestStatusUpdated extends Notification
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
            ->subject('Your Vacation Request Has Been Updated')
            ->line("Your vacation request has been **{$this->request->status}**.")
            ->line('Admin Note: ' . ($this->request->admin_note ?: 'No note provided.'))
            ->action('View Request', url('/my-requests')) 
            ->line('Thank you.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'request_id'  => $this->request->id,
            'status'      => $this->request->status,
            'admin_note'  => $this->request->admin_note,
            'updated_at'  => now(),
        ];
    }
}
