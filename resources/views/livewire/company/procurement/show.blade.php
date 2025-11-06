<div class="container-fluid" wire:key="company-procure-show-{{ $req->id }}-v{{ $version }}">
    <x-alerts.flash />

    <x-ui.page-header :title="$req->title" :subtitle="'PR-#' . $req->id">
        <x-slot:actions>
            <a href="{{ route('company.procurements') }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
            <button class="btn btn-outline-secondary btn-sm material-shadow-none" wire:click="openEditDetails">
                Edit Details
            </button>
            <button class="btn btn-outline-secondary btn-sm material-shadow-none" wire:click="openEditBudget">
                Edit Budget & Logistics
            </button>

            @php
                $statusRaw = $req->status instanceof \BackedEnum ? $req->status->value : (string) $req->status;
            @endphp
            @if(strtolower($statusRaw) === 'approved')
                <button class="btn btn-success btn-sm text-light waves-effect waves-light"
                    wire:click="$dispatch('request-publish', { id: {{ $req->id }} })">
                    Publish
                </button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Summary --}}
    <livewire:company.procurement.components.request-summary :requestId="$req->id"
        wire:key="summary-{{ $req->id }}-v{{ $version }}" />

    {{-- Items --}}
    <div class="card mt-3" wire:key="items-card-{{ $req->id }}-v{{ $version }}">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Items</h5>
            <div class="btn-group">
                <button class="btn btn-soft-primary waves-effect" wire:click="openItemWizard('product')">
                    <i class="mdi mdi-package-variant"></i> Add Product
                </button>
                <button class="btn btn-soft-info waves-effect" wire:click="openItemWizard('service')">
                    <i class="mdi mdi-briefcase"></i> Add Service
                </button>
            </div>
        </div>
        <div class="card-body">
            <livewire:company.procurement.items.table :requestId="$req->id"
                wire:key="items-{{ $req->id }}-v{{ $version }}" />
        </div>
    </div>

    {{-- Approvals --}}
    <livewire:company.procurement.components.approvals-card :requestId="$req->id"
        wire:key="approvals-{{ $req->id }}-v{{ $version }}" />

    {{-- Modals --}}
    <livewire:company.procurement.modals.edit-details :requestId="$req->id"
        wire:key="edit-details-{{ $req->id }}-v{{ $version }}" />

    <livewire:company.procurement.modals.edit-budget :requestId="$req->id"
        wire:key="edit-budget-{{ $req->id }}-v{{ $version }}" />

    <livewire:company.procurement.items.wizard :requestId="$req->id" wire:key="wizard-{{ $req->id }}-v{{ $version }}" />
</div>