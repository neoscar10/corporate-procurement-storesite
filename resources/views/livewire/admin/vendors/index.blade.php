@php $tz = auth()->user()->timezone ?? config('app.timezone', 'UTC'); @endphp

<div class="container-fluid" wire:key="admin-vendors-index">
    <x-ui.page-header title="Vendors" subtitle="Super Admin">
        <x-slot:actions>
            <button class="btn btn-primary btn-sm text-light waves-effect waves-light" wire:click="openCreate">
                <i class="mdi mdi-plus"></i> New Vendor
            </button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label mb-0 small text-muted">Search</label>
                    <input type="text" class="form-control" placeholder="Name, email, phone, company…"
                        wire:model.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label mb-0 small text-muted">Active</label>
                    <select class="form-select" wire:model="active">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label mb-0 small text-muted">Provides</label>
                    <select class="form-select" wire:model="provides">
                        <option value="">Products & Services</option>
                        <option value="products">Products only</option>
                        <option value="services">Services only</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label mb-0 small text-muted">Per page</label>
                    <select class="form-select" wire:model="perPage">
                        <option>10</option>
                        <option selected>15</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-nowrap align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Vendor</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Provides</th>
                        <th>Categories</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        @php
                            $provides = [];
                            if ($r->provides_products)
                                $provides[] = 'Products';
                            if ($r->provides_services)
                                $provides[] = 'Services';
                            $providesStr = implode(' & ', $provides) ?: '—';
                        @endphp
                        <tr wire:key="vendor-{{ $r->id }}">
                            <td class="fw-semibold">{{ $r->name ?? '—' }}</td>
                            <td class="text-muted">{{ $r->email ?? '—' }}</td>
                            <td class="text-muted">{{ $r->company_name ?? '—' }}</td>
                            <td>
                                @if($r->provides_products)
                                    <span class="badge bg-primary-subtle text-primary me-1">Products</span>
                                @endif
                                @if($r->provides_services)
                                    <span class="badge bg-info-subtle text-info">Services</span>
                                @endif
                                @if(!$r->provides_products && !$r->provides_services)
                                    <span class="badge bg-light text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                @if($r->categories->count())
                                    @foreach($r->categories->take(3) as $c)
                                        <span class="badge bg-light text-dark me-1">{{ $c->name }}</span>
                                    @endforeach
                                    @if($r->categories->count() > 3)
                                        <span class="badge bg-light text-muted">+{{ $r->categories->count() - 3 }}</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
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
                                        <li><a class="dropdown-item" href="#"
                                                wire:click.prevent="openEdit({{ $r->id }})">Edit</a></li>
                                        <li><a class="dropdown-item" href="#" wire:click.prevent="toggle({{ $r->id }})">
                                                {{ $r->is_active ? 'Deactivate' : 'Activate' }}
                                            </a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="#"
                                                wire:click.prevent="askDelete({{ $r->id }})">Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No vendors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-2">{{ $rows->links() }}</div>
        </div>
    </div>

    {{-- Upsert modal --}}
    <livewire:admin.vendors.upsert wire:key="vendors-upsert" />

    {{-- Delete confirm --}}
    <x-ui.confirm id="vendorDeleteConfirm" wire:key="vendor-delete-confirm" wire:ignore.self>
        <x-slot:title>Delete Vendor</x-slot:title>
        <div>Are you sure you want to delete this vendor?</div>
        <x-slot:confirm>
            <button class="btn btn-danger text-light waves-effect waves-light" wire:click="delete"
                data-bs-dismiss="modal">
                Delete
            </button>
        </x-slot:confirm>
    </x-ui.confirm>

    {{-- JS helpers --}}
    <script>
        window.addEventListener('vendor:open-upsert-js', () => {
            const el = document.getElementById('vendorUpsert');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
        window.addEventListener('vendor:close-upsert-js', () => {
            const el = document.getElementById('vendorUpsert');
            if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
        });
        window.addEventListener('vendor:open-delete-js', () => {
            const el = document.getElementById('vendorDeleteConfirm');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    </script>
</div>