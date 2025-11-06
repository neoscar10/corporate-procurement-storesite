<div class="card mt-3" wire:key="approvals-card-{{ $req->id }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Approvals</h5>

        @php
            $hasApprovalRows = $req->approvals->count() > 0;           // only after "Request Approvals"
            $meRow = $mergedRows->firstWhere('is_me', true);
            $meIsPending = $hasApprovalRows && $meRow && strtolower($meRow->status) === 'pending' && $status !== 'published';
        @endphp

        <div class="d-flex gap-2">
            {{-- Company Admin can publish at any time --}}
            @if($canPublishNow && $status !== 'published')
                <button class="btn btn-success btn-sm text-light waves-effect waves-light" wire:click="publishNow"
                    wire:loading.attr="disabled" wire:target="publishNow">
                    <span wire:loading.remove wire:target="publishNow">
                        <i class="mdi mdi-check-decagram"></i> Publish Now
                    </span>
                    <span wire:loading wire:target="publishNow">
                        <x-ui.spinner size="sm" text="Publishing..." />
                    </span>
                </button>
            @endif

            {{-- Creator-only: request approvals (create rows + notify) --}}
            @if($isCreator && !$req->approvals->count() && $status !== 'published')
                <button class="btn btn-outline-primary btn-sm material-shadow-none" wire:click="requestApprovals"
                    wire:loading.attr="disabled" wire:target="requestApprovals">
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
                <button class="btn btn-primary btn-sm text-light waves-effect waves-light" wire:click="publish"
                    wire:loading.attr="disabled" wire:target="publish">
                    <span wire:loading.remove wire:target="publish">
                        <i class="mdi mdi-send-check-outline"></i> Publish
                    </span>
                    <span wire:loading wire:target="publish">
                        <x-ui.spinner size="sm" text="Publishing..." />
                    </span>
                </button>
            @endif

            {{-- Approver actions (top of card) only after approvals were requested --}}
            @if($meIsPending)
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-soft-success waves-effect" wire:click="openApproveConfirm">
                        <i class="mdi mdi-check"></i> Approve
                    </button>
                    <button class="btn btn-soft-danger waves-effect" wire:click="openReject">
                        <i class="mdi mdi-close"></i> Reject
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="card-body">
        <x-alerts.flash />

        @php
            $badge = function ($s) {
                $s = is_string($s) ? strtolower($s) : (string) $s;
                return match ($s) {
                    'approved' => ['badge bg-success-subtle text-success', 'APPROVED'],
                    'rejected' => ['badge bg-danger-subtle text-danger', 'REJECTED'],
                    'pending' => ['badge bg-secondary-subtle text-secondary', 'PENDING'],
                    default => ['badge bg-secondary-subtle text-secondary', strtoupper($s)],
                };
            };
        @endphp

        @if($mergedRows->isEmpty())
            <div class="text-muted">No approvers resolved for this request.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Approver</th>
                            <th>Status</th>
                            <th>Approved At</th>
                            <th>Rejected At</th>
                            <th>Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mergedRows as $row)
                            @php [$cls, $txt] = $badge($row->status); @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2">
                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                {{ strtoupper(substr($row->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $row->name }}
                                                @if($row->is_me)
                                                    <span class="badge bg-light text-muted ms-1">You</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small">{{ $row->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="{{ $cls }}">{{ $txt }}</span></td>
                                <td>{{ optional($row->approved_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td>{{ optional($row->rejected_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-truncate" style="max-width:360px;">{{ $row->comment ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Approve Confirm --}}
    <x-ui.confirm id="approveConfirm" size="md" wire:model="confirmApproveOpen">
        <x-slot:title>Confirm Approval</x-slot:title>
        <div>Are you sure you want to <span class="fw-semibold text-success">approve</span> this request?</div>
        <x-slot:confirm>
            <button class="btn btn-success text-light waves-effect waves-light" wire:click="approve"
                wire:loading.attr="disabled" data-bs-dismiss="modal">
                <span wire:loading.remove>Approve</span>
                <span wire:loading><x-ui.spinner size="sm" text="Working..." /></span>
            </button>
        </x-slot:confirm>
    </x-ui.confirm>

    {{-- Reject with reason --}}
    <x-ui.modal id="rejectModal" :show="$rejectOpen" size="md" wire:ignore.self>
        <x-slot:title>Reject Request</x-slot:title>
        <div class="mb-2">
            <label class="form-label">Reason</label>
            <textarea class="form-control" rows="3" wire:model.defer="rejectComment"
                placeholder="Brief reason for rejection"></textarea>
            @error('rejectComment')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <x-slot:footer>
            <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal"
                wire:click="$set('rejectOpen', false)">Cancel</button>
            <button class="btn btn-danger text-light waves-effect waves-light" wire:click="reject"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Reject</span>
                <span wire:loading><x-ui.spinner size="sm" text="Working..." /></span>
            </button>
        </x-slot:footer>
    </x-ui.modal>
</div>

{{-- JS open/close fallbacks (unchanged) --}}
<script>
    window.addEventListener('open-approve-confirm-js', () => {
        const el = document.getElementById('approveConfirm');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });
    window.addEventListener('close-approve-confirm-js', () => {
        const el = document.getElementById('approveConfirm');
        if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
    });
    window.addEventListener('open-reject-modal-js', () => {
        const el = document.getElementById('rejectModal');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });
    window.addEventListener('close-reject-modal-js', () => {
        const el = document.getElementById('rejectModal');
        if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
    });
</script>