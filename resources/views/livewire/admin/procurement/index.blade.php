@php $tz = auth()->user()->timezone ?? config('app.timezone', 'UTC'); @endphp

<div class="container-fluid" wire:key="sa-procure-index">
    <x-ui.page-header title="Procurement Requests (All Companies)" subtitle="Super Admin view">
        <x-slot:actions>
            <span class="badge bg-light text-dark">Total: {{ $rows->total() }}</span>
        </x-slot:actions>
    </x-ui.page-header>
{{-- filters --}}
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label mb-0 small text-muted">Search</label>
                <input type="text" class="form-control" placeholder="Search by PR #, title or company..."
                    wire:model.live.debounce.400ms="search">
            </div>

            <div class="col-md-3">
                <label class="form-label mb-0 small text-muted">Status</label>
                <select class="form-select" wire:model.live="status">
                    <option value="">All</option>
                    <option value="published">Published</option>
                    <option value="approved">Approved</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="draft">Draft</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="canceled">Canceled</option>
                </select>
            </div>

            <div class="col-md-2 ms-auto">
                <label class="form-label mb-0 small text-muted">Per page</label>
                <select class="form-select" wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>
</div>


    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-nowrap align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">PR #</th>
                        <th scope="col">Title</th>
                        <th scope="col">Company</th>
                        <th scope="col">Type</th>
                        <th scope="col">Priority</th>
                        <th scope="col">Items</th>
                        <th scope="col">Created</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                                                                @php
    $statusRaw = $r->status instanceof \BackedEnum ? strtolower($r->status->value) : strtolower((string) $r->status);
    $statusCls = match ($statusRaw) {
        'published' => 'badge bg-success-subtle text-success',
        'approved' => 'badge bg-primary-subtle text-primary',
        'pending', 'pending_approval' => 'badge bg-info-subtle text-info',
        'draft' => 'badge bg-secondary-subtle text-secondary',
        'rejected' => 'badge bg-danger-subtle text-danger',
        'cancelled', 'canceled' => 'badge bg-dark-subtle text-dark',
        default => 'badge bg-secondary-subtle text-secondary',
    };
                        @endphp
                        <tr wire:key="sa-pr-{{ $r->id }}">
                            <th scope="row">PR-#{{ $r->id }}</th>

                            <td class="text-truncate" style="max-width: 320px;" title="{{ $r->title }}">
                                <a  href="{{ route('admin.procure.requests.show', $r->id) }}">{{ $r->title }}</a>

                            </td>

                            <td>
                                <span class="">{{ $r->company?->name ?? '—' }}</span>
                            </td>

                            <td class="text-uppercase">{{ $r->type }}</td>
                            <td class="text-capitalize">{{ $r->priority }}</td>
                            <td><span class="badge bg-light text-dark">{{ $r->items_count }}</span></td>

                            <td>{{ $r->created_at ? $r->created_at->timezone($tz)->format('D, j M Y · g:i a') : '—' }}</td>

                            <td><span class="{{ $statusCls }}">{{ strtoupper($statusRaw) }}</span></td>

                            <td class="text-end">
                                <div class="dropdown">
                                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.procure.requests.show', $r->id) }}">
                                                Open
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-2">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
</div>