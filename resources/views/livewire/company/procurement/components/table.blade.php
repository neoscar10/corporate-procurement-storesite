<div class="card">
    <div class="card-body table-responsive">
        <table class="table align-middle table-hover">
            <thead class="table-light">
                <tr>
                    <th>PR #</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Items</th>
                    <th>Desired Response</th>
                    <th>Expected Delivery</th>
                    <th>Status</th>
                    <th>Stage</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td>PR-#{{ $r->id }}</td>
                        <td class="text-truncate" style="max-width:280px;">
                            <a href="{{ route('company.procure.requests.show', $r->id) }}"
                                class="text-decoration-underline">
                                {{ $r->title }}
                            </a>
                        </td>
                        <td class="text-uppercase">{{ $r->type }}</td>
                        <td class="text-capitalize">{{ $r->priority }}</td>
                        <td><span class="badge bg-light text-dark">{{ $r->items_count }}</span></td>
                        <td>{{ optional($r->desired_response_at)->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>{{ optional($r->expected_delivery_at)->format('Y-m-d H:i') ?? '—' }}</td>
                        <td><span class="badge bg-soft-primary text-uppercase">{{ $r->status }}</span></td>
                        <td>{{ $r->stage }}</td>
                        <td class="text-end">
                            <a href="{{ route('company.procure.requests.show', $r->id) }}"
                                class="btn btn-sm btn-light waves-effect waves-light">Open</a>
                            @if(in_array($r->status->value ?? $r->status, ['draft', 'cancelled', 'canceled'], true))
                                <button class="btn btn-sm btn-outline-danger material-shadow-none"
                                    wire:click="$dispatch('request-delete-ask', {{ $r->id }})">
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">No requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-2">
            {{ $rows->links() }}
        </div>
    </div>
</div>