@php
    use App\Models\Company\CompanyMember;

    $me = auth()->user();
    $companyCtxId = isset($companyId) ? (int) $companyId : (int) data_get($company ?? null, 'id');

    $canInvite = false;

    if ($me && $companyCtxId) {
        // True CompanyAdmin for this company (pivot)
        $canInvite = CompanyMember::where('company_id', $companyCtxId)
            ->where('user_id', (int) $me->id)
            ->where('role_label', 'CompanyAdmin')
            ->where('is_active', true)
            ->exists();

        // Fallbacks: direct permission or platform super-admin
        if (!$canInvite && method_exists($me, 'hasPermission')) {
            $canInvite = $me->hasPermission('manage_users') || (bool) $me->is_admin;
        }
    }
@endphp

<div class="text-center py-2">
    <div class="mb-3">
        <i class="mdi mdi-check-decagram-outline text-success" style="font-size:56px;"></i>
    </div>
    <h5 class="mb-2">Onboarding complete</h5>
    <p class="text-muted mb-4">
        Your company profile is under review. You can add company users as you await approval.
    </p>

    @if($canInvite)
        <button type="button" class="btn btn-primary waves-effect waves-light"
            wire:click="$dispatch('invite.open', { companyId: {{ (int) ($companyId ?? 0) }} })">
            Invite users
        </button>
    @endif
</div>