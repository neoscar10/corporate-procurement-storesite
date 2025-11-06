<div class="card card-bg-fill h-100">
    <div class="card-body">
        <h6 class="mb-3">Company Basic Info</h6>

        @php $c = $company; @endphp

        @if($c)
            <dl class="row mb-0">
                <dt class="col-6 text-muted">Legal Name</dt>
                <dd class="col-6 fw-medium">{{ $c->legal_name ?? '—' }}</dd>

                <dt class="col-6 text-muted">Brand Name</dt>
                <dd class="col-6 fw-medium">{{ $c->brand_name ?? '—' }}</dd>

                <dt class="col-6 text-muted">CIN</dt>
                <dd class="col-6 fw-medium">{{ $c->cin ?? '—' }}</dd>

                <dt class="col-6 text-muted">PAN</dt>
                <dd class="col-6 fw-medium">{{ $c->pan ?? '—' }}</dd>

                <dt class="col-6 text-muted">GSTIN</dt>
                <dd class="col-6 fw-medium">{{ $c->gstin ?? '—' }}</dd>

                <dt class="col-6 text-muted">Created</dt>
                <dd class="col-6 fw-medium">{{ optional($c->created_at)->format('M j, Y') ?? '—' }}</dd>
            </dl>
        @else
            <p class="text-muted mb-0">No data submitted.</p>
        @endif
    </div>
</div>