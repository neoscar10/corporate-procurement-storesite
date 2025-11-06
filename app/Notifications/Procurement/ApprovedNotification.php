<?php

namespace App\Notifications\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Procurement\ProcurementRequest;

class ApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProcurementRequest $req) {}

    public function via($notifiable): array { return ['mail']; }

    protected function moneyRange(): string
    {
        $cur = $this->req->currency ?? 'INR';
        $sym = $cur === 'INR' ? '₹' : $cur.' ';
        $min = $this->req->budget_min ? number_format($this->req->budget_min, 2) : '0.00';
        $max = $this->req->budget_max ? number_format($this->req->budget_max, 2) : '—';
        return "{$sym}{$min} - {$sym}{$max}";
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Procurement Approved: '.$this->req->title)
            ->greeting('Hello '.$notifiable->name)
            ->line('A procurement request has been approved.')
            ->line('Title: '.$this->req->title)
            ->line('Budget: '.$this->moneyRange())
            ->action('View Request', url('/procure/requests/'.$this->req->id))
            ->line('You are receiving this as an interested stakeholder.');
    }
}
