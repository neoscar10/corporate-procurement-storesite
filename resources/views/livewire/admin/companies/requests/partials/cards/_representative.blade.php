<div class="card card-bg-fill h-100">
    <div class="card-body">
        <h6 class="mb-3">Authorized Representative</h6>

        @php $rep = $company->representative; @endphp

        @if($rep)
            <dl class="row mb-0">
                <dt class="col-5 text-muted">Name</dt>
                <dd class="col-7 fw-medium">{{ data_get($rep, 'full_name', '—') }}</dd>

                <dt class="col-5 text-muted">Email</dt>
                <dd class="col-7 fw-medium">{{ data_get($rep, 'email', '—') }}</dd>

                <dt class="col-5 text-muted">Mobile</dt>
                <dd class="col-7 fw-medium">{{ data_get($rep, 'mobile', '—') }}</dd>

                <dt class="col-5 text-muted">Designation</dt>
                <dd class="col-7 fw-medium">{{ data_get($rep, 'designation', '—') }}</dd>

                @php
                    $idType = data_get($rep, 'govt_id_type');
                    $idNo = data_get($rep, 'govt_id_number');
                @endphp

                @if(filled($idType))
                    <dt class="col-5 text-muted">Govt ID Type</dt>
                    <dd class="col-7 fw-medium">{{ $idType }}</dd>
                @endif

                @if(filled($idNo))
                    <dt class="col-5 text-muted">Govt ID No.</dt>
                    <dd class="col-7 fw-medium">{{ $idNo }}</dd>
                @endif
            </dl>
        @else
            <p class="text-muted mb-0">No representative on record.</p>
        @endif
    </div>
</div>