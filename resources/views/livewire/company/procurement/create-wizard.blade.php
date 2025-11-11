<div>
    {{-- Step 1: Details --}}
    <x-ui.modal id="createStep1" :show="$showStep1" size="lg" wire:key="create-step1">
        <x-slot:name>open-create-step1</x-slot:name>
        <x-slot:title>Create Procurement — Step 1 (Details)</x-slot:title>
    
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" wire:model.defer="title">
                @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <select class="form-select" wire:model.defer="type">
                    <option value="rfi">RFI</option>
                    <option value="req">REQ</option>
                    <option value="po">PO</option>
                    <option value="rfp">RFP</option>
                </select>
                @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Priority</label>
                <select class="form-select" wire:model.defer="priority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
                @error('priority')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Desired Response At</label>
                <input type="datetime-local" class="form-control" wire:model.defer="desired_response_at">
                @error('desired_response_at')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Expected Delivery At</label>
                <input type="datetime-local" class="form-control" wire:model.defer="expected_delivery_at">
                @error('expected_delivery_at')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    
        <x-slot:footer>
            <div wire:key="step1-footer-save">
                <button class="btn btn-ghost-secondary material-shadow-none" data-bs-dismiss="modal" wire:click="closeStep1">
                    Cancel
                </button>
        
                <button class="btn btn-primary text-light waves-effect waves-light" wire:click="saveStep1"
                    wire:loading.attr="disabled" wire:target="saveStep1">
                    <span wire:loading.remove wire:target="saveStep1">Save &amp; Continue</span>
                    <span wire:loading wire:target="saveStep1">
                        <x-ui.spinner size="sm" text="Saving..." />
                    </span>
                </button>
            </div>
        </x-slot:footer>

    </x-ui.modal>
    
    {{-- Step 2: Budget & Logistics --}}
    <x-ui.modal id="createStep2" :show="$showStep2" size="lg" wire:key="create-step2">
        <x-slot:title>Create Procurement — Step 2 (Budget & Logistics)</x-slot:title>
    
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Currency</label>
                <select class="form-select" wire:model.defer="currency">
                    <option value="INR">INR (₹)</option>
                </select>
                @error('currency')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Budget Min (₹)</label>
                <input type="number" step="0.01" class="form-control" wire:model.defer="budget_min">
                @error('budget_min')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Budget Max (₹)</label>
                <input type="number" step="0.01" class="form-control" wire:model.defer="budget_max">
                @error('budget_max')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment Terms</label>
                <select class="form-select" wire:model.defer="payment_terms">
                    <option value="">—</option>
                    <option value="advance">Advance</option>
                    <option value="net_30">30 days</option>
                    <option value="net_45">45 days</option>
                    <option value="net_50">50 days</option>
                    <option value="on_delivery">On delivery</option>
                </select>
                @error('payment_terms')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Preferred Vendor Regions</label>
                <input type="text" class="form-control" placeholder="e.g., Maharashtra, Gujarat"
                    wire:model.defer="vendor_regions_input">
            
                @error('preferred_vendor_regions')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Delivery Location</label>
                <textarea class="form-control" rows="2" wire:model.defer="delivery_location"></textarea>
                @error('delivery_location')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            
            <div class="col-6">
                <label class="form-label">Notes</label>
                <textarea class="form-control" rows="2" wire:model.defer="notes"></textarea>
                @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    
        <x-slot:footer>
            <button class="btn btn-light material-shadow-none" wire:click="closeStep2" data-bs-dismiss="modal">Back</button>
            <button class="btn btn-success text-light waves-effect waves-light" wire:click="saveStep2"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Create Request</span>
                <span wire:loading><x-ui.spinner size="sm" text="Creating..." /></span>
            </button>
        </x-slot:footer>
    </x-ui.modal>


 
    <script>
        window.addEventListener('open-create-step1', () => {
            console.log('[CreateWizard] open step1');
            const el = document.getElementById('createStep1');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
        window.addEventListener('close-create-step1', () => {
            console.log('[CreateWizard] close step1');
            const el = document.getElementById('createStep1');
            if (el) bootstrap.Modal.getOrCreateInstance(el).hide();
        });
        window.addEventListener('open-create-step2', () => {
            console.log('[CreateWizard] open step2');
            const el = document.getElementById('createStep2');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    </script>
    
</div>