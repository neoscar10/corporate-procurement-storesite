@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    // Helpers: safe string/lines (never echo arrays directly)
    $asText = function ($v, $fallback = '—') {
        if ($v === null || $v === '')
            return $fallback;
        if (is_bool($v))
            return $v ? 'Yes' : 'No';
        if (is_array($v)) {
            $flat = [];
            foreach ($v as $val) {
                if (is_scalar($val))
                    $flat[] = (string) $val;
                elseif (is_array($val) && isset($val['value']))
                    $flat[] = (string) $val['value'];
            }
            return $flat ? implode(', ', $flat) : $fallback;
        }
        return (string) $v;
    };

    $asLines = function ($v, $fallback = '—') {
        if ($v === null || $v === '')
            return $fallback;
        if (is_array($v)) {
            $flat = [];
            foreach ($v as $val) {
                if (is_scalar($val))
                    $flat[] = (string) $val;
                elseif (is_array($val) && isset($val['value']))
                    $flat[] = (string) $val['value'];
            }
            return $flat ? implode("\n", $flat) : $fallback;
        }
        return (string) $v;
    };

    // Normalize company name (brand first, fallback legal)
    $companyName = $req->company->brand_name
        ?? $req->company->legal_name
        ?? '—';

    // Item status badge
    $statusRaw = $item->status instanceof \BackedEnum ? strtolower($item->status->value) : strtolower((string) $item->status);
    $statusCls = match ($statusRaw) {
        'draft' => 'badge bg-warning-subtle text-warning',
        'pending', 'pending_approval' => 'badge bg-secondary-subtle text-secondary',
        'approved' => 'badge bg-info-subtle text-info',
        'published', 'ready' => 'badge bg-success-subtle text-success',
        'rejected' => 'badge bg-danger-subtle text-danger',
        'cancelled', 'canceled' => 'badge bg-dark-subtle text-dark',
        default => 'badge bg-secondary-subtle text-secondary',
    };

    // Summary helpers
    $kindLower = strtolower($item->kind ?? '');
    $badgeClass = match ($kindLower) {
        'service' => 'badge bg-info-subtle text-info',
        'product' => 'badge bg-primary-subtle text-primary',
        default => 'badge bg-secondary-subtle text-secondary'
    };
    $qtyStr = ($item->quantity !== null && $item->quantity !== '') ? (int) $item->quantity : '—';
    $unitStr = $item->unit ?: '—';
    $budgetStr = ($item->budget_amount !== null) ? number_format((float) $item->budget_amount, 2) : '—';
    $requiredOn = optional($item->date_required)?->format('F j, Y') ?: '—';

    // Normalize product URLs to a flat string array
    $productUrls = [];
    if ($item->kind === 'product' && $item->productSpec) {
        $raw = $item->productSpec->product_urls ?? [];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [$raw];
        }
        if (!is_array($raw))
            $raw = [$raw];
        foreach ($raw as $u) {
            if (is_string($u) && trim($u) !== '') {
                $productUrls[] = trim($u);
            } elseif (is_array($u)) {
                $cand = $u['url'] ?? $u['href'] ?? $u['value'] ?? null;
                if (is_string($cand) && trim($cand) !== '')
                    $productUrls[] = trim($cand);
            }
        }
        $productUrls = array_values(array_unique($productUrls));
    }

    // Attachment normalizer
    $attachments = collect($item->attachments ?? []);
@endphp

<div class="container-fluid" wire:key="sa-item-show-{{ $req->id }}-{{ $item->id }}">
    <x-ui.page-header :title="$asText($item->name)" :subtitle="'PR-#' . $req->id . ' • ' . $companyName">
        <x-slot:actions>
            <a href="{{ route('admin.procure.requests.show', $req->id) }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back to Request
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row">
        {{-- MAIN --}}
        <div class="col-lg-8">
            {{-- Summary (copy of company design) --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Kind</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-shape-outline text-primary"></i>
                                <span class="{{ $badgeClass }}">{{ ucfirst($kindLower ?: '—') }}</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-muted small">Quantity</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-counter text-primary"></i>
                                <span class="fw-semibold">{{ $qtyStr }}</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-muted small">Unit</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-ruler-square text-primary"></i>
                                <span class="fw-semibold">{{ $unitStr }}</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-muted small">Budget</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-cash-multiple text-info"></i>
                                <span class="fw-semibold text-truncate" title="{{ $budgetStr }}">{{ $budgetStr }}</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-muted small">Required</div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="mdi mdi-calendar-month text-info"></i>
                                <span class="fw-semibold text-truncate" title="{{ $requiredOn }}">{{ $requiredOn }}</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="text-muted small">Status</div>
                            <div><span class="{{ $statusCls }}">{{ strtoupper($statusRaw) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Full Specification (copy of company design) --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Full Specification</h6>
                </div>
                <div class="card-body">
                    @if($item->kind === 'product')
                        @php $ps = $item->productSpec; @endphp

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small">Brand</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-tag text-primary"></i>
                                    <span class="fw-semibold">{{ $asText($ps->brand ?? null) }}</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted small">Model</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-cube-outline text-primary"></i>
                                    <span class="fw-semibold">{{ $asText($ps->model ?? null) }}</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted small">Quality</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-star-outline text-warning"></i>
                                    <span class="fw-semibold">{{ $asText($ps->quality_level ?? null) }}</span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted small">Packaging Requirement</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-package-variant text-info"></i>
                                    <span class="fw-semibold text-truncate" title="{{ $asText($ps->packaging_requirement ?? null) }}">
                                        {{ $asText($ps->packaging_requirement ?? null) }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="text-muted small">Inspection Required</div>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-shield-check {{ ($ps->inspection_required ?? false) ? 'text-success' : 'text-muted' }}"></i>
                                    @if($ps && ($ps->inspection_required ?? false))
                                        <span class="badge bg-success-subtle text-success">Yes</span>
                                    @else
                                        <span class="badge bg-light text-muted">No</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Technical Specs table --}}
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
                                                        <td>{{ $asText($row['key'] ?? null) }}</td>
                                                        <td>{{ $asText($row['value'] ?? null) }}</td>
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
                                {!! nl2br(e($asLines($ss->scope_of_work ?? null))) !!}
                            </div>
                        </div>

                        {{-- Deliverables --}}
                        <div class="mb-3">
                            <div class="text-muted small mb-2">Deliverables</div>
                            @php $dels = is_array($ss->deliverables ?? null) ? $ss->deliverables : []; @endphp
                            @if(empty($dels))
                                <div class="text-muted">—</div>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($dels as $d)
                                        @php
                                            $milestone = $asText($d['milestone'] ?? null);
                                            $criteria = $asText($d['criteria'] ?? null);
                                            $due = $asText($d['due_date'] ?? null, '');
                                        @endphp
                                        <li class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-semibold">{{ $milestone }}</div>
                                                    <div class="text-muted small">{{ $criteria }}</div>
                                                </div>
                                                @if($due !== '')
                                                    <span class="badge bg-light text-muted ms-2">{{ $due }}</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Key Personnels --}}
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
                                                    <td>{{ $asText($p['role'] ?? null) }}</td>
                                                    <td>{{ (int) ($p['count'] ?? 1) }}</td>
                                                    <td>{{ $asText($p['qualification'] ?? null) }}</td>
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

            {{-- Media & URLs (copy of company design) --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Media & URLs</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Attached Files --}}
                        <div class="col-12">
                            <div class="text-muted small mb-2">Attached Files</div>

                            @if(($attachments->count() ?? 0) > 0)
                                <div class="row g-2">
                                    @foreach($attachments as $att)
                                        @php
                                            // Support model/object or array payloads
                                            $path = is_array($att) ? ($att['path'] ?? '') : ($att->path ?? '');
                                            $name = is_array($att)
                                                ? ($att['original_name'] ?? ($path ? basename($path) : 'file'))
                                                : ($att->original_name ?? ($path ? basename($path) : 'file'));

                                            $mime = is_array($att) ? ($att['mime'] ?? ($att['mime_type'] ?? null)) : ($att->mime ?? ($att->mime_type ?? null));
                                            $ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
                                            $isImage = ($mime && is_string($mime) && str_starts_with($mime, 'image/'))
                                                || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);

                                            $url = '#';
                                            if (is_array($att)) {
                                                $rawUrl = $att['url'] ?? null;
                                                if (is_string($rawUrl) && str_starts_with($rawUrl, 'http')) {
                                                    $url = $rawUrl;
                                                } elseif ($path) {
                                                    $url = Storage::url($path);
                                                }
                                            } else {
                                                $rawUrl = $att->url ?? null;
                                                if (is_string($rawUrl) && str_starts_with($rawUrl, 'http')) {
                                                    $url = $rawUrl;
                                                } elseif ($path) {
                                                    $url = Storage::url($path);
                                                }
                                            }
                                        @endphp

                                        @if($isImage)
                                            <div class="col-6 col-md-4 col-lg-3">
                                                <a href="{{ $url }}" target="_blank" class="text-decoration-none d-block">
                                                    <div class="border rounded-3 p-1 shadow-sm h-100">
                                                        <div class="ratio ratio-4x3">
                                                            <img src="{{ $url }}" alt="{{ $asText($name) }}" class="img-fluid rounded-2" style="object-fit: cover;">
                                                        </div>
                                                        <div class="small text-truncate mt-2" title="{{ $asText($name) }}">
                                                            <i class="mdi mdi-image-multiple-outline me-1"></i>{{ $asText($name) }}
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @else
                                            <div class="col-12 col-md-6 col-lg-4">
                                                <a href="{{ $url }}" class="btn btn-sm btn-light w-100 text-start waves-effect waves-light" target="_blank">
                                                    <i class="mdi mdi-paperclip me-1"></i>
                                                    {{ Str::limit($asText($name), 36) }}
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
                        <div class="col-12">
                            <div class="text-muted small mb-2">Product URLs</div>
                            @if($item->kind === 'product' && !empty($productUrls))
                                <ul class="list-unstyled mb-0">
                                    @foreach($productUrls as $u)
                                        <li class="mb-1">
                                            <a href="{{ $u }}" target="_blank" class="text-decoration-underline">
                                                {{ Str::limit($u, 70) }}
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

            {{-- Description (copy of company design) --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Description</h6>
                </div>
                <div class="card-body">
                    <div class="text-muted">{!! nl2br(e($asLines($item->short_description ?? null))) !!}</div>
                </div>
            </div>
        </div>

        {{-- RIGHT RAIL --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><h6 class="mb-0">Request (dummy card for quotes)</h6></div>
                <div class="card-body">
                    <div class="text-muted small">Title</div>
                    <div class="fw-semibold">{{ $asText($req->title) }}</div>

                    

                    <div class="mt-2 text-muted small">Request Status</div>
                    @php
                        $rStatusRaw = $req->status instanceof \BackedEnum ? strtolower($req->status->value) : strtolower((string) $req->status);
                        $rStatusCls = match ($rStatusRaw) {
                            'published' => 'badge bg-success-subtle text-success',
                            'approved' => 'badge bg-primary-subtle text-primary',
                            'pending', 'pending_approval' => 'badge bg-info-subtle text-info',
                            'draft' => 'badge bg-secondary-subtle text-secondary',
                            'rejected' => 'badge bg-danger-subtle text-danger',
                            'cancelled', 'canceled' => 'badge bg-dark-subtle text-dark',
                            default => 'badge bg-secondary-subtle text-secondary',
                        };
                    @endphp
                    <div><span class="{{ $rStatusCls }}">{{ strtoupper($rStatusRaw) }}</span></div>

                    <div class="mt-3">
                        <a href="{{ route('admin.procure.requests.show', $req->id) }}"
                           class="btn btn-soft-primary w-100 waves-effect">
                           View Request
                        </a>
                    </div>
                </div>
            </div>

            {{-- (Optional) Quick meta --}}
            
        </div>
    </div>
</div>
