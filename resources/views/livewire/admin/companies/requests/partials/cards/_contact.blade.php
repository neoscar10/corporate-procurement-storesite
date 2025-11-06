<div class="card card-bg-fill h-100">
    <div class="card-body">
        <h6 class="mb-3">Company Contact</h6>

        @php $contact = $company->contact; @endphp

        @if($contact)
            <dl class="row mb-0">
                <dt class="col-4 text-muted">Name</dt>
                <dd class="col-8 fw-medium">{{ $company->legal_name }}</dd>

                <dt class="col-4 text-muted">Email</dt>
                <dd class="col-8 fw-medium">{{ data_get($contact, 'official_email') ?? data_get($contact, 'work_email') ?? '—' }}
                </dd>

                <dt class="col-4 text-muted">Phone</dt>
                <dd class="col-8 fw-medium">{{ data_get($contact, 'primary_phone') ?? data_get($contact, 'mobile') ?? '—' }}</dd>

                <dt class="col-4 text-muted">Website</dt>
                <dd class="col-8 fw-medium">
                    @php $web = data_get($contact, 'website') ?? data_get($contact, 'url'); @endphp
                    @if(filled($web))
                        <a href="{{ \Illuminate\Support\Str::startsWith($web, ['http://', 'https://']) ? $web : 'https://' . $web }}"
                            target="_blank">
                            {{ $web }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </dl>
        @else
            <p class="text-muted mb-0">No contact submitted.</p>
        @endif
    </div>
</div>