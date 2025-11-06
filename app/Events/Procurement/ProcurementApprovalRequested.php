<?php 
namespace App\Events\Procurement;

use App\Models\Procurement\ProcurementRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProcurementApprovalRequested {
    use Dispatchable, SerializesModels;
    public function __construct(public ProcurementRequest $request, public array $approverIds) {}
}
