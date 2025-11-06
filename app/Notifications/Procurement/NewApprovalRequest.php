<?php
namespace App\Notifications\Procurement;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\{Notification,Messages\MailMessage};
use App\Models\Procurement\ProcurementRequest;

class NewApprovalRequest extends Notification implements ShouldQueue
{
    use Queueable;
    public function __construct(public ProcurementRequest $req) {}
    public function via($notifiable){ return ['mail']; }
    public function toMail($notifiable){
        return (new MailMessage)
            ->subject('Approval requested: '.$this->req->title)
            ->greeting('Hi '.$notifiable->name)
            ->line('A procurement request requires your approval.')
            ->line('Title: '.$this->req->title)
            ->line('Budget: '.($this->req->currency.' '.number_format($this->req->budget_min ?? 0).' - '.number_format($this->req->budget_max ?? 0)))
            ->action('Open Request', url()->to('/procure/requests/'.$this->req->id))
            ->line('Thank you.');
    }
}
