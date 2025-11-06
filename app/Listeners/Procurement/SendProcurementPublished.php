<?php // SendProcurementPublished.php
namespace App\Listeners\Procurement;

use App\Events\Procurement\ProcurementPublished;
use App\Services\Support\NotificationService;

class SendProcurementPublished
{
    public function __construct(protected NotificationService $notify) {}
    public function handle(ProcurementPublished $event): void
    {
        $this->notify->procurementPublished($event->request);
    }
}
