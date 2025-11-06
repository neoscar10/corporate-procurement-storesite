<div>
    <x-ui.page-header title="Company Requests" subtitle="Review and act on company onboarding submissions">
        <x-slot:actions>
            <a href="{{ route('admin.company.requests.index') }}" class="btn btn-light">Refresh</a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-alerts.flash />

    @include('livewire.admin.companies.requests.partials._filters')

    <div class="card card-bg-fill">
        <div class="card-body">
            @include('livewire.admin.companies.requests.partials._table', ['companies' => $companies])
            <div class="mt-3">{{ $companies->links() }}</div>
        </div>
    </div>
</div>