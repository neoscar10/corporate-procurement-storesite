<div class="container-fluid" wire:key="company-procure-index">
    <x-alerts.flash />

    <x-ui.page-header title="Procurement Requests" subtitle="All requests in your company">
        <x-slot:actions>
            @if ($this->canCreate)
                <button class="btn btn-primary waves-effect waves-light" wire:click="openCreate"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="mdi mdi-plus"></i> New Request</span>
                    <span wire:loading><x-ui.spinner size="sm" text="Opening..." /></span>
                </button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <livewire:company.procurement.components.filters :search="$search" :status="$status" :type="$type" :from="$from"
        :to="$to" :per-page="$perPage" :can-create="$this->canCreate"
        wire:key="filters-{{ md5($search . $status . $type . ($from ?? '') . ($to ?? '') . $perPage) }}" />

    <livewire:company.procurement.components.table :search="$search" :status="$status" :type="$type" :from="$from"
        :to="$to" :per-page="$perPage"
        wire:key="table-{{ md5($search . $status . $type . ($from ?? '') . ($to ?? '') . $perPage) }}" />

    {{-- Creation Wizard (2-step, both modals) --}}
    <livewire:company.procurement.create-wizard wire:key="create-wizard" />

    {{-- Delete confirm --}}
    <x-ui.confirm id="confirmDeleteModal" wire:model="deleteId">
        <x-slot:title>Delete Request</x-slot:title>
        <div>Are you sure you want to delete this request? This action cannot be undone.</div>
        <x-slot:confirm>
            <button class="btn btn-danger text-light waves-effect waves-light" wire:click="deleteDraft"
                wire:loading.attr="disabled" data-bs-dismiss="modal">
                <span wire:loading.remove>Delete</span>
                <span wire:loading><x-ui.spinner size="sm" text="Deleting..." /></span>
            </button>
        </x-slot:confirm>
    </x-ui.confirm>
</div>