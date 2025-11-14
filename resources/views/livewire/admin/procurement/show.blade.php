@php $tz = auth()->user()->timezone ?? config('app.timezone', 'UTC'); @endphp
@php
$statusCls = match ($status) {
    'published' => 'badge bg-success-subtle text-success',
    'approved' => 'badge bg-primary-subtle text-primary',
    'pending', 'pending_approval' => 'badge bg-info-subtle text-info',
    'draft' => 'badge bg-secondary-subtle text-secondary',
    'rejected' => 'badge bg-danger-subtle text-danger',
    'cancelled', 'canceled' => 'badge bg-dark-subtle text-dark',
    default => 'badge bg-secondary-subtle text-secondary',
};
@endphp


<div class="container-fluid" wire:key="sa-procure-show-{{ $req->id }}-v{{ $version }}">
    <x-ui.page-header :title="$req->title" :subtitle="'PR-#' . $req->id">
        <x-slot:actions>
            <span class="{{ $statusCls }}">{{ strtoupper($status) }}</span>
            <span class="badge bg-light text-dark me-2">
                Company: {{ $req->company?->name ?? '—' }}
            </span>
            <a href="{{ route('superadmin.procure.requests.index') }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row">
        
        <div class="col-md-8">
            {{-- SUMMARY (read-only mirror) --}}
            <div class="card" wire:key="sa-summary-{{ $req->id }}">
                <div class="card-header">
                    <h5 class="mb-0">Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted">Type</div>
                            <div class="text-uppercase">{{ $req->type }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Priority</div>
                            <div class="text-capitalize">{{ $req->priority }}</div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted">Desired Response</div>
                            <div>
                                {{ $req->desired_response_at ? $req->desired_response_at->timezone($tz)->format('D, j M Y · g:i a') : '—' }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Expected Delivery</div>
                            <div>
                                {{ $req->expected_delivery_at ? $req->expected_delivery_at->timezone($tz)->format('D, j M Y · g:i a') : '—' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="text-muted">Budget (Min)</div>
                            <div>{{ $req->budget_min ? number_format($req->budget_min, 2) : '—' }}
                                {{ $req->currency ?? '' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted">Budget (Max)</div>
                            <div>{{ $req->budget_max ? number_format($req->budget_max, 2) : '—' }}
                                {{ $req->currency ?? '' }}</div>
                        </div>

                        <div class="col-12">
                            <div class="text-muted">Notes</div>
                            <div class="text-wrap">{{ $req->notes ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ITEMS (read-only mirror of company table) --}}
            <div class="card mt-3" wire:key="sa-items-card-{{ $req->id }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items</h5>
                    <span class="badge bg-light text-dark">Total: {{ $req->items->count() }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive" wire:key="sa-items-table-{{ $req->id }}">
                        <table class="table table-bordered table-nowrap align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Kind</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Budget</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($req->items as $it)
                                                                                                                                    @php
                                    $raw = $it->status ?? 'draft';
                                    $statusraw = $raw instanceof \BackedEnum ? strtolower($raw->value)
                                        : ($raw instanceof \UnitEnum ? strtolower($raw->name) : strtolower((string) $raw));
                                    $statusClass = match ($statusraw) {
                                        'draft' => 'badge bg-warning-subtle text-warning',
                                        'pending', 'pending_approval' => 'badge bg-secondary-subtle text-secondary',
                                        'approved' => 'badge bg-info-subtle text-info',
                                        'published' => 'badge bg-success-subtle text-success',
                                        'rejected' => 'badge bg-danger-subtle text-danger',
                                        'cancelled', 'canceled' => 'badge bg-dark-subtle text-dark',
                                        default => 'badge bg-secondary-subtle text-secondary',
                                    };
                                    $sym = $req->currency === 'INR' ? '₹' : '';
                                    @endphp
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td class="text-capitalize">{{ $it->kind }}</td>
                                        <td class="text-truncate" style="max-width: 300px;" title="{{ $it->name }}">
                                            <a class="text-decoration-underline" href="{{ route('admin.procure.requests.items.show', [$req->id, $it->id]) }}">
                                                {{ $it->name }}
                                            </a>
                                        </td>
                                        <td>{{ $it->budget_amount ? $sym . number_format($it->budget_amount, 2) : '—' }}
                                        </td>
                                        <td><span class="{{ $statusClass }}">{{ strtoupper($statusraw) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No items.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: APPROVALS (read-only mirror) --}}
        <div class="col-md-4">
            <div class="card" wire:key="sa-approvals-card-{{ $req->id }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dummy card</h5>
                   
                </div>
                <div class="card-body">
                    @if($mergedRows->isEmpty())
                        <div class="text-muted">this section will have cards related to quotation management</div>
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
        $st = strtolower((string) ($row->status ?? 'pending'));
        $cls = match ($st) {
            'approved' => 'badge bg-success-subtle text-success',
            'rejected' => 'badge bg-danger-subtle text-danger',
            'pending' => 'badge bg-secondary-subtle text-secondary',
            default => 'badge bg-secondary-subtle text-secondary',
        };
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $row->name }}
                                                    <span class="{{ $cls }} ms-1">{{ strtoupper($st) }}</span>
                                                </div>
                                                <span class="text-muted small">{{ $row->email }}</span>
                                            </td>
                                            <td class="text-wrap text-break"
                                                style="overflow-wrap:anywhere; white-space:normal;">
                                                {{ $row->comment ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>