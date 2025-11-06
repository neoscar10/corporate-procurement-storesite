<div class="card card-bg-fill h-100">
    <div class="card-body">
        <h6 class="mb-3">Procurement Preferences</h6>
        @if($company->preference)
            <dl class="row mb-0">
                <dt class="col-6 text-muted">Type</dt>
                <dd class="col-6">{{ ucfirst($company->preference->procurement_type ?? '—') }}</dd>

                <dt class="col-6 text-muted">Frequency</dt>
                <dd class="col-6">{{ $company->preference->frequency ?? '—' }}</dd>

                <dt class="col-6 text-muted">Avg Budget</dt>
                <dd class="col-6">{{ $company->preference->avg_monthly_budget ?? '—' }}</dd>

                <dt class="col-6 text-muted">Payment Terms</dt>
                <dd class="col-6">{{ $company->preference->preferred_payment_terms ?? '—' }}</dd>
            </dl>
        @else
            <p class="text-muted mb-0">No data submitted.</p>
        @endif
    </div>
</div>