<div class="card card-bg-fill mt-3">
    <div class="card-body">
        <h6 class="mb-3">Company Addresses</h6>

        @php
$addresses = $company->addresses ?? collect();
$registered = $addresses instanceof \Illuminate\Support\Collection ? $addresses->firstWhere('type', 'registered') : null;
$corporate = $addresses instanceof \Illuminate\Support\Collection ? $addresses->firstWhere('type', 'corporate') : null;
$billing = $addresses instanceof \Illuminate\Support\Collection ? $addresses->firstWhere('type', 'billing') : null;

$fmt = function ($a) {
    if (!$a)
        return '—';
    $parts = [
        data_get($a, 'line1') ?: data_get($a, 'address_line1'),
        data_get($a, 'line2') ?: data_get($a, 'address_line2'),
        data_get($a, 'city') ?: data_get($a, 'locality'),
        data_get($a, 'state') ?: data_get($a, 'region'),
        data_get($a, 'postal_code') ?: data_get($a, 'zip') ?: data_get($a, 'postcode'),
        data_get($a, 'country'),
    ];
    $parts = array_values(array_filter($parts, fn($v) => filled($v)));
    return $parts ? implode(', ', $parts) : '—';
};
        @endphp

        @if(($company->addresses ?? null) && count($company->addresses) > 0)
            <div class="row">
                <div class="mb-3 col-md-4">
                    <div class="text-muted mb-1">Registered</div>
                    <div class="fw-medium">{{ $fmt($registered) }}</div>
                </div>

                <div class="mb-3 col-md-4">
                    <div class="text-muted mb-1">Corporate</div>
                    <div class="fw-medium">{{ $fmt($corporate) }}</div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted mb-1">Billing</div>
                    <div class="fw-medium">{{ $fmt($billing) }}</div>
                </div>

            </div>

        @else
            <p class="text-muted mb-0">No addresses submitted.</p>
        @endif
    </div>
</div>