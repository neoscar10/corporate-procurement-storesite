<div>
    <div class="" wire:key="items-table-{{ $requestId ?? 'req' }}">
        <table class="table table-bordered table-nowrap align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Kind</th>
                    <th scope="col">Name</th>
                    
                    {{-- <th scope="col">Qty</th>
                    <th scope="col">Unit</th> --}}
                   
                    <th scope="col">Budget</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end" style="width: 60px;"></th>
                </tr>
            </thead>
    
            <tbody>
                @forelse($items as $it)
                    @php
                        // Normalize enum|string status safely
                        $raw = $it->status ?? 'draft';
                        $statusraw = $raw instanceof \BackedEnum ? strtolower($raw->value)
                            : ($raw instanceof \UnitEnum ? strtolower($raw->name) : strtolower((string) $raw));

                        $isDraft = (bool) ($it->is_draft ?? $statusraw === 'draft');
                        $hasSpec = $it->kind === 'product' ? (bool) $it->productSpec : (bool) $it->serviceSpec;
                        $hasFiles = method_exists($it, 'attachments') ? ($it->attachments?->count() ?? 0) > 0 : false;
                        $progress = $isDraft ? ($hasSpec ? ($hasFiles ? '3/3' : '2/3') : '1/3') : '—';

                        $statusClass = match ($statusraw) {
                            'draft' => 'badge bg-warning-subtle text-warning',
                            'pending', 'pending_approval' => 'badge bg-secondary-subtle text-secondary',
                            'approved' => 'badge bg-info-subtle text-info',
                            'published' => 'badge bg-success-subtle text-success',
                            'rejected' => 'badge bg-danger-subtle text-danger',
                            'cancelled', 'canceled' => 'badge bg-dark-subtle text-dark',
                            default => 'badge bg-secondary-subtle text-secondary',
                        };
                        $sym = '₹';
                    @endphp

                    <tr>
                        <th scope="row">{{ $it->id }}</th>
                        <td class="text-capitalize">{{ $it->kind }}</td>
                        <td class="text-truncate" style="max-width: 260px;" title="{{ $it->name }}">
                            <a class="text-decoration-underline"
                                href="{{ route('company.procure.requests.items.show', [$requestId, $it->id]) }}">
                                {{ $it->name }}
                            </a>
                        </td>
                        
                        <td>{{ $it->budget_amount ? $sym . number_format($it->budget_amount, 2) : '—' }}</td>
                        <td>
                            @if($isDraft)
                                <span class="{{ $statusClass }}">Draft</span>
                                <span class="badge bg-light text-muted ms-1">{{ $progress }}</span>
                            @else
                                <span class="{{ $statusClass }}">{{ strtoupper($statusraw) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <a href="#" role="button" id="dd-item-{{ $it->id }}" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="ri-more-2-fill"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dd-item-{{ $it->id }}">
                                    @if($canMutate)
                                        <li>
                                            <a class="dropdown-item" href="#"
                                                wire:click.prevent="$dispatch('resume-item', { id: {{ $it->id }} })">
                                                {{ $isDraft ? 'Continue' : 'Edit' }} {{ $isDraft ? '(' . $progress . ')' : '' }}
                                            </a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#"
                                                wire:click.prevent="askDelete({{ $it->id }})">
                                                Delete item
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a class="dropdown-item"
                                            href="{{ route('company.procure.requests.items.show', [$requestId, $it->id]) }}">
                                                View
                                            </a>
                                        </li>

                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">No items yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Confirm Delete Modal --}}
    <x-ui.confirm id="confirmItemDelete" wire:model="deleteId" size="md" >
        <x-slot:title>Delete Item</x-slot:title>
        <div class="text-muted">
            This will permanently remove the item, its specifications, and all attached files. This action cannot be undone.
        </div>
        <x-slot:confirm>
            <button class="btn btn-danger text-light waves-effect waves-light" wire:click="deleteItem"
                wire:loading.attr="disabled" data-bs-dismiss="modal">
                <span wire:loading.remove>Delete</span>
                <span wire:loading><x-ui.spinner size="sm" text="Deleting..." /></span>
            </button>
        </x-slot:confirm>
    </x-ui.confirm>
    
    {{-- Force-open confirm via browser event --}}
    <script>
        window.addEventListener('open-item-delete-modal', () => {
            const el = document.getElementById('confirmItemDelete');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    </script>
</div>