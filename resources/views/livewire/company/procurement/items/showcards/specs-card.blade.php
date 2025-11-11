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

                <div class="col-md-4">
                    <div class="text-muted small">Packaging Requirement</div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="mdi mdi-package-variant text-info"></i>
                        <span class="fw-semibold text-truncate" title="{{ $ps->packaging_requirement ?? '' }}">
                            {{ $ps->packaging_requirement ?? '—' }}
                        </span>
                    </div>
                </div>

                <div class="col-md-4">
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