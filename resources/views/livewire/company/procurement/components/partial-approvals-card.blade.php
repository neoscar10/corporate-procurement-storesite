{{-- Approvals card (inlined, no child component) --}}
        <div class="card" wire:key="approvals-card-{{ $req->id }}">
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
                        <button type="button" class="btn btn-success btn-sm text-light waves-effect waves-light text-nowrap"
                            wire:click="publishNow" wire:loading.attr="disabled" wire:target="publishNow">
                            <span wire:loading.remove wire:target="publishNow">
                                <i class="mdi mdi-check-decagram"></i> Publish
                            </span>
                            <span wire:loading wire:target="publishNow">
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
                        <button type="button" class="btn btn-primary btn-sm text-light waves-effect waves-light text-nowrap"
                            wire:click="publish" wire:loading.attr="disabled" wire:target="publish">
                            <span wire:loading.remove wire:target="publish">
                                <i class="mdi mdi-send-check-outline"></i> Publish
                            </span>
                            <span wire:loading wire:target="publish">
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
                                    <th>Comment</th>
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
                                        <td class="text-wrap text-break" style="overflow-wrap:anywhere; white-space:normal;">
                                            {{ $row->comment ?? '—' }}
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


        </div>
