@php
    $statusRaw = $req->status instanceof \BackedEnum
        ? strtolower($req->status->value)
        : strtolower((string) $req->status);

    [$statusCls, $statusText, $statusIcon] = match ($statusRaw) {
        'published' => ['badge bg-success-subtle text-success', 'PUBLISHED', 'mdi-check-decagram'],
        'approved' => ['badge bg-primary-subtle text-primary', 'APPROVED', 'mdi-check-circle-outline'],
        'pending_approval',
        'pending' => ['badge bg-info-subtle text-info', 'PENDING APPROVAL', 'mdi-timer-sand'],
        'draft' => ['badge bg-secondary-subtle text-secondary', 'DRAFT', 'mdi-file-document-edit-outline'],
        'rejected' => ['badge bg-danger-subtle text-danger', 'REJECTED', 'mdi-close-octagon-outline'],
        'cancelled', 'canceled' => ['badge bg-dark-subtle text-dark', 'CANCELLED', 'mdi-cancel'],
        default => ['badge bg-light text-muted', strtoupper($statusRaw ?: 'UNKNOWN'), 'mdi-help-circle-outline'],
    };
@endphp

{{-- Place this block immediately below the page header --}}
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <span class="{{ $statusCls }}">
        <i class="mdi {{ $statusIcon }} align-middle me-1"></i>{{ $statusText }}
    </span>

    {{-- Optional: stage chip --}}
    @if(!empty($req->stage))
        <span class="badge bg-light text-dark text-uppercase">
            <i class="mdi mdi-flag-outline align-middle me-1"></i>{{ str_replace('_', ' ', $req->stage) }}
        </span>
    @endif

    {{-- Optional: items count --}}
    <span class="badge bg-light text-muted">
        <i class="mdi mdi-package-variant align-middle me-1"></i>
        {{ $req->items_count ?? ($req->items?->count() ?? 0) }} items
    </span>

    {{-- Optional: approved/published timestamps --}}
    @if($req->approved_at && in_array($statusRaw, ['approved', 'published'], true))
        <span class="badge bg-light text-muted" title="Approved at">
            <i class="mdi mdi-calendar-check-outline align-middle me-1"></i>
            {{ \Illuminate\Support\Carbon::parse($req->approved_at)->format('M j, Y · g:i a') }}
        </span>
    @endif
    @if($req->published_at && $statusRaw === 'published')
        <span class="badge bg-light text-muted" title="Published at">
            <i class="mdi mdi-calendar-star-outline align-middle me-1"></i>
            {{ \Illuminate\Support\Carbon::parse($req->published_at)->format('M j, Y · g:i a') }}
        </span>
    @endif
</div>