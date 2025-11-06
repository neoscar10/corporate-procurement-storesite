<?php

namespace App\Notifications\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Procurement\ProcurementRequest;

class PublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProcurementRequest $req) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Procurement Published: '.$this->req->title)
            ->greeting('Hello '.$notifiable->name)
            ->line('A procurement request has been published and is now visible to platform admins.')
            ->line('Title: '.$this->req->title)
            ->action('Open Request', url('/procure/requests/'.$this->req->id))
            ->line('You’re receiving this because you are a watcher, creator, or company admin.');
    }
}
