<div class="row g-3">
    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-body py-3">
                <h6 class="fw-semibold mb-2">Request Details</h6>

                {{-- 2-column grid (labels fixed width, values left-aligned & wrapping) --}}
                <div class="small"
                    style="display:grid;grid-template-columns:160px 1fr;row-gap:.35rem;column-gap:.5rem;">
                    <div class="text-muted">Type</div>
                    <div class="text-uppercase text-break">{{ $req->type }}</div>

                    <div class="text-muted">Priority</div>
                    <div class="text-capitalize text-break">{{ $req->priority }}</div>

                    <div class="text-muted">Status</div>
                    <div class="text-break">{{ $req->status }}</div>

                    <div class="text-muted">Stage</div>
                    <div class="text-break">{{ $req->stage }}</div>

                    <div class="text-muted">Items</div>
                    <div>{{ $req->items_count }}</div>

                    
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-body py-3">
                <h6 class="fw-semibold mb-2">Budget</h6>

                <div class="small"
                    style="display:grid;grid-template-columns:160px 1fr;row-gap:.25rem;column-gap:.5rem;">
                    <div class="text-muted">Currency</div>
                    <div class="text-break">{{ $req->currency === 'INR' ? 'INR (₹)' : $req->currency }}</div>

                    <div class="text-muted">Range</div>
                    <div class="text-break">
                        @php $sym = $req->currency === 'INR' ? '₹' : ($req->currency . ' '); @endphp
                        {{ $sym }}{{ number_format($req->budget_min ?? 0, 2) }} —
                        {{ $req->budget_max !== null ? $sym . number_format($req->budget_max, 2) : '—' }}
                    </div>

                    <div class="text-muted">Payment Terms</div>
                    <div class="text-break">{{ $req->payment_terms ?? '—' }}</div>

                    @php $tz = auth()->user()->timezone ?? config('app.timezone', 'UTC'); @endphp
                    
                    <div class="text-muted">Desired Response</div>
                    <div>{{ $req->desired_response_at ? $req->desired_response_at->timezone($tz)->format('D, j M Y · g:i a') : '—' }}</div>
                    
                    <div class="text-muted">Expected Delivery</div>
                    <div>{{ $req->expected_delivery_at ? $req->expected_delivery_at->timezone($tz)->format('D, j M Y · g:i a') : '—' }}
                    </div>


                    <div class="text-muted">Preferred Vendor Location</div>
                    <div class="text-break">{{ $req->preferred_vendor_region ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>