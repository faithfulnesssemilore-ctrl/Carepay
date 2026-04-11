<div class="d-flex flex-column gap-4">
    {{-- Header --}}
    <div>
        <h1 class="display-5 fw-bold mb-2">User Management</h1>
        <p class="text-muted-custom">Manage and monitor user accounts</p>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury p-4">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="small text-muted-custom mb-1">Total Users</div>
                        <div class="h4 fw-bold">{{ $totalUsers }}</div>
                    </div>
                    <div class="icon-container icon-container-sm" style="background: rgba(168, 85, 247, 0.2);">
                        <x-lucide-users class="w-5 h-5 text-primary-custom" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury p-4">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="small text-muted-custom mb-1">Active</div>
                        <div class="h4 fw-bold">{{ $activeUsers }}</div>
                    </div>
                    <div class="icon-container icon-container-sm" style="background: rgba(34, 197, 94, 0.2);">
                        <x-lucide-user-check class="w-5 h-5 text-success" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-luxury p-4">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="small text-muted-custom mb-1">Suspended</div>
                        <div class="h4 fw-bold">{{ $suspendedUsers }}</div>
                    </div>
                    <div class="icon-container icon-container-sm" style="background: rgba(239, 68, 68, 0.2);">
                        <x-lucide-user-x class="w-5 h-5 text-danger" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card card-luxury p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-secondary-custom border-secondary-custom">
                        <x-lucide-search class="text-muted-custom w-5 h-5" />
                    </span>
                    <input 
                        type="text" 
                        class="form-control bg-secondary-custom border-secondary-custom text-light" 
                        placeholder="Search by name or email..."
                        wire:model.live="searchQuery"
                    />
                </div>
            </div>
            <div class="col-md-4">
                <select 
                    class="form-select bg-secondary-custom border-secondary-custom text-light"
                    wire:model.live="filterStatus"
                >
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card card-luxury overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead class="table-secondary-custom">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">KYC</th>
                        <th class="px-4 py-3">Tier</th>
                        <th class="px-4 py-3">Balance</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filteredUsers as $user)
                        <tr>
                            <td class="px-4 py-3 fw-semibold">{{ $user['name'] }}</td>
                            <td class="px-4 py-3 small">{{ $user['email'] }}</td>
                            <td class="px-4 py-3 small text-monospace">{{ $user['phone'] }}</td>
                            <td class="px-4 py-3">
                                @if($user['kycStatus'] === 'verified')
                                    <span class="badge bg-success">
                                        <x-lucide-check-circle-2 class="w-3 h-3 d-inline me-1" />
                                        Verified
                                    </span>
                                @elseif($user['kycStatus'] === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        <x-lucide-clock class="w-3 h-3 d-inline me-1" />
                                        Pending
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <x-lucide-x-circle class="w-3 h-3 d-inline me-1" />
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 small">
                                <span class="badge bg-primary-custom">{{ $user['tier'] }}</span>
                            </td>
                            <td class="px-4 py-3 fw-semibold text-success">₦{{ number_format($user['balance'], 2) }}</td>
                            <td class="px-4 py-3">
                                @if($user['status'] === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 small text-muted-custom">{{ $user['joined'] }}</td>
                            <td class="px-4 py-3">
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-light"
                                    wire:click="viewUser({{ $user['id'] }})"
                                >
                                    Manage
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted-custom">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <x-lucide-inbox class="w-8 h-8 opacity-50" />
                                    <span>No users found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Management Modal --}}
    @if($showModal && $selectedUser)
        <div class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark-custom border-secondary-custom">
                    <div class="modal-header border-secondary-custom">
                        <h5 class="modal-title">User Management</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal()"></button>
                    </div>
                    <div class="modal-body">
                        {{-- User Details --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary-custom">Account Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Name</div>
                                    <div class="fw-semibold">{{ $selectedUser['name'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Email</div>
                                    <div class="fw-semibold">{{ $selectedUser['email'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Phone</div>
                                    <div class="fw-semibold">{{ $selectedUser['phone'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Joined</div>
                                    <div class="fw-semibold">{{ $selectedUser['joined'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">KYC Status</div>
                                    <div>
                                        @if($selectedUser['kycStatus'] === 'verified')
                                            <span class="badge bg-success">Verified</span>
                                        @elseif($selectedUser['kycStatus'] === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Account Status</div>
                                    <div>
                                        @if($selectedUser['status'] === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Suspended</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Financial Info --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary-custom">Financial Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Tier</div>
                                    <div class="fw-semibold">{{ $selectedUser['tier'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Balance</div>
                                    <div class="h5 fw-bold text-success">₦{{ number_format($selectedUser['balance'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary-custom">
                        <button type="button" class="btn btn-outline-light" wire:click="closeModal()">Close</button>
                        @if($selectedUser['status'] === 'active')
                            <button type="button" class="btn btn-danger" wire:click="suspendUser()">Suspend User</button>
                        @else
                            <button type="button" class="btn btn-warning" wire:click="unsuspendUser()">Unsuspend User</button>
                        @endif
                        @if($selectedUser['kycStatus'] !== 'verified')
                            <button type="button" class="btn btn-success" wire:click="verifyUser()">Verify KYC</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
