<div class="card card-bg-fill h-100">
    <div class="card-body">
        <h6 class="mb-3">Financial / Billing</h6>
        @if($company->bankAccounts->isNotEmpty())
            @php $bank = $company->bankAccounts->first(); @endphp
            <dl class="row mb-0">
                <dt class="col-4 text-muted">Bank</dt>
                <dd class="col-8">{{ $bank->bank_name ?? '—' }}</dd>

                <dt class="col-4 text-muted">A/C No</dt>
                <dd class="col-8">{{ $bank->account_number ?? '—' }}</dd>

                <dt class="col-4 text-muted">IFSC</dt>
                <dd class="col-8">{{ $bank->ifsc ?? '—' }}</dd>

                <dt class="col-4 text-muted">Branch</dt>
                <dd class="col-8">{{ $bank->branch ?? '—' }}</dd>
            </dl>
        @else
            <p class="text-muted mb-0">No billing details yet.</p>
        @endif
    </div>
</div>