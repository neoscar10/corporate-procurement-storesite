<?php // SendProcurementApprovalRequest.php
namespace App\Listeners\Procurement;

use App\Events\Procurement\ProcurementApprovalRequested;
use App\Services\Support\NotificationService;

class SendProcurementApprovalRequest
{
    public function __construct(protected NotificationService $notify) {}
    public function handle(ProcurementApprovalRequested $event): void
    {
        $this->notify->procurementApprovalRequested($event->request, $event->approverIds);
    }
}
