<x-ui.modal id="vendorUpsert" :show="$show" size="lg" wire:ignore.self>
    <x-slot:title>
        {{ $editId ? 'Edit Vendor' : 'New Vendor' }}
    </x-slot:title>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Vendor Name</label>
            <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g., Jane Doe / ACME Ltd">
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror>
        </div>

        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" wire:model.defer="email" placeholder="vendor@example.com"
                @if($editId) disabled @endif>
            @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror>
            @if($editId)
                <div class="form-text">Email cannot be changed after creation.</div>
            @endif
        </div>

        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" wire:model.defer="phone" placeholder="+1 555…">
            @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror>
        </div>

        <div class="col-md-6">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control" wire:model.defer="company_name" placeholder="Optional">
            @error('company_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror>
        </div>

        <div class="col-md-6 d-flex align-items-end">
            <div class="d-flex align-items-center gap-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="v_prods"
                        wire:model.live="provides_products">
                    <label class="form-check-label" for="v_prods">Provides Products</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="v_svcs"
                        wire:model.live="provides_services">
                    <label class="form-check-label" for="v_svcs">Provides Services</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="v_active"
                        wire:model.defer="is_active">
                    <label class="form-check-label" for="v_active">Active</label>
                </div>
            </div>
            @error('provides_products')<div class="text-danger small ms-3">{{ $message }}</div>@enderror>
        </div>

        {{-- Category pickers (slick two-column, searchable) --}}
        <div class="col-12">
            <hr class="my-2">
        </div>

        <div class="col-md-6">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold">
                    Product Categories
                    @if(!$provides_products)
                        <span class="badge bg-light text-muted ms-2">disabled</span>
                    @endif
                </div>
                <input type="text" class="form-control w-auto" style="max-width:220px" placeholder="Search…"
                    wire:model.debounce.300ms="productCatSearch" @disabled(!$provides_products)>
            </div>
            <div class="border rounded p-2" style="max-height: 260px; overflow:auto;">
                @forelse($productOptions as $opt)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" value="{{ $opt->id }}"
                            wire:model.defer="product_category_ids" id="pcat_{{ $opt->id }}" @disabled(!$provides_products)>
                        <label class="form-check-label" for="pcat_{{ $opt->id }}">{{ $opt->name }}</label>
                    </div>
                @empty
                    <div class="text-muted small">No product categories.</div>
                @endforelse
            </div>
        </div>

        <div class="col-md-6">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold">
                    Service Categories
                    @if(!$provides_services)
                        <span class="badge bg-light text-muted ms-2">disabled</span>
                    @endif
                </div>
                <input type="text" class="form-control w-auto" style="max-width:220px" placeholder="Search…"
                    wire:model.debounce.300ms="serviceCatSearch" @disabled(!$provides_services)>
            </div>
            <div class="border rounded p-2" style="max-height: 260px; overflow:auto;">
                @forelse($serviceOptions as $opt)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" value="{{ $opt->id }}"
                            wire:model.defer="service_category_ids" id="scat_{{ $opt->id }}" @disabled(!$provides_services)>
                        <label class="form-check-label" for="scat_{{ $opt->id }}">{{ $opt->name }}</label>
                    </div>
                @empty
                    <div class="text-muted small">No service categories.</div>
                @endforelse
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal"
            wire:click="$set('show', false)">Cancel</button>

        <button class="btn btn-primary text-light waves-effect waves-light" wire:click="submit"
            wire:loading.attr="disabled">
            <span wire:loading.remove>Save</span>
            <span wire:loading><x-ui.spinner size="sm" text="Saving..." /></span>
        </button>
    </x-slot:footer>
</x-ui.modal>