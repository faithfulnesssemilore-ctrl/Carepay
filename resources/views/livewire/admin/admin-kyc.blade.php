<div class="d-flex flex-column gap-4">
    {{-- Header --}}
    <div>
        <h1 class="display-5 fw-bold mb-2">KYC Management</h1>
        <p class="text-muted-custom">Review and manage KYC submissions</p>
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
                        placeholder="Search by name, ID, or email..."
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
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Submissions Table --}}
    <div class="card card-luxury overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead class="table-secondary-custom">
                    <tr>
                        <th class="px-4 py-3">User ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">ID Type</th>
                        <th class="px-4 py-3">Tier</th>
                        <th class="px-4 py-3">Submitted</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filteredSubmissions as $submission)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="text-monospace small text-primary-custom">{{ $submission['userId'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <div class="small fw-semibold">{{ $submission['name'] }}</div>
                                    <div class="small text-muted-custom">{{ $submission['email'] }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 small">{{ $submission['idType'] }}</td>
                            <td class="px-4 py-3 small">
                                <span class="badge bg-primary-custom">{{ $submission['tier'] }}</span>
                            </td>
                            <td class="px-4 py-3 small">{{ $submission['submittedDate'] }}</td>
                            <td class="px-4 py-3">
                                @if($submission['status'] === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($submission['status'] === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-light"
                                    wire:click="viewSubmission({{ $submission['id'] }})"
                                >
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted-custom">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <x-lucide-inbox class="w-8 h-8 opacity-50" />
                                    <span>No KYC submissions found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Review Modal --}}
    @if($showModal && $selectedSubmission)
        <div class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark-custom border-secondary-custom">
                    <div class="modal-header border-secondary-custom">
                        <h5 class="modal-title">Review KYC Submission</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal()"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Submission Details --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary-custom">Personal Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Full Name</div>
                                    <div class="fw-semibold">{{ $selectedSubmission['name'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Email</div>
                                    <div class="fw-semibold">{{ $selectedSubmission['email'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">ID Type</div>
                                    <div class="fw-semibold">{{ $selectedSubmission['idType'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">ID Number</div>
                                    <div class="fw-semibold">{{ $selectedSubmission['idNumber'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Tier</div>
                                    <div class="fw-semibold">{{ $selectedSubmission['tier'] }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted-custom">Documents</div>
                                    <div class="fw-semibold">{{ $selectedSubmission['documents'] }} uploaded</div>
                                </div>
                            </div>
                        </div>

                        {{-- Documents --}}
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 text-primary-custom">Documents</h6>
                            <div class="d-flex flex-column gap-2">
                                @for($i = 1; $i <= $selectedSubmission['documents']; $i++)
                                    <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: rgba(255, 255, 255, 0.05);">
                                        <div class="d-flex align-items-center gap-2">
                                            <x-lucide-file-text class="w-5 h-5 text-primary-custom" />
                                            <span class="small">Document_{{ $i }}.pdf</span>
                                        </div>
                                        <a href="#" class="btn btn-sm btn-link text-primary-custom p-0">View</a>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        {{-- Review Notes --}}
                        @if($selectedSubmission['status'] === 'pending')
                            <div class="mb-3">
                                <label class="form-label">Review Notes</label>
                                <textarea 
                                    class="form-control bg-secondary-custom border-secondary-custom text-light" 
                                    rows="3"
                                    wire:model="reviewNotes"
                                    placeholder="Add notes about your decision..."
                                ></textarea>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-secondary-custom">
                        <button type="button" class="btn btn-outline-light" wire:click="closeModal()">Close</button>
                        @if($selectedSubmission['status'] === 'pending')
                            <button type="button" class="btn btn-danger" wire:click="rejectSubmission()">Reject</button>
                            <button type="button" class="btn btn-success" wire:click="approveSubmission()">Approve</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
