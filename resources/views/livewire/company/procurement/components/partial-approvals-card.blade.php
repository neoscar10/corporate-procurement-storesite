{{-- Approvals card (inlined, no child component) --}}
        <div class="card mt-3" wire:key="approvals-card-{{ $req->id }}">
            <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                <h5 class="mb-0 me-2">Approvals</h5>
        
                @php
$hasApprovalRows = $req->approvals->count() > 0;
$meRow = $mergedRows->firstWhere('is_me', true);
$meIsPending = $hasApprovalRows && $meRow
    && strtolower((string) ($meRow->status ?? '')) === 'pending'
    && $status !== 'published';
                @endphp
        
                <div class="d-flex gap-2 flex-wrap ms-auto">
                    {{-- Company Admin can publish at any time --}}
                    @if($canPublishNow && $status !== 'published' && $req->items->count() > 0)
                        {{-- Fast-track publish (Company Admin) --}}
                        <button type="button" class="btn btn-success btn-sm text-light waves-effect waves-light text-nowrap"
                            wire:click="attemptPublishNow" wire:loading.attr="disabled" wire:target="attemptPublishNow">
                            <span wire:loading.remove wire:target="attemptPublishNow">
                                <i class="mdi mdi-check-decagram"></i> Publish
                            </span>
                            <span wire:loading wire:target="attemptPublishNow">
                                <x-ui.spinner size="sm" text="Publishing..." />
                            </span>
                        </button>
                    @endif
        
                    {{-- Creator-only: request approvals (create rows + notify) --}}
                    @if($isCreator && $status !== 'published' && $req->items->count() > 0)
                        <button type="button" class="btn btn-outline-primary btn-sm material-shadow-none text-nowrap"
                            wire:click="requestApprovals" wire:loading.attr="disabled" wire:target="requestApprovals">
                            <span wire:loading.remove wire:target="requestApprovals">
                                <i class="mdi mdi-account-check-outline"></i> Request Approvals
                            </span>
                            <span wire:loading wire:target="requestApprovals">
                                <x-ui.spinner size="sm" text="Sending..." />
                            </span>
                        </button>
                    @endif
        
                    {{-- Creator-only: normal publish after approvals fulfilled --}}
                    @if($canPublish)
                        {{-- Normal publish (creator after approvals) --}}
                        <button type="button" class="btn btn-primary btn-sm text-light waves-effect waves-light text-nowrap"
                            wire:click="attemptPublish" wire:loading.attr="disabled" wire:target="attemptPublish">
                            <span wire:loading.remove wire:target="attemptPublish">
                                <i class="mdi mdi-send-check-outline"></i> Publish
                            </span>
                            <span wire:loading wire:target="attemptPublish">
                                <x-ui.spinner size="sm" text="Publishing..." />
                            </span>
                        </button>
                    @endif
        
                    {{-- Approver actions (only after approvals were requested) --}}
                    @if($meIsPending)
                        <div class="btn-group btn-group-sm flex-shrink-0">
                            <button type="button" class="btn btn-soft-success waves-effect text-nowrap"
                                wire:click="openApproveConfirm">
                                <i class="mdi mdi-check"></i> Approve
                            </button>
                            <button type="button" class="btn btn-soft-danger waves-effect text-nowrap" wire:click="openReject">
                                <i class="mdi mdi-close"></i> Reject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        
            <div class="card-body">
                @if($mergedRows->isEmpty())
                    <div class="text-muted">No approvers resolved for this request.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>Approver</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mergedRows as $row)
                                                                @php
        $statusNorm = strtolower((string) ($row->status ?? 'pending'));
        $badgeCls = match ($statusNorm) {
            'approved' => 'badge bg-success-subtle text-success',
            'rejected' => 'badge bg-danger-subtle text-danger',
            'pending' => 'badge bg-secondary-subtle text-secondary',
            default => 'badge bg-secondary-subtle text-secondary',
        };
        $badgeText = strtoupper($statusNorm);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $row->name }}
                                                <span class="{{ $badgeCls }} ms-1">{{ $badgeText }}</span>
                                            </div>
                                            <span class="text-muted small">{{ $row->email }}</span>
                                        </td>
                                        {{-- Comment / Reason --}}
                                        <td>
                                            @if($row->comment && $row->status === 'rejected')
                                                <button class="btn btn-primary btn-sm text-light waves-effect waves-light"
                                                    wire:click="openReason({{ $row->approver_id }})">
                                                    <i class="mdi mdi-eye-outline align-middle me-1"></i> View Reason
                                                </button>
                                            @else
                                            <span class="text-muted"></span>
                                            @endif
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        
            {{-- Approve Confirm (bind via wire:model.live; no wire:ignore) --}}
            <x-ui.confirm id="approveConfirm-{{ $req->id }}" size="md" wire:model.live="confirmApproveOpen"
                wire:key="approveConfirm-{{ $req->id }}">
                <x-slot:title>Confirm Approval</x-slot:title>
                <div>Are you sure you want to <span class="fw-semibold text-success">approve</span> this request?</div>
                <x-slot:confirm>
                    <button type="button" class="btn btn-success text-light waves-effect waves-light" wire:click="approve"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Approve</span>
                        <span wire:loading><x-ui.spinner size="sm" text="Working..." /></span>
                    </button>
                </x-slot:confirm>
            </x-ui.confirm>
            
            {{-- Reject with reason (bind via wire:model.live; no wire:ignore) --}}
            <x-ui.modal id="rejectModal-{{ $req->id }}" wire:model.live="rejectOpen" size="md"
                wire:key="rejectModal-{{ $req->id }}">
                <x-slot:title>Reject Request</x-slot:title>
                <div class="mb-2">
                    <label class="form-label">Reason</label>
                    <textarea class="form-control" rows="3" wire:model.defer="rejectComment"
                        placeholder="Brief reason for rejection"></textarea>
                    @error('rejectComment')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <x-slot:footer>
                    <button type="button" class="btn btn-ghost-secondary material-shadow-none"
                        wire:click="$set('rejectOpen', false)">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger text-light waves-effect waves-light" wire:click="reject"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Reject</span>
                        <span wire:loading><x-ui.spinner size="sm" text="Working..." /></span>
                    </button>
                </x-slot:footer>
            </x-ui.modal>

            {{-- View Reason (Approver note) --}}
            <x-ui.modal id="viewReasonModal" :show="$viewReasonOpen" size="md" wire:ignore.self
                wire:key="view-reason-modal-{{ $req->id }}">
                <x-slot:title>
                    <div class="d-flex align-items-center gap-2">
                        <i class="mdi mdi-comment-text-outline text-primary fs-5"></i>
                        <span>Approval Note</span>
                    </div>
                </x-slot:title>
            
                @php
$row = $req->approvals->firstWhere('approver_id', $viewReasonBy);
$status = strtolower($row->status ?? '');
[$badgeCls, $badgeTxt] = match ($status) {
    'approved' => ['badge bg-success-subtle text-success', 'APPROVED'],
    'rejected' => ['badge bg-danger-subtle text-danger', 'REJECTED'],
    'pending' => ['badge bg-secondary-subtle text-secondary', 'PENDING'],
    default => ['badge bg-secondary-subtle text-secondary', strtoupper($status ?: 'PENDING')],
};
$when = $row?->approved_at ?: $row?->rejected_at;
$whenStr = $when ? \Carbon\Carbon::parse($when)->format('F j, Y • g:i A') : '—';
                @endphp
            
                <div class="mb-3 d-flex align-items-start gap-3">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary">
                            <i class="mdi mdi-account-outline"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">
                            {{ $row?->approver?->name ?? 'User #' . $viewReasonBy }}
                            <span class="{{ $badgeCls }} ms-2 align-middle">{{ $badgeTxt }}</span>
                        </div>
                        <div class="text-muted small">
                            {{ $row?->approver?->email ?? '—' }} &middot; {{ $whenStr }}
                        </div>
                    </div>
                </div>
            
                <div class="border rounded bg-light-subtle p-3" >
                    {{ $viewReasonText }}
                </div>
            
                <x-slot:footer>
                    <div class="d-flex w-100 justify-content-between">
                        <button class="btn btn-light material-shadow-none" data-bs-dismiss="modal"
                            wire:click="$set('viewReasonOpen', false)">
                            Close
                        </button>
                    </div>
                </x-slot:footer>
            </x-ui.modal>

            {{-- COnditional message modal for publishing --}}
            <x-ui.modal id="companyStatusModal-{{ $req->id }}" wire:model.live="companyStatusModalOpen" size="md"
                wire:key="company-status-modal-{{ $req->id }}" wire:ignore.self>
                <x-slot:title>
                    <div class="d-flex align-items-center gap-2">
                        <i class="mdi mdi-domain text-primary fs-5"></i>
                        <span>Publishing Blocked</span>
                    </div>
                </x-slot:title>
            
                <div class="mb-2">
                    {{ $companyStatusMessage }}
                </div>
            
                <x-slot:footer>
                    <button type="button" class="btn btn-light material-shadow-none"
                        wire:click="$set('companyStatusModalOpen', false)">
                        Close
                    </button>
            
                    @if($companyNeedsOnboarding)
                        <a href="{{ route('company.onboarding') }}" class="btn btn-primary text-light waves-effect waves-light">
                            Go to Onboarding
                        </a>
                    @endif
                </x-slot:footer>
            </x-ui.modal>
            
            {{-- anchor to safely pass the modal id to JS without Blade inside the script --}}
            <div id="companyStatusModalAnchor-{{ $req->id }}" data-modal-id="companyStatusModal-{{ $req->id }}"></div>

            {{-- Force-open Approvals confirm via browser events (same pattern as Items) --}}
            <script>
                window.addEventListener('open-approve-confirm', () => {
                    const el = document.getElementById('approveConfirm-{{ $req->id }}');
                    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
                });

                window.addEventListener('open-reject-modal', () => {
                    const el = document.getElementById('rejectModal-{{ $req->id }}');
                    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
                });
            </script>

            <script>
                window.addEventListener('open-view-reason-js', () => {
                    const el = document.getElementById('viewReasonModal');
                    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
                });

                window.addEventListener('close-view-reason-js', () => {
                    const el = document.getElementById('viewReasonModal');
                    if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
                });

                // keep Livewire state in sync if user closes via X/ESC/backdrop
                document.addEventListener('hidden.bs.modal', (e) => {
                    if (e.target && e.target.id === 'viewReasonModal') {
                        const root = e.target.closest('[wire\\:id]');
                        const comp = root ? window.Livewire?.find(root.getAttribute('wire:id')) : null;
                        if (comp) comp.set('viewReasonOpen', false);
                    }
                });
            </script>
            
            @push('scripts')
<script>
(function () {
  // read the id from the nearby anchor to avoid Blade in JS
  const anchor = document.getElementById('companyStatusModalAnchor-{{ $req->id }}');
  const modalId = anchor ? anchor.dataset.modalId : 'companyStatusModal-{{ $req->id }}';

  window.addEventListener('open-company-status-modal', () => {
    const el = document.getElementById(modalId);
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
  });

  window.addEventListener('close-company-status-modal', () => {
    const el = document.getElementById(modalId);
    if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
  });

  // keep Livewire boolean in sync if user closes via X/ESC/backdrop
  document.addEventListener('hidden.bs.modal', (e) => {
    if (e.target && e.target.id === modalId) {
      const root = e.target.closest('[wire\\:id]');
      const comp = root ? window.Livewire?.find(root.getAttribute('wire:id')) : null;
      if (comp) comp.set('companyStatusModalOpen', false);
    }
  });
})();
</script>
@endpush






        </div>
