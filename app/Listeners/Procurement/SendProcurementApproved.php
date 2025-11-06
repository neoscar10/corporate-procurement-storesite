<?php // SendProcurementApproved.php
namespace App\Listeners\Procurement;

use App\Events\Procurement\ProcurementApproved;
use App\Services\Support\NotificationService;

class SendProcurementApproved
{
    public function __construct(protected NotificationService $notify) {}
    public function handle(ProcurementApproved $event): void
    {
        $this->notify->procurementApproved($event->request);
    }
}
