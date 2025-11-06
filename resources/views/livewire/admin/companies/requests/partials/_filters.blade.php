<div class="card card-bg-fill mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <x-forms.input label="Search" name="search" wire:model.live="search"
                    placeholder="Company / CIN / PAN / GSTIN" />
            </div>

            <div class="col-md-2">
                <x-forms.select label="Status" name="status" wire:model.live="status">
                    <option value="all">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </x-forms.select>
            </div>

            <div class="col-md-2">
                <x-forms.input type="date" label="From" name="from" wire:model.live="from" />
            </div>

            <div class="col-md-2">
                <x-forms.input type="date" label="To" name="to" wire:model.live="to" />
            </div>

            <div class="col-md-2">
                <x-forms.select label="Show" name="perPage" wire:model.live="perPage">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </x-forms.select>
            </div>
        </div>
    </div>
</div>