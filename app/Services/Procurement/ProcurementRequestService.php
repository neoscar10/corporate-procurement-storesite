<?php
namespace App\Services\Procurement;

use App\Models\Procurement\{ProcurementRequest, ProcurementItem, ProcurementApproval};
use App\Enums\Procurement\{RequestStatus,ItemKind};
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Services\Support\NotificationService;
use App\Services\Support\PermissionsRegistry; // if you keep a central registry
use App\Models\Company\{ApprovalWorkflow, ApprovalStep, Company};

class ProcurementRequestService
{
    public function __construct(
        protected NotificationService $notify
    ) {}

    public function createDraft(int $companyId, int $creatorId, array $step1, array $step2 = []): ProcurementRequest
    {
        return DB::transaction(function() use ($companyId,$creatorId,$step1,$step2) {
            $req = new ProcurementRequest([
                'company_id' => $companyId,
                'created_by' => $creatorId,
                'title' => $step1['title'],
                'type'  => $step1['type'],
                'priority' => $step1['priority'],
                'desired_response_at' => $step1['desired_response_at'] ?? null,
                'expected_delivery_at'=> $step1['expected_delivery_at'] ?? null,
                'currency' => $step2['currency'] ?? 'INR',
                'budget_min' => $step2['budget_min'] ?? null,
                'budget_max' => $step2['budget_max'] ?? null,
                'payment_terms' => $step2['payment_terms'] ?? null,
                'delivery_location' => $step2['delivery_location'] ?? null,
                'preferred_vendor_region' => $step2['preferred_vendor_region'] ?? null,
                'notes' => $step2['notes'] ?? null,
                'status' => RequestStatus::DRAFT,
                'stage' => 'building',
            ]);
            $req->save();

            return $req;
        });
    }

    public function updateDraft(ProcurementRequest $req, array $data): ProcurementRequest
    {
        $req->fill($data)->save();
        return $req;
    }

    /** Add item shell (product/service/contact_admin) — starts as draft and can be saved step-wise */
    public function addItemDraft(ProcurementRequest $req, array $payload): ProcurementItem
    {
        return DB::transaction(function() use ($req,$payload){
            $item = $req->items()->create([
                'company_id' => $req->company_id,
                'kind' => $payload['kind'],
                'name' => $payload['name'],
                'short_description' => $payload['short_description'] ?? null,
                'priority' => $payload['priority'] ?? null,
                'unit' => $payload['unit'] ?? null,
                'quantity' => $payload['quantity'] ?? 1,
                'date_required' => $payload['date_required'] ?? null,
                'budget_amount' => $payload['budget_amount'] ?? null,
                'service_budget_mode' => $payload['service_budget_mode'] ?? null,
                'service_payment_type' => $payload['service_payment_type'] ?? null,
                'is_draft' => true,
                'status' => 'draft',
                'detail_completed_at' => now(),
            ]);

            $req->increment('items_count');
            $this->invalidateApprovals($req, 'item_added');
            return $item;
        });
    }

    /** Save specs (branch to product/service) */
    public function saveItemSpecs(ProcurementItem $item, array $specs): void
        {
        if ($item->kind === ItemKind::PRODUCT->value) {
            $urls = [];
            if (isset($specs['product_urls'])) {
                $urls = array_values(array_filter(
                    array_map(fn($u) => trim((string)$u), (array) $specs['product_urls']),
                    fn($u) => $u !== ''
                ));
            }

            $item->productSpec()->updateOrCreate(
                ['procurement_item_id' => $item->id],
                [
                    'brand'                  => $specs['brand'] ?? null,
                    'model'                  => $specs['model'] ?? null,
                    'quality_level'          => $specs['quality_level'] ?? null,
                    'packaging_requirement'  => $specs['packaging_requirement'] ?? null,
                    'inspection_required'    => (bool)($specs['inspection_required'] ?? false),
                    'technical_specs'        => $specs['technical_specs'] ?? null,
                    'product_urls'           => !empty($urls) ? $urls : null, // <-- add this
                ]
            );  
        } elseif ($item->kind === ItemKind::SERVICE->value) {
            $item->serviceSpec()->updateOrCreate(
                ['procurement_item_id'=>$item->id],
                [
                    'scope_of_work'  => $specs['scope_of_work'] ?? null,
                    'deliverables'   => $specs['deliverables'] ?? null,
                    'key_personnels' => $specs['key_personnels'] ?? null,
                ]
            );
        }

        $this->invalidateApprovals($item->request, 'item_specs_updated');
        $item->update(['spec_completed_at'=>now()]);
    }

    /** Mark attachments step done and finalize draft item */
    public function finalizeItem(ProcurementItem $item): ProcurementItem
    {
        $item->update([
            'attachments_completed_at'=>now(),
            'is_draft'=>false,
            'status'=>'ready',
            'completed_at'=>now(),
        ]);
        return $item->fresh();
    }

    /** Submit request for approvals: build approver rows and notify */
    public function submitForApproval(ProcurementRequest $req): void
    {
        DB::transaction(function() use ($req){
            // Build approver list from workflow or permission holders
            $approverIds = $this->resolveApprovers($req);

            // Create approval rows as pending
            foreach ($approverIds as $uid) {
                $req->approvals()->firstOrCreate(
                    ['approver_id' => (int) $uid],
                    ['status' => 'pending']
                );
            }

            // Default: set request into pending_approval
            $req->update([
                'status' => RequestStatus::PENDING_APPROVAL,
                'stage'  => 'awaiting_approvals'
            ]);

            // If the creator is required to approve, auto-approve their row for better UX
            $notifyIds = $approverIds;
            if (in_array((int)$req->created_by, $approverIds, true)) {
                $creatorRow = $req->approvals()->where('approver_id', $req->created_by)->first();
                if ($creatorRow && $creatorRow->status !== 'approved') {
                    $creatorRow->update([
                        'status'      => 'approved',
                        'approved_at' => now(),
                        'comment'     => 'Auto-approved (creator)'
                    ]);
                }
                // do not notify the creator
                $notifyIds = array_values(array_diff($notifyIds, [(int)$req->created_by]));
            }

            // If everything is already approved (e.g., creator was the only approver), finalize
            $pending = $req->approvals()->where('status','pending')->count();
            if ($pending === 0) {
                $req->update([
                    'status'      => RequestStatus::APPROVED,
                    'approved_at' => now(),
                    'stage'       => 'approved',
                ]);
                event(new \App\Events\Procurement\ProcurementApproved($req));
                return;
            }

            // Otherwise, notify remaining approvers only (exclude creator if auto-approved)
            if (!empty($notifyIds)) {
                event(new \App\Events\Procurement\ProcurementApprovalRequested($req, $notifyIds));
            }
        });
    }


    /** Approver action: approve or reject. If Company Admin approves, auto-approve whole request. */
    public function recordApproval(ProcurementRequest $req, User $approver, bool $approve, ?string $comment=null): void
    {
        DB::transaction(function() use ($req,$approver,$approve,$comment){

            // Ensure ALL expected approvers have rows before any action,
            // so "all must approve" remains true even if approvals weren't explicitly requested.
            $expected = $this->resolveApprovers($req);
            foreach ($expected as $uid) {
                $req->approvals()->firstOrCreate(
                    ['approver_id' => (int) $uid],
                    ['status' => 'pending']
                );
            }

            // Lock my row and update
            $row = $req->approvals()
                ->where('approver_id', $approver->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['approver_id' => $approver->id],
                    ['status' => 'pending']
                );

            if ($approve) {
                $row->update([
                    'status'      => 'approved',
                    'approved_at' => now(),
                    'comment'     => $comment,
                ]);

                if ($this->isCompanyAdminFor($approver, $req->company_id)) {
                    // Company Admin: auto-approve all, approve request
                    $req->approvals()->where('status','pending')->update(['status'=>'approved','approved_at'=>now()]);
                    $req->update(['status'=>RequestStatus::APPROVED,'approved_at'=>now(),'stage'=>'approved']);
                    event(new \App\Events\Procurement\ProcurementApproved($req));
                } else {
                    // If every row is approved, approve request
                    $pending = $req->approvals()->where('status','pending')->count();
                    if ($pending === 0) {
                        $req->update(['status'=>RequestStatus::APPROVED,'approved_at'=>now(),'stage'=>'approved']);
                        event(new \App\Events\Procurement\ProcurementApproved($req));
                    } elseif (self::statusString($req->status) !== 'pending_approval') {
                        // Set request status to pending_approval if not already
                        $req->update(['status'=>RequestStatus::PENDING_APPROVAL,'stage'=>'awaiting_approvals']);
                    }
                }
            } else {
                $row->update([
                    'status'      => 'rejected',
                    'rejected_at' => now(),
                    'comment'     => $comment,
                ]);
                $req->update(['status'=>RequestStatus::REJECTED,'stage'=>'rejected']);
                event(new \App\Events\Procurement\ProcurementRejected($req, $approver->id));
            }
        });
    }


    /** After approval, company can publish (visible to Super Admin/etc.) */
    public function publish(ProcurementRequest $req): void
    {
        if ($req->status !== RequestStatus::APPROVED) {
            throw new \RuntimeException('Request not approved.');
        }
        $req->update(['status'=>RequestStatus::PUBLISHED,'published_at'=>now(),'stage'=>'published']);
        // $this->notify->procurementPublished($req);
        event(new \App\Events\Procurement\ProcurementPublished($req));
    }

    public function publishByAdmin(ProcurementRequest $req, User $by): void
    {
        if (! $this->isCompanyAdminFor($by, $req->company_id)) {
            throw new \RuntimeException('Only a Company Admin can publish immediately.');
        }

        DB::transaction(function () use ($req) {
            // Mark any pending approvals as approved (for audit consistency)
            $req->approvals()->where('status', 'pending')->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'comment'     => 'Auto-approved by Company Admin.',
            ]);

            // If not already approved, set as approved now
            if (($req->approved_at ?? null) === null) {
                $req->update([
                    'status'      => RequestStatus::APPROVED,
                    'approved_at' => now(),
                    'stage'       => 'approved',
                ]);
                event(new \App\Events\Procurement\ProcurementApproved($req));
            }

            // Publish
            $req->update([
                'status'       => RequestStatus::PUBLISHED,
                'published_at' => now(),
                'stage'        => 'published',
            ]);

            event(new \App\Events\Procurement\ProcurementPublished($req));
        });
    }

    /** Safe status normalizer for enums/strings */
    public static function statusString($raw): string
    {
        if ($raw instanceof \BackedEnum) return strtolower($raw->value);
        if ($raw instanceof \UnitEnum)   return strtolower($raw->name);
        return strtolower((string) $raw);
    }


    /** Derive approvers from ApprovalWorkflow/Steps or fallback to permission holders. */
    public function resolveApprovers(ProcurementRequest $req): array
{
    // 1) Prefer workflow (threshold-based)
    $flow = \App\Models\Company\ApprovalWorkflow::where('company_id', $req->company_id)->first();
    $amount = $req->budget_max ?? $req->budget_min ?? 0;

    $ids = [];
    if ($flow) {
        $steps = \App\Models\Company\ApprovalStep::where('approval_workflow_id', $flow->id)
            ->orderBy('order')
            ->get();

        foreach ($steps as $s) {
            if (is_null($s->threshold_amount) || $amount <= $s->threshold_amount) {
                if ($s->approver_user_id) $ids[] = (int) $s->approver_user_id;
            }
        }
    }

    // 2) Fallback: all users in company with approve_procurement permission (enabled)
    if (empty($ids)) {
        $ids = \App\Models\User::query()
            ->whereHas('companyMembers', function ($q) use ($req) {
                $q->where('company_id', $req->company_id)
                  ->where('is_active', true);
            })
            ->whereHas('permissions', function ($q) {
                $q->where('permissions.name', 'approve_procurement')
                  ->where('user_permission.is_enabled', true);
            })
            ->pluck('users.id')
            ->map(fn ($i) => (int) $i)
            ->all();
    }

    // 3) Normalize (do NOT remove creator)
    $ids = array_values(array_unique($ids));

    if (empty($ids)) {
        return $ids;
    }

    // 4) Exclude any Company Admins (role, company_admin perm, or is_admin)
    $adminIds = \App\Models\User::whereIn('id', $ids)
        ->where(function ($q) use ($req) {
            $q->whereHas('companyMembers', function ($m) use ($req) {
                $m->where('company_id', $req->company_id)
                  ->where('is_active', true)
                  ->whereIn('role_label', ['CompanyAdmin','company_admin','admin','owner']);
            })
            ->orWhereHas('permissions', function ($p) {
                $p->where('permissions.name', 'company_admin')
                  ->where('user_permission.is_enabled', true);
            })
            ->orWhere('is_admin', true);
        })
        ->pluck('id')
        ->map(fn ($i) => (int) $i)
        ->all();

    // Creator stays if present (even if creator), only admins are excluded
    $ids = array_values(array_diff($ids, $adminIds));

    return $ids;
}


    public function isCompanyAdminFor(User $user, int $companyId): bool
    {
        // Prefer relation if it exists
        $viaRole = method_exists($user, 'companyMembers')
            ? $user->companyMembers()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereIn('role_label', ['CompanyAdmin','company_admin','admin','owner'])
                ->exists()
            : \App\Models\Company\CompanyMember::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereIn('role_label', ['CompanyAdmin','company_admin','admin','owner'])
                ->exists();

        $viaPerm = method_exists($user, 'hasPermission') && $user->hasPermission('company_admin');

        return $viaRole || $viaPerm || (bool) ($user->is_admin ?? false);
    }

    // Helper
    public function invalidateApprovals(ProcurementRequest $req, string $reason = 'item_changed'): void
    {
        DB::transaction(function () use ($req) {
            // reset all approval rows to pending
            $req->approvals()->update([
                'status'      => 'pending',
                'approved_at' => null,
                'rejected_at' => null,
                'comment'     => null,
            ]);

            // roll request back to initial state
            $req->forceFill([
                'status'       => RequestStatus::DRAFT,
                'stage'        => 'building',
                'approved_at'  => null,
                'published_at' => null,
            ])->save();
        });
    }

    public function updateItemCore(\App\Models\Procurement\ProcurementItem $item, array $data): void
    {
        DB::transaction(function () use ($item, $data) {
            $item->fill([
                'name'                 => $data['name'] ?? $item->name,
                'short_description'    => $data['short_description'] ?? $item->short_description,
                'priority'             => $data['priority'] ?? $item->priority,
                'unit'                 => $data['unit'] ?? $item->unit,
                'quantity'             => $data['quantity'] ?? $item->quantity,
                'date_required'        => $data['date_required'] ?? $item->date_required,
                'budget_amount'        => $data['budget_amount'] ?? $item->budget_amount,
                'service_budget_mode'  => $data['service_budget_mode'] ?? $item->service_budget_mode,
                'service_payment_type' => $data['service_payment_type'] ?? $item->service_payment_type,
            ])->save();

            // reset approvals + request state
            $this->invalidateApprovals($item->request, 'item_core_updated');
        });
    }



}
