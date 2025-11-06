<div class="card card-bg-fill h-100">
    <div class="card-body">
        <h6 class="mb-3">KYC Documents</h6>

        @if($company->kycDocuments->isNotEmpty())
            <ul class="list-unstyled mb-0">
                @foreach($company->kycDocuments as $doc)
                    @php
                        /** @var \App\Models\Company\CompanyKycDocument $doc */
                        $name = $doc->display_name;
                        $url = $doc->public_url;
                        $type = strtoupper((string) $doc->document_type);
                    @endphp

                    <li class="mb-2 d-flex align-items-center justify-content-between">
                        <span class="flex-grow-1 me-2 text-truncate">
                            <i class="mdi mdi-file-document-outline me-1"></i>
                            {{ $type }} —
                            @if($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="link-primary text-decoration-none">
                                    {{ $name }}
                                </a>
                            @else
                                {{ $name }}
                            @endif
                        </span>

                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-soft-primary">
                                View
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted mb-0">No documents uploaded.</p>
        @endif
    </div>
</div>