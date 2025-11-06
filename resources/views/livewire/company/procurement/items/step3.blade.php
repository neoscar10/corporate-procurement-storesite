<div class="mb-2">
    <label class="form-label">Attachments</label>
    <input type="file" class="form-control" wire:model="files" multiple>
    <div class="text-muted small mt-1">Max 10MB each.</div>
    @error('files.*')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
<div class="mt-3 d-flex justify-content-between">
    <button class="btn btn-light" wire:click="$set('step', 2)">Back</button>
    <button class="btn btn-success text-light waves-effect waves-light" wire:click="saveAttachments"
        wire:loading.attr="disabled">
        <span wire:loading.remove>Finish</span>
        <span wire:loading><x-ui.spinner size="sm" text="Uploading..." /></span>
    </button>
</div>