<x-tables.simple :headers="['Created', 'Company', 'IDs', 'Status', 'Progress', 'Actions']">
    @forelse($companies as $c)
        @php
            $badge = [
                'pending' => 'warning',
                'approved' => 'success',
                'rejected' => 'danger',
                'cancelled' => 'secondary',
            ][$c->status] ?? 'light';
            $p = $c->onboardingProgress;
            $progressSteps = collect([
                'P' => (bool) optional($p)->procurement_done,
                'K' => (bool) optional($p)->kyc_done,
                'B' => (bool) optional($p)->billing_done,
            ]);
            $doneCount = $progressSteps->filter()->count();
        @endphp

        <tr>
            <td class="text-muted">{{ $c->created_at?->format('Y-m-d') }}</td>
            <td>
                <div class="fw-semibold">{{ $c->legal_name ?? $c->brand_name ?? '—' }}</div>
                <div class="text-muted small">#{{ $c->id }}</div>
            </td>
            <td class="text-muted small">
                CIN: {{ $c->cin ?? '—' }}<br>
                PAN: {{ $c->pan ?? '—' }}<br>
                GSTIN: {{ $c->gstin ?? '—' }}
            </td>
            <td>
                <span class="badge bg-{{ $badge }}">{{ ucfirst($c->status) }}</span>
            </td>
            <td>
                <div class="d-flex align-items-center gap-1">
                    @foreach($progressSteps as $label => $ok)
                        <span class="badge rounded-pill {{ $ok ? 'bg-success' : 'bg-light text-muted' }}">{{ $label }}</span>
                    @endforeach
                    <span class="ms-2 text-muted small">{{ $doneCount }}/3</span>
                </div>
            </td>
            <td class="text-nowrap">
                <a href="{{ route('admin.company.requests.show', $c->id) }}" class="btn btn-sm btn-soft-primary">View</a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center text-muted">No companies found.</td>
        </tr>
    @endforelse
</x-tables.simple>