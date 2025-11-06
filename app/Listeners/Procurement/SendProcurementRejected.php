<?php 
namespace App\Listeners\Procurement;

use App\Events\Procurement\ProcurementRejected;
use App\Services\Support\NotificationService;

class SendProcurementRejected
{
    public function __construct(protected NotificationService $notify) {}
    public function handle(ProcurementRejected $event): void
    {
        $this->notify->procurementRejected($event->request, $event->byUserId);
    }
}
