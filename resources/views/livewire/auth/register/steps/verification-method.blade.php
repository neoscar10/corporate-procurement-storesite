<x-alerts.flash />

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Verify via</label>
        <div class="d-flex gap-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" id="vmEmail" value="email" wire:model="channel">
                <label class="form-check-label" for="vmEmail">Email</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" id="vmSms" value="sms" wire:model="channel">
                <label class="form-check-label" for="vmSms">SMS</label>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
        <button class="btn btn-primary text-light waves-effect waves-light" wire:click="issue" wire:loading.attr="disabled"
            wire:target="issue">
            <span wire:loading.remove wire:target="issue">Send Code</span>
            <span wire:loading wire:target="issue">
                <x-spinner size="sm" text="Sending..." />
            </span>
        </button>
    </div>

</div>