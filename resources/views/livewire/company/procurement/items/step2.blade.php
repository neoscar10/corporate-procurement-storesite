@if($kind === 'product')
    <div class="row g-3" wire:key="spec-product">
        <div class="col-md-4"><label class="form-label">Brand</label><input class="form-control" wire:model.defer="brand">
        </div>
        <div class="col-md-4"><label class="form-label">Model</label><input class="form-control" wire:model.defer="model">
        </div>
        <div class="col-md-4"><label class="form-label">Quality Level</label><input class="form-control"
                wire:model.defer="quality_level"></div>
        <div class="col-md-6"><label class="form-label">Packaging Requirement</label><input class="form-control"
                wire:model.defer="packaging_requirement"></div>
        <div class="col-md-6 d-flex align-items-center pt-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" wire:model.defer="inspection_required" id="inspect">
                <label class="form-check-label" for="inspect">Inspection required</label>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Technical Specs</label>
            @foreach($technical_specs as $i => $row)
                <div class="row g-2 align-items-center mb-1">
                    <div class="col-md-5"><input class="form-control" placeholder="Key"
                            wire:model.defer="technical_specs.{{ $i }}.key"></div>
                    <div class="col-md-5"><input class="form-control" placeholder="Value"
                            wire:model.defer="technical_specs.{{ $i }}.value"></div>
                    <div class="col-md-2">
                        <button class="btn btn-light btn-sm w-100" wire:click.prevent="removeTechRow({{ $i }})">Remove</button>
                    </div>
                </div>
            @endforeach
            <button class="btn btn-soft-primary btn-sm mt-1" wire:click.prevent="addTechRow">+ Add Row</button>
        </div>
    </div>
@else
    <div class="row g-3" wire:key="spec-service">
        <div class="col-12">
            <label class="form-label">Scope of Work</label>
            <textarea class="form-control" rows="3" wire:model.defer="scope_of_work"></textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Deliverables</label>
            @foreach($deliverables as $i => $d)
                <div class="row g-2 align-items-center mb-1">
                    <div class="col-md-3"><input class="form-control" placeholder="Milestone"
                            wire:model.defer="deliverables.{{ $i }}.milestone"></div>
                    <div class="col-md-5"><input class="form-control" placeholder="Criteria"
                            wire:model.defer="deliverables.{{ $i }}.criteria"></div>
                    <div class="col-md-3"><input type="date" class="form-control"
                            wire:model.defer="deliverables.{{ $i }}.due_date"></div>
                    <div class="col-md-1">
                        <button class="btn btn-light btn-sm w-100" wire:click.prevent="removeDeliverable({{ $i }})">×</button>
                    </div>
                </div>
            @endforeach
            <button class="btn btn-soft-primary btn-sm mt-1" wire:click.prevent="addDeliverable">+ Add Row</button>
        </div>
        <div class="col-12">
            <label class="form-label">Key Personnels</label>
            @foreach($key_personnels as $i => $p)
                <div class="row g-2 align-items-center mb-1">
                    <div class="col-md-4"><input class="form-control" placeholder="Role"
                            wire:model.defer="key_personnels.{{ $i }}.role"></div>
                    <div class="col-md-3"><input type="number" min="1" class="form-control" placeholder="Count"
                            wire:model.defer="key_personnels.{{ $i }}.count"></div>
                    <div class="col-md-4"><input class="form-control" placeholder="Qualification"
                            wire:model.defer="key_personnels.{{ $i }}.qualification"></div>
                    <div class="col-md-1">
                        <button class="btn btn-light btn-sm w-100" wire:click.prevent="removePersonnel({{ $i }})">×</button>
                    </div>
                </div>
            @endforeach
            <button class="btn btn-soft-primary btn-sm mt-1" wire:click.prevent="addPersonnel">+ Add Row</button>
        </div>
    </div>
@endif
<div class="mt-3 d-flex justify-content-between">
    <button class="btn btn-light" wire:click="$set('step', 1)">Back</button>
    <button class="btn btn-primary waves-effect waves-light" wire:click="saveSpecs">Save & Continue</button>
</div>