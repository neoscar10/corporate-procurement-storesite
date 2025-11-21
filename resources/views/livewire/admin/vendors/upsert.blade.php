<div>
    <x-ui.modal id="vendorUpsert" :show="$show" size="lg" wire:ignore.self>
    <x-slot:title>
        {{ $editId ? 'Edit Vendor' : 'New Vendor' }}
    </x-slot:title>

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Vendor Name</label>
            <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g., Jane Doe / ACME Ltd">
            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" wire:model.defer="email" placeholder="vendor@example.com"
                @if($editId) disabled @endif>
            @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @if($editId)
                <div class="form-text">Email cannot be changed after creation.</div>
            @endif
        </div>

        <div class="col-md-4">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" wire:model.defer="phone" placeholder="+1 555…">
            @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label class="form-label">Company Name</label>
            <input type="text" class="form-control" wire:model.defer="company_name" placeholder="Optional">
            @error('company_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
            @error('provides_products')<div class="text-danger small ms-3">{{ $message }}</div>@enderror
        </div>

        <div class="col-12"><hr class="my-2"></div>

        {{-- Product picker trigger + chips --}}
        <div class="col-md-6">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold d-flex align-items-center gap-2">
                    <span>Product Categories</span>
                    <span class="badge bg-light text-dark">{{ count($product_category_ids) }}</span>
                    @unless($provides_products)
                        <span class="badge bg-light text-muted">disabled</span>
                    @endunless
                </div>

                <button type="button"
                        class="btn btn-soft-primary btn-sm waves-effect"
                        wire:click="openProdPicker"
                        @disabled(!$provides_products)>
                    <i class="mdi mdi-format-list-bulleted"></i> Choose
                </button>
            </div>

            <div class="border rounded p-2" style="min-height:52px;">
                @forelse($selectedProduct as $cat)
                    <span class="badge bg-primary-subtle text-primary me-1 mb-1">
                        {{ $cat->name }}
                        <a href="#" class="ms-1 text-primary"
                           wire:click.prevent="toggleCategory('product', {{ $cat->id }})"
                           title="Remove"><i class="mdi mdi-close"></i></a>
                    </span>
                @empty
                    <span class="text-muted small">No product categories selected.</span>
                @endforelse
            </div>
        </div>

        {{-- Service picker trigger + chips --}}
        <div class="col-md-6">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold d-flex align-items-center gap-2">
                    <span>Service Categories</span>
                    <span class="badge bg-light text-dark">{{ count($service_category_ids) }}</span>
                    @unless($provides_services)
                        <span class="badge bg-light text-muted">disabled</span>
                    @endunless
                </div>

            <button type="button"
                    class="btn btn-soft-info btn-sm waves-effect"
                    wire:click="openSvcPicker"
                    @disabled(!$provides_services)>
                <i class="mdi mdi-format-list-bulleted"></i> Choose
            </button>
            </div>

            <div class="border rounded p-2" style="min-height:52px;">
                @forelse($selectedService as $cat)
                    <span class="badge bg-info-subtle text-info me-1 mb-1">
                        {{ $cat->name }}
                        <a href="#" class="ms-1 text-info"
                           wire:click.prevent="toggleCategory('service', {{ $cat->id }})"
                           title="Remove"><i class="mdi mdi-close"></i></a>
                    </span>
                @empty
                    <span class="text-muted small">No service categories selected.</span>
                @endforelse
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal"
            wire:click="$set('show', false)">Cancel</button>

        <button class="btn btn-primary text-light waves-effect waves-light" wire:click="submit" wire:loading.attr="disabled"
            wire:target="submit">
            <span wire:loading.remove wire:target="submit">
                Save
            </span>
        
            <span wire:loading wire:target="submit">
                <x-ui.spinner size="sm" text="Saving..." />
            </span>
        </button>

    </x-slot:footer>
</x-ui.modal>

{{-- Product categories modal --}}
<x-ui.modal id="vendorCatModalProduct" wire:model.live="showProdModal" size="lg" wire:ignore.self>
    <x-slot:title>
        <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-tag-multiple text-primary"></i>
            <span>Choose Product Categories</span>
        </div>
    </x-slot:title>

    <div class="row g-2">
        <div class="col-md-8">
            <input type="text" class="form-control" placeholder="Search products…"
                   wire:model.live="productCatSearch">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-light w-100 material-shadow-none" wire:click="selectAll('product')">
                Select All
            </button>
            <button class="btn btn-ghost-danger w-100 material-shadow-none" wire:click="clearAll('product')">
                Clear
            </button>
        </div>

        <div class="col-12">
            <div class="border rounded p-2" style="max-height: 360px; overflow:auto;">
                @forelse($productOptions as $opt)
                    <div class="form-check mb-1">
                        <input class="form-check-input"
                               type="checkbox"
                               value="{{ $opt->id }}"
                               id="pick_pcat_{{ $opt->id }}"
                               wire:change="toggleCategory('product', {{ $opt->id }})"
                               @checked(in_array($opt->id, $product_category_ids, true))>
                        <label class="form-check-label" for="pick_pcat_{{ $opt->id }}">{{ $opt->name }}</label>
                    </div>
                @empty
                    <div class="text-muted small">No categories.</div>
                @endforelse
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button class="btn btn-ghost-secondary material-shadow-none" wire:click="closeProdPicker">
            Done
        </button>
    </x-slot:footer>
</x-ui.modal>

{{-- Service categories modal --}}
<x-ui.modal id="vendorCatModalService" wire:model.live="showSvcModal" size="lg" wire:ignore.self>
    <x-slot:title>
        <div class="d-flex align-items-center gap-2">
            <i class="mdi mdi-briefcase-variant-outline text-info"></i>
            <span>Choose Service Categories</span>
        </div>
    </x-slot:title>

    <div class="row g-2">
        <div class="col-md-8">
            <input type="text" class="form-control" placeholder="Search services…"
                   wire:model.live="serviceCatSearch">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button class="btn btn-light w-100 material-shadow-none" wire:click="selectAll('service')">
                Select All
            </button>
            <button class="btn btn-ghost-danger w-100 material-shadow-none" wire:click="clearAll('service')">
                Clear
            </button>
        </div>

        <div class="col-12">
            <div class="border rounded p-2" style="max-height: 360px; overflow:auto;">
                @forelse($serviceOptions as $opt)
                    <div class="form-check mb-1">
                        <input class="form-check-input"
                               type="checkbox"
                               value="{{ $opt->id }}"
                               id="pick_scat_{{ $opt->id }}"
                               wire:change="toggleCategory('service', {{ $opt->id }})"
                               @checked(in_array($opt->id, $service_category_ids, true))>
                        <label class="form-check-label" for="pick_scat_{{ $opt->id }}">{{ $opt->name }}</label>
                    </div>
                @empty
                    <div class="text-muted small">No categories.</div>
                @endforelse
            </div>
        </div>
    </div>

    <x-slot:footer>
        <button class="btn btn-ghost-secondary material-shadow-none" wire:click="closeSvcPicker">
            Done
        </button>
    </x-slot:footer>
</x-ui.modal>

{{-- JS fallbacks to force-open/close modals (Bootstrap) --}}
<script>
    window.addEventListener('vendor:open-upsert-js', () => {
        const el = document.getElementById('vendorUpsert');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });

    window.addEventListener('vendor:open-prod-cats-js', () => {
        const el = document.getElementById('vendorCatModalProduct');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });

    window.addEventListener('vendor:open-svc-cats-js', () => {
        const el = document.getElementById('vendorCatModalService');
        if (el) bootstrap.Modal.getOrCreateInstance(el).show();
    });

    window.addEventListener('vendor:close-cat-modals-js', () => {
        ['vendorCatModalProduct','vendorCatModalService'].forEach(id => {
            const el = document.getElementById(id);
            if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
        });
    });
</script>

</div>