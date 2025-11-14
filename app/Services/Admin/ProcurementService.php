<?php

namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Procurement\{ProcurementRequest, ProcurementItem};
use App\Enums\Procurement\RequestStatus;

class ProcurementService
{
    /**
     * Super Admin list: only published, newest first.
     */
    public function listRequests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ProcurementRequest::query()
            ->where('status', RequestStatus::PUBLISHED) // enum-safe
            ->withCount('items')
            ->with([
                'company:id,brand_name,legal_name', // ✅ singular relation, correct columns
                'creator:id,name',
            ])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Full request for the Show page (cross-company).
     */
    public function getRequest(int $id): ProcurementRequest
    {
        return ProcurementRequest::with([
                'company:id,brand_name,legal_name', // ✅ fix here too
                'creator:id,name,email',
                'items.productSpec',
                'items.serviceSpec',
                'attachments',
                'approvals.approver:id,name,email',
            ])->findOrFail($id);
    }

    // Fetch proc req items
     public function getItem(int $requestId, int $itemId): ProcurementItem
    {
        return ProcurementItem::with([
                'request:id,company_id,title,status',
                'request.company:id,brand_name as name',
                'productSpec',
                'serviceSpec',
                'attachments',
            ])
            ->where('procurement_request_id', $requestId)
            ->findOrFail($itemId);
    }
}
