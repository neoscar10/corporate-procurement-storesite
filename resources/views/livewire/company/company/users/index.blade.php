{{-- resources/views/livewire/company/users/index.blade.php --}}
<div>
    <x-alerts.flash />

    <x-ui.page-header :title="'Company users'" :subtitle="$company->name">
        <x-slot:actions>
            @if($canInvite)
                <button type="button" class="btn btn-primary btn-sm text-light waves-effect waves-light"
                    wire:click="$dispatch('invite.open', { companyId: {{ (int) $company->id }} })">
                    <i class="mdi mdi-account-plus-outline me-1"></i>
                    Invite users
                </button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-nowrap align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $index => $member)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>
                                    {{ $member->user->full_name ?? $member->user->name ?? '—' }}
                                </td>
                                <td>{{ $member->user->email ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $member->role_label ?? 'Member' }}
                                    </span>
                                    @if(!empty($member->department))
                                        <span class="text-muted ms-1">• {{ $member->department }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->is_active)
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ optional($member->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No users have been added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Reuse your existing invite modal exactly as-is --}}
    <livewire:auth.invite.invite-user-modal :key="'invite-user-modal-' . $company->id" />
</div>