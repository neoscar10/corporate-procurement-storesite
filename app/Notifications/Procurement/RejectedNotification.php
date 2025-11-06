<?php

namespace App\Notifications\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Procurement\ProcurementRequest;
use App\Models\User;

class RejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProcurementRequest $req, public int $byUserId) {}

    public function via($notifiable): array { return ['mail']; }

    protected function byName(): string
    {
        $u = User::find($this->byUserId);
        return $u?->name ?? 'An approver';
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Procurement Rejected: '.$this->req->title)
            ->greeting('Hello '.$notifiable->name)
            ->line('A procurement request was rejected.')
            ->line('Title: '.$this->req->title)
            ->line('Rejected by: '.$this->byName())
            ->action('Review Request', url('/procure/requests/'.$this->req->id))
            ->line('Please review and update as needed.');
    }
}
