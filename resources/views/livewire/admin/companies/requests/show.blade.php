<div>
    {{-- Page header --}}
    <x-ui.page-header :title="$company->legal_name ?? $company->brand_name ?? 'Company'" :subtitle="'CIN: ' . ($company->cin ?? '—') . ' • PAN: ' . ($company->pan ?? '—') . ' • GSTIN: ' . ($company->gstin ?? '—')">
    <x-slot:actions>
        @php
$badge = [
    'pending' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger',
    'cancelled' => 'secondary',
][$company->status] ?? 'light';
          @endphp
    
        <div class="d-flex align-items-center gap-2 flex-wrap">
            {{-- status --}}
            <span class="badge rounded-pill bg-{{ $badge }} fs-12 py-1 px-2">
                {{ ucfirst($company->status) }}
            </span>
    
            @if($company->status === 'pending')
                <button class="btn btn-sm btn-success text-light waves-effect waves-light" wire:click="openApprove">
                    <i class="mdi mdi-check-circle-outline me-1"></i> Approve
                </button>
                <button class="btn btn-sm btn-outline-danger" wire:click="openReject">
                    <i class="mdi mdi-close-circle-outline me-1"></i> Reject
                </button>
                <button class="btn btn-sm btn-outline-secondary" wire:click="openCancel">
                    <i class="mdi mdi-cancel me-1"></i> Cancel
                </button>
            @else
                <span class="text-muted fs-12">Request is {{ $company->status }}.</span>
            @endif
    
            {{-- push Back to the far right --}}
            <a href="{{ route('admin.company.requests.index') }}" class="btn btn-sm btn-light ms-auto">
                Back
            </a>
        </div>
    </x-slot:actions>  
    </x-ui.page-header>

    <div class="my-2">
        <span class="badge bg-primary border text-light" style="font-size: 1.3">
            Resubmissions: {{ (int) data_get($company ?? $req ?? null, 'resubmission_count', 0) }}
        </span>
    </div>

    <x-alerts.flash />

    <div class="row g-3">
        <div class="col-xl-4">
            @include('livewire.admin.companies.requests.partials.cards._preference', ['company' => $company])
        </div>
        <div class="col-xl-4">
            @include('livewire.admin.companies.requests.partials.cards._kyc', ['company' => $company])
        </div>
        <div class="col-xl-4">
            @include('livewire.admin.companies.requests.partials.cards._billing', ['company' => $company])
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-xl-4">
            @include('livewire.admin.companies.requests.partials.cards._basic', ['company' => $company])
        </div>
        <div class="col-xl-4">
            @include('livewire.admin.companies.requests.partials.cards._representative', ['company' => $company])
        </div>
        <div class="col-xl-4">
            @include('livewire.admin.companies.requests.partials.cards._contact', ['company' => $company])
        </div>
    </div>

    @include('livewire.admin.companies.requests.partials.cards._addresses', ['company' => $company])


   

    {{-- ======= MODALS  ======= --}}
    @include('livewire.admin.companies.requests.partials.modals._approve')
    @include('livewire.admin.companies.requests.partials.modals._reject')
    @include('livewire.admin.companies.requests.partials.modals._cancel')

    {{-- Shared modal helper (open/close + keep open across) --}}
    @include('livewire.shared._modal-scripts')

</div>