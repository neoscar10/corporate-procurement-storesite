<div>
    <x-alerts.flash />

    <div class="row g-3">
        {{-- PAN --}}
        <div class="col-md-4">
            <label class="form-label">PAN Document</label>
            <input type="file" class="form-control" wire:model="pan_document" accept=".pdf,.jpg,.jpeg,.png">

            @if(!empty($existing['pan']['url']))
                <div class="mt-2">
                    <div class="text-truncate">
                        <span class="badge bg-light text-dark me-2">Current</span>
                        <a href="{{ $existing['pan']['url'] }}" target="_blank" class="text-decoration-underline">
                            {{ $existing['pan']['name'] }}
                        </a>
                    </div>
        
                </div>
            @endif
            @error('pan_document')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- CIN --}}
        <div class="col-md-4">
            <label class="form-label">CIN Document</label>
            <input type="file" class="form-control" wire:model="cin_document" accept=".pdf,.jpg,.jpeg,.png">

            @if(!empty($existing['cin']['url']))
                <div class=" mt-2 d-flex ">
                    <div class="text-truncate">
                        <span class="badge bg-light text-dark me-2">Current</span>
                        <a href="{{ $existing['cin']['url'] }}" target="_blank" class="text-decoration-underline">
                            {{ $existing['cin']['name'] }}
                        </a>
                    </div>
                </div>
            @endif
            @error('cin_document')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- GSTIN --}}
        <div class="col-md-4">
            <label class="form-label">GSTIN Document</label>
            <input type="file" class="form-control" wire:model="gstin_document" accept=".pdf,.jpg,.jpeg,.png">

            @if(!empty($existing['gstin']['url']))
                <div class="mt-2">
                    <div class="text-truncate">
                        <span class="badge bg-light text-dark me-2">Current</span>
                        <a href="{{ $existing['gstin']['url'] }}" target="_blank" class="text-decoration-underline">
                            {{ $existing['gstin']['name'] }}
                        </a>
                    </div>
                </div>
            @endif

            @error('gstin_document')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- unified progress while uploading or saving --}}
        <x-ui.loader-progress targets="pan_document,cin_document,gstin_document,save" text="Uploading documents…"
            height="8" />

        <div class="d-flex justify-content-center mt-5">
            <button class="btn btn-primary w-100 text-light waves-effect waves-light" wire:click="save"
                wire:loading.attr="disabled" wire:target="save,pan_document,cin_document,gstin_document">
                {{-- show normal label unless the SAVE action is running --}}
                <span wire:loading.remove wire:target="save">
                    Save & proceed
                </span>
                {{-- only show spinner when SAVE is running, not during uploads --}}
                <span wire:loading wire:target="save">
                    <x-spinner size="sm" text="Uploading..." />
                </span>
            </button>
        </div>

        </div>
    </div>
</div>