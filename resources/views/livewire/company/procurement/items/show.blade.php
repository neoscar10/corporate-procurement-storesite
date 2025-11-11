@php
$statusStr = $req->status instanceof \BackedEnum ? strtolower($req->status->value) : strtolower((string) $req->status);
$kind = ucfirst($item->kind ?? 'Item');
@endphp


<div class="container-fluid">
    <x-ui.page-header :title="$item->name" :subtitle="$req->title . ' • PR-#' . $req->id . ($req->creator ? ' • by ' . $req->creator->name : '')">
        <x-slot:actions>
            <a href="{{ route('company.procure.requests.show', $req->id) }}" class="btn btn-light btn-sm">
                <i class="mdi mdi-arrow-left"></i> Back to Request
            </a>
        </x-slot:actions>
    </x-ui.page-header>


    <div class="row">
        

        {{-- Main content (left) --}}
        <div class="col-lg-8">

            {{-- Card: Summary --}}
            @include('livewire.company.procurement.items.showcards.summary-card')
            {{-- Card: Description --}}
            @include('livewire.company.procurement.items.showcards.description-card')
            {{-- Card: Full Specification --}}
            @include('livewire.company.procurement.items.showcards.specs-card')
            {{-- Card: Media files & Product URLs --}}
            @include('livewire.company.procurement.items.showcards.media-files-card')
        </div>
        {{-- Sidebar (roght) --}}
        <div class="col-lg-4">
            {{-- Dummy Quotation (sidebar quick view) --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Quotation (Preview)</h6>
                    <span class="badge bg-light text-muted">Dummy</span>
                </div>
                <div class="card-body">
                    <div class="text-muted small mb-2">
                        Placeholder summary
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between mb-1">
                            <span>Vendors invited</span>
                            <span class="fw-semibold">—</span>
                        </li>
                        <li class="d-flex justify-content-between mb-1">
                            <span>Lowest quote</span>
                            <span class="fw-semibold">—</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>Status</span>
                            <span class="">dummy</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Reuse the existing Wizard modal so "Resume / Edit" works --}}
    <livewire:company.procurement.items.wizard :requestId="$req->id" />
</div>
