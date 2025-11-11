<div class="card mb-3">
    <div class="card-header">
        <h6 class="mb-0">Summary</h6>
    </div>

    @php
        $kindLower = strtolower($kind ?? ($item->kind ?? ''));
        $badgeClass = match ($kindLower) {
            'service' => 'badge bg-info-subtle text-info',
            'product' => 'badge bg-primary-subtle text-primary',
            default => 'badge bg-secondary-subtle text-secondary'
        };

        $qtyVal = $item->quantity ?? null;
        $qtyStr = ($qtyVal !== null && $qtyVal !== '') ? (int) $qtyVal : '—';

        $unitStr = $item->unit ?: '—';

        // Use your $money(...) helper if available; otherwise format safely.
        $budgetStr = isset($money)
            ? ($money($item->budget_amount ?? null) ?? '—')
            : (($item->budget_amount ?? null) !== null ? number_format((float) $item->budget_amount, 2) : '—');

        $requiredOn = optional($item->date_required)?->format('F j, Y') ?: '—';
        $kindLabel = ucfirst($kindLower ?: '—');
    @endphp

    <div class="card-body">
        {{-- Meta tiles --}}
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Kind</div>
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-shape-outline text-primary"></i>
                    <span class="{{ $badgeClass }}">{{ $kindLabel }}</span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Quantity</div>
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-counter text-primary"></i>
                    <span class="fw-semibold">{{ $qtyStr }}</span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Unit</div>
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-ruler-square text-primary"></i>
                    <span class="fw-semibold">{{ $unitStr }}</span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Budget</div>
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-cash-multiple text-info"></i>
                    <span class="fw-semibold text-truncate" title="{{ $budgetStr }}">{{ $budgetStr }}</span>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Required</div>
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-calendar-month text-info"></i>
                    <span class="fw-semibold text-truncate" title="{{ $requiredOn }}">{{ $requiredOn }}</span>
                </div>
            </div>
        </div>
    </div>
</div>