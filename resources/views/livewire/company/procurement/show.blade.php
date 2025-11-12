<div class="container-fluid" wire:key="company-procure-show-{{ $req->id }}-v{{ $version }}">
    <x-alerts.flash />

    <x-ui.page-header :title="$req->title" :subtitle="' • PR-#' . $req->id . ($req->creator ? ' • by ' . $req->creator->name : '')">
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
        </x-slot:actions>
    </x-ui.page-header>

    <div class="row">
        <div class="col-md-8">
           

            {{-- Items (remount on structure changes only) --}}
            <div class="card mt-3" wire:key="items-card-{{ $req->id }}-v{{ $version }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Items</h5>
                    @if ($req->stage == 'building' || $req->status == 'rejected')
                        <div class="btn-group">
                            <button class="btn btn-soft-primary waves-effect" wire:click="openItemWizard('product')">
                                <i class="mdi mdi-package-variant"></i> Add Product
                            </button>
                            <button class="btn btn-soft-info waves-effect" wire:click="openItemWizard('service')">
                                <i class="mdi mdi-briefcase"></i> Add Service
                            </button>
                        </div>
                    @endif
                </div>
                <div class="mx-4 text-muted"><small>Items can be added only while request is in building stage</small>
                </div>
                <div class="card-body">
                    <livewire:company.procurement.items.table :requestId="$req->id"
                        wire:key="items-{{ $req->id }}-v{{ $version }}" />
                </div>
            </div>
        </div>

        <div class="col-md-4">
        @include('livewire.company.procurement.components.partial-approvals-card')

        {{-- Summary (stable) --}}
        <livewire:company.procurement.components.request-summary :requestId="$req->id" wire:key="summary-{{ $req->id }}" />
       
        </div>
    </div>

    {{-- Modals --}}
    <livewire:company.procurement.modals.edit-details :requestId="$req->id" wire:key="edit-details-{{ $req->id }}" />
    <livewire:company.procurement.modals.edit-budget :requestId="$req->id" wire:key="edit-budget-{{ $req->id }}" />
    <livewire:company.procurement.items.wizard :requestId="$req->id" wire:key="wizard-{{ $req->id }}-v{{ $version }}" />
</div>