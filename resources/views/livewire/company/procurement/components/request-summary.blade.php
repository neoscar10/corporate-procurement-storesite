<div class="row">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Request Meta</h5>
                <div class="d-flex justify-content-between"><span>Type</span><span
                        class="text-uppercase">{{ $req->type }}</span></div>
                <div class="d-flex justify-content-between"><span>Priority</span><span
                        class="text-capitalize">{{ $req->priority }}</span></div>
                <div class="d-flex justify-content-between"><span>Status</span><span
                        class="">{{ $req->status }}</span></div>
                <div class="d-flex justify-content-between"><span>Stage</span><span>{{ $req->stage }}</span></div>
                <div class="d-flex justify-content-between"><span>Items</span><span>{{ $req->items_count }}</span></div>
                <div class="d-flex justify-content-between">
                    <span>Attachments</span><span>{{ $req->attachments_count }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Budget</h5>
                <div class="d-flex justify-content-between">
                    <span>Currency</span><span>{{ $req->currency === 'INR' ? 'INR (₹)' : $req->currency }}</span></div>
                <div class="d-flex justify-content-between"><span>Range</span>
                    <span>
                        @php $sym = $req->currency === 'INR' ? '₹' : ($req->currency . ' ') @endphp
                        {{ $sym }}{{ number_format($req->budget_min ?? 0, 2) }} —
                        {{ $req->budget_max ? $sym . number_format($req->budget_max, 2) : '—' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between"><span>Payment
                        Terms</span><span>{{ $req->payment_terms ?? '—' }}</span></div>
                <div class="d-flex justify-content-between"><span>Desired
                        Response</span><span>{{ ($req->desired_response_at)->format('Y-m-d H:i') ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between"><span>Expected
                        Delivery</span><span>{{ ($req->expected_delivery_at)->format('Y-m-d H:i') ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between"><span>Prefered Vendor Location</span><span>{{ ($req->preferred_vendor_region) }}</span>
                </div>
                 {{-- {{ dd($req) }} --}}
            </div>
        </div>
    </div>
</div>