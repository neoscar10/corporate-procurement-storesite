@php $tz = auth()->user()->timezone ?? config('app.timezone', 'UTC'); @endphp

<div class="container-fluid" wire:key="admin-vendor-categories">
    <x-alerts.flash />

    <x-ui.page-header title="Vendor Categories" :subtitle="ucfirst($kind) . ' • Super Admin'">
        <x-slot:actions>
            <button class="btn btn-primary btn-sm text-light waves-effect waves-light"
                wire:click="openCreate('{{ $kind }}')">
                <i class="mdi mdi-plus"></i> New {{ ucfirst($kind) }} Category
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Kind Switch (tabs) --}}
    <div class="card mb-3">
        <div class="card-body">
            <ul class="nav nav-pills gap-1">
                <li class="nav-item">
                    <a href="#" class="nav-link {{ $kind === 'product' ? 'active' : '' }}"
                        wire:click.prevent="$set('kind','product')">
                        Products <span class="badge bg-light text-dark ms-1">{{ $counts['product'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link {{ $kind === 'service' ? 'active' : '' }}"
                        wire:click.prevent="$set('kind','service')">
                        Services <span class="badge bg-light text-dark ms-1">{{ $counts['service'] ?? 0 }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label mb-0 small text-muted">Search</label>
                    <input type="text" class="form-control" placeholder="Name or slug…"
                        wire:model.live.debounce.400ms="search">
                </div>
    
                <div class="col-md-2">
                    <label class="form-label mb-0 small text-muted">Active</label>
                    <select class="form-select" wire:model.live="active">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
    
                <div class="col-md-2 ms-auto">
                    <label class="form-label mb-0 small text-muted">Per page</label>
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>


    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-nowrap align-middle">
                <thead class="table-light">
                    <tr>
                        {{-- <th style="width:70px;">Order</th> --}}
                        <th>Name</th>
                        <th>Slug</th>
                        <th style="width:110px;">Status</th>
                        <th style="width:110px;">Updated</th>
                        <th style="width:60px;" class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr wire:key="vc-{{ $r->id }}">
                            {{-- <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button class="btn btn-light material-shadow-none" wire:click="move({{ $r->id }}, 'up')"
                                        title="Move up">
                                        <i class="mdi mdi-chevron-up"></i>
                                    </button>
                                    <button class="btn btn-light material-shadow-none"
                                        wire:click="move({{ $r->id }}, 'down')" title="Move down">
                                        <i class="mdi mdi-chevron-down"></i>
                                    </button>
                                </div>
                                <span class="badge bg-light text-muted ms-1">{{ (int) $r->display_order }}</span>
                            </td> --}}
                            <td class="fw-semibold">{{ $r->name }}</td>
                            <td class="text-muted">{{ $r->slug }}</td>
                            <td>
                                @if($r->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ optional($r->updated_at)->timezone($tz)->format('j M Y, g:i a') }}
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="openEdit({{ $r->id }})">
                                                Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" wire:click.prevent="toggle({{ $r->id }})">
                                                {{ $r->is_active ? 'Deactivate' : 'Activate' }}
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#"
                                                wire:click.prevent="askDelete({{ $r->id }})">
                                                Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No categories.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-2">{{ $rows->links() }}</div>
        </div>
    </div>

    {{-- Mount the Upsert child component (holds its own modal) --}}
    <livewire:admin.vendor-categories.upsert wire:key="vc-upsert-{{ $kind }}" />

    {{-- Delete Confirm (JS-controlled, uses $deleteId in the parent) --}}
    <div class="modal fade" id="vcDelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md" style="--bs-modal-width: 720px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mb-0">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    This will permanently remove the category. This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger text-light waves-effect waves-light" wire:click="delete"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Delete</span>
                        <span wire:loading><x-ui.spinner size="sm" text="Deleting..." /></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const show = id => { const el = document.getElementById(id); if (el) bootstrap.Modal.getOrCreateInstance(el).show(); };
                const hide = id => { const el = document.getElementById(id); if (el) bootstrap.Modal.getOrCreateInstance(el).hide(); };

                // Upsert modal open/close from child events
                window.addEventListener('vc:open-upsert-js', () => show('vcUpsert'));
                window.addEventListener('vc:close-upsert-js', () => hide('vcUpsert'));

                // Delete confirm (parent events)
                window.addEventListener('vc:open-delete', () => show('vcDelete'));
                window.addEventListener('vc:close-delete', () => hide('vcDelete'));

                // Keep Livewire state tidy if user closes Delete via ESC/X/backdrop
                document.addEventListener('hidden.bs.modal', (e) => {
                    const id = e.target?.id || '';
                    if (id !== 'vcDelete') return;

                    const root = e.target.closest('[wire\\:id]');
                    const comp = root ? window.Livewire?.find(root.getAttribute('wire:id')) : null;
                    if (comp) comp.set('deleteId', null);
                });
            })();
        </script>
    @endpush
</div>