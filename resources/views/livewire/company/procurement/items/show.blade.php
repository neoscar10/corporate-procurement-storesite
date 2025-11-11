@php
$statusStr = $req->status instanceof \BackedEnum ? strtolower($req->status->value) : strtolower((string) $req->status);
$kind = ucfirst($item->kind ?? 'Item');
@endphp


<div class="container-fluid">
    <x-ui.page-header :title="$item->name" :subtitle="$req->title . ' • PR-#' . $req->id . ($req->creator ? ' • by ' . $req->creator->name : '')">
        <x-slot:actions>
            <a href="{{ route('company.procure.requests.show', $req->id) }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back to Request
            </a>
        </x-slot:actions>
    </x-ui.page-header>


    <div class="row">
        

        {{-- Main content (right) --}}
        <div class="col-lg-8">
            {{-- Card: Full Specification --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Full Specification</h6>
                </div>
                <div class="card-body">
                    @if($item->kind === 'product')
                        @php $ps = $item->productSpec; @endphp

                        {{-- Meta tiles --}}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small">Brand</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-tag text-primary"></i>
                                    <span class="fw-semibold">{{ $ps->brand ?? '—' }}</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted small">Model</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-cube-outline text-primary"></i>
                                    <span class="fw-semibold">{{ $ps->model ?? '—' }}</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted small">Quality</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                    <span class="fw-semibold">{{ $ps->quality_level ?? '—' }}</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Packaging Requirement</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-package-variant text-info"></i>
                                    <span class="fw-semibold text-truncate" title="{{ $ps->packaging_requirement ?? '' }}">
                                        {{ $ps->packaging_requirement ?? '—' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="text-muted small">Inspection Required</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i
                                        class="mdi mdi-shield-check {{ ($ps->inspection_required ?? false) ? 'text-success' : 'text-muted' }}"></i>
                                    @if($ps && ($ps->inspection_required ?? false))
                                        <span class="badge bg-success-subtle text-success">Yes</span>
                                    @else
                                        <span class="badge bg-light text-muted">No</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Technical Specs (keep as table) --}}
                            <div class="col-12">
                                <hr class="my-2">
                                <div class="text-muted small mb-2">Technical Specs</div>
                                @php $tech = is_array($ps->technical_specs ?? null) ? $ps->technical_specs : []; @endphp
                                @if(empty($tech))
                                    <div class="text-muted">—</div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:35%;">Key</th>
                                                    <th>Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tech as $row)
                                                    <tr>
                                                        <td>{{ $row['key'] ?? '' }}</td>
                                                        <td>{{ $row['value'] ?? '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        @php $ss = $item->serviceSpec; @endphp

                        {{-- Scope of Work --}}
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Scope of Work</div>
                            <div class="bg-light-subtle rounded p-3 border">
                                {!! nl2br(e($ss->scope_of_work ?? '—')) !!}
                            </div>
                        </div>

                        {{-- Deliverables (improved list) --}}
                        <div class="mb-3">
                            <div class="text-muted small mb-2">Deliverables</div>
                            @php $dels = is_array($ss->deliverables ?? null) ? $ss->deliverables : []; @endphp
                            @if(empty($dels))
                                <div class="text-muted">—</div>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($dels as $d)
                                        @php
            $milestone = $d['milestone'] ?? '—';
            $criteria = $d['criteria'] ?? '—';
            $due = $d['due_date'] ?? null;
                                        @endphp
                                        <li class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-semibold">{{ $milestone }}</div>
                                                    <div class="text-muted small">{{ $criteria }}</div>
                                                </div>
                                                @if($due)
                                                    <span class="badge bg-light text-muted ms-2">{{ $due }}</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Key Personnels (keep as table) --}}
                        <div>
                            <div class="text-muted small mb-2">Key Personnels</div>
                            @php $kp = is_array($ss->key_personnels ?? null) ? $ss->key_personnels : []; @endphp
                            @if(empty($kp))
                                <div class="text-muted">—</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Role</th>
                                                <th style="width:120px;">Count</th>
                                                <th>Qualification</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($kp as $p)
                                                <tr>
                                                    <td>{{ $p['role'] ?? '—' }}</td>
                                                    <td>{{ (int) ($p['count'] ?? 1) }}</td>
                                                    <td>{{ $p['qualification'] ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card: Summary --}}
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
$requiredOn = optional($item->date_required)?->format('F j, Y'); // e.g., November 7, 2025
            @endphp
        
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:18%;">Kind</th>
                                <th style="width:14%;">Quantity</th>
                                <th style="width:14%;">Unit</th>
                                <th style="width:22%;">Budget</th>
                                <th style="width:32%;">Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="{{ $badgeClass }}">{{ ucfirst($kindLower ?: '—') }}</span>
                                </td>
                                <td>{{ (int) ($item->quantity ?? 1) }}</td>
                                <td>{{ $item->unit ?: '—' }}</td>
                                <td>{{ $money($item->budget_amount ?? null) }}</td>
                                <td>{{ $requiredOn ?: '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

            
            {{-- Card: Description --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Description</h6>
                </div>
                <div class="card-body">
                    <div class="text-muted">{!! nl2br(e($item->short_description ?? '—')) !!}</div>
                </div>
            </div>

            {{-- Card: Media files & Product URLs --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Media & URLs</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
            
                        {{-- Attached Files --}}
                        <div class="col-12">
                            <div class="text-muted small mb-2">Attached Files</div>
            
                            @if(method_exists($item, 'attachments') && $item->attachments->count())
                                <div class="row g-2">
                                    @foreach($item->attachments as $att)
                                        @php
        $path = $att->path ?? '';
        $url = \Illuminate\Support\Facades\Storage::url($path);
        $name = $att->original_name ?? basename($path);
        $mime = $att->mime ?? null;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $isImage = ($mime && str_starts_with($mime, 'image/'))
            || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                        @endphp

                                        @if($isImage)
                                            <div class="col-6 col-md-4 col-lg-3">
                                                <a href="{{ $url }}" target="_blank" class="text-decoration-none d-block">
                                                    <div class="border rounded-3 p-1 shadow-sm h-100">
                                                        <div class="ratio ratio-4x3">
                                                            <img src="{{ $url }}" alt="{{ $name }}" class="img-fluid rounded-2"
                                                                style="object-fit: cover;">
                                                        </div>
                                                        <div class="small text-truncate mt-2" title="{{ $name }}">
                                                            <i class="mdi mdi-image-multiple-outline me-1"></i>{{ $name }}
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @else
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <a href="{{ $url }}" class="btn btn-sm btn-light w-100 text-start waves-effect waves-light"
                                                    target="_blank">
                                                    <i class="mdi mdi-paperclip me-1"></i>
                                                    {{ \Illuminate\Support\Str::limit($name, 36) }}
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted">No files attached.</div>
                            @endif
                        </div>
            
                        {{-- Product URLs --}}
                        @php
$urls = ($item->kind === 'product' && $item->productSpec)
    ? (is_array($item->productSpec->product_urls ?? null) ? $item->productSpec->product_urls : [])
    : [];
                        @endphp
                        
                        <div class="col-12">
                            @php $ps = $item->productSpec; @endphp
                            <div class="text-muted small mb-2">Product URLs</div>
                            @if($ps && is_array($ps->product_urls) && count($ps->product_urls))
                                <ul class="list-unstyled mb-0">
                                    @foreach($ps->product_urls as $u)
                                        <li class="mb-1">
                                            <a href="{{ $u }}" target="_blank" class="text-decoration-underline">
                                                {{ \Illuminate\Support\Str::limit($u, 70) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-muted">No URLs captured.</div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>


            {{-- Card: Quotation (Content area, extended later if you prefer) --}}
            {{-- <div class="card">
                <div class="card-header"><h6 class="mb-0">Quotation</h6></div>
                <div class="card-body">
                    <div class="text-muted small">
                        Hook this to the vendor quotations for this item/request. For now, this is a placeholder.
                    </div>
                </div>
            </div> --}}
        </div>

        {{-- Sidebar (left) --}}
        <div class="col-lg-4">
        
            {{-- Dummy Quotation (sidebar quick view) --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Quotation (Preview)</h6>
                    <span class="badge bg-light text-muted">Dummy</span>
                </div>
                <div class="card-body">
                    <div class="text-muted small mb-2">
                        Placeholder summary
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between mb-1">
                            <span>Vendors invited</span>
                            <span class="fw-semibold">—</span>
                        </li>
                        <li class="d-flex justify-content-between mb-1">
                            <span>Lowest quote</span>
                            <span class="fw-semibold">—</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>Status</span>
                            <span class="">dummy</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Reuse the existing Wizard modal so "Resume / Edit" works --}}
    <livewire:company.procurement.items.wizard :requestId="$req->id" />
</div>
