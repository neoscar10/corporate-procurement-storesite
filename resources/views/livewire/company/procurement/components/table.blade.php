<div>
    @php $tz = auth()->user()->timezone ?? config('app.timezone', 'UTC'); @endphp
    
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-nowrap align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">PR #</th>
                        <th scope="col">Title</th>
                        <th scope="col">Type</th>
                        <th scope="col">Priority</th>
                        <th scope="col">Items</th>
                        <th scope="col">Status</th>
                        <th scope="col">Stage</th>
                        <th scope="col" class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        @php
                            $statusRaw = $r->status instanceof \BackedEnum ? strtolower($r->status->value) : strtolower((string) $r->status);
                            $statusCls = match ($statusRaw) {
                                'draft' => 'badge bg-secondary-subtle text-secondary',
                                'pending', 'pending_approval' => 'badge bg-info-subtle text-info',
                                'approved' => 'badge bg-primary-subtle text-primary',
                                'published' => 'badge bg-success-subtle text-success',
                                'rejected' => 'badge bg-danger-subtle text-danger',
                                'cancelled', 'canceled' => 'badge bg-dark-subtle text-dark',
                                default => 'badge bg-secondary-subtle text-secondary',
                            };
                        @endphp
                        <tr wire:key="req-row-{{ $r->id }}">
                            <th scope="row">PR-#{{ $r->id }}</th>

                            <td class="text-truncate" style="max-width: 280px;">
                                <a href="{{ route('company.procure.requests.show', $r->id) }}"
                                    class="text-decoration-underline">
                                    {{ $r->title }}
                                </a>
                            </td>

                            <td class="text-uppercase">{{ $r->type }}</td>
                            <td class="text-capitalize">{{ $r->priority }}</td>
                            <td><span class="badge bg-light text-dark">{{ $r->items_count }}</span></td>

                            <td><span class="{{ $statusCls }}">{{ strtoupper($statusRaw) }}</span></td>
                            <td>{{ $r->stage }}</td>

                            <td class="text-end">
                                <div class="dropdown">
                                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="ri-more-2-fill"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('company.procure.requests.show', $r->id) }}">
                                                Open
                                            </a>
                                        </li>

                                        @if(in_array($r->status->value ?? $r->status, ['draft', 'cancelled', 'canceled'], true))
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#"
                                                    wire:click.prevent="confirmDelete({{ $r->id }})">
                                                    Delete
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    
            <div class="mt-2">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
    
    {{-- was: id="confirmDeleteModal-{{ $this->id }}" --}}
    <x-ui.confirm id="confirmDeleteModal-{{ $domId }}" size="md" wire:ignore.self>
        <x-slot:title>Delete Request</x-slot:title>
        <div>Are you sure you want to delete this request? This action cannot be undone.</div>
        <x-slot:confirm>
            <button class="btn btn-danger text-light waves-effect waves-light" wire:click="deleteConfirmed"
                wire:loading.attr="disabled">
                <span wire:loading.remove>Delete</span>
                <span wire:loading>Deleting...</span>
            </button>
        </x-slot:confirm>
    </x-ui.confirm>
    
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                const id = @json($domId); // was $this->id
                const getModal = () => {
                    const el = document.getElementById('confirmDeleteModal-' + id);
                    return el ? bootstrap.Modal.getOrCreateInstance(el) : null;
                };
                Livewire.on('table:confirm-delete:open', () => { const m = getModal(); if (m) m.show(); });
                Livewire.on('table:confirm-delete:close', () => { const m = getModal(); if (m) m.hide(); });
            });
        </script>
    @endpush
</div>