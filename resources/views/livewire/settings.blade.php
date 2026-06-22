<div class="bg-dark-custom min-vh-100 py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">
                <div class="d-flex flex-column flex-md-row align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <h1 class="display-6 fw-bold mb-2">Settings</h1>
                        <p class="text-muted-custom mb-0">Manage your profile, security, and notification preferences.</p>
                    </div>
                    <button wire:click="logout" class="btn btn-outline-light py-2 px-4 rounded-xl">Logout</button>
                </div>

                @if($successMessage)
                    <div class="alert alert-success border-0 rounded-xl shadow-sm mb-4">
                        {{ $successMessage }}
                    </div>
                @endif

                @if($errorMessage)
                    <div class="alert alert-danger border-0 rounded-xl shadow-sm mb-4">
                        {{ $errorMessage }}
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="card card-luxury border h-100 p-3">
                            <div class="card-body">
                                <h5 class="fw-semibold mb-3">Tabs</h5>
                                <div class="list-group">
                                    <button type="button" wire:click="$set('activeTab','security')" class="list-group-item list-group-item-action rounded-xl mb-2 {{ $activeTab === 'security' ? 'active' : 'bg-secondary-custom text-white' }}">
                                        Security
                                    </button>
                                    <button type="button" wire:click="$set('activeTab','notifications')" class="list-group-item list-group-item-action rounded-xl mb-2 {{ $activeTab === 'notifications' ? 'active' : 'bg-secondary-custom text-white' }}">
                                        Notifications
                                    </button>
                                    <button type="button" wire:click="$set('activeTab','account')" class="list-group-item list-group-item-action rounded-xl {{ $activeTab === 'account' ? 'active' : 'bg-secondary-custom text-white' }}">
                                        Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="card card-luxury border h-100 p-4">
                            <div class="card-body">
                                @if($activeTab === 'security')
                                    <h3 class="h5 fw-semibold mb-3">Security Settings</h3>
                                    <p class="text-muted-custom mb-4">Keep your account secure with strong passwords and alerts.</p>

                                    <form wire:submit.prevent="changePassword">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Current Password</label>
                                                <input type="password" wire:model.defer="currentPassword" class="form-control bg-secondary-custom rounded-xl py-3 @error('currentPassword') is-invalid @enderror" placeholder="Enter current password" required>
                                                @error('currentPassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">New Password</label>
                                                <input type="password" wire:model.defer="newPassword" class="form-control bg-secondary-custom rounded-xl py-3 @error('newPassword') is-invalid @enderror" placeholder="New password" required>
                                                @error('newPassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Confirm Password</label>
                                                <input type="password" wire:model.defer="confirmPassword" class="form-control bg-secondary-custom rounded-xl py-3 @error('confirmPassword') is-invalid @enderror" placeholder="Confirm new password" required>
                                                @error('confirmPassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        <div class="mt-4 d-flex justify-content-end gap-2">
                                            <button type="submit" class="btn btn-gradient rounded-xl py-2 px-4" @if($isProcessing) disabled @endif>
                                                {{ $isProcessing ? 'Saving...' : 'Change Password' }}
                                            </button>
                                        </div>
                                    </form>
                                @elseif($activeTab === 'notifications')
                                    <h3 class="h5 fw-semibold mb-3">Notification Preferences</h3>
                                    <p class="text-muted-custom mb-4">Control how you receive updates from CarePay.</p>

                                    <form wire:submit.prevent="updateSettings">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="notificationsEnabled" wire:model.defer="notificationsEnabled">
                                            <label class="form-check-label" for="notificationsEnabled">Enable all notifications</label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="emailNotifications" wire:model.defer="emailNotifications">
                                            <label class="form-check-label" for="emailNotifications">Email notifications</label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="transactionAlerts" wire:model.defer="transactionAlerts">
                                            <label class="form-check-label" for="transactionAlerts">Transaction alert emails</label>
                                        </div>

                                        <div class="mt-4 d-flex justify-content-end gap-2">
                                            <button type="submit" class="btn btn-gradient rounded-xl py-2 px-4" @if($isProcessing) disabled @endif>
                                                {{ $isProcessing ? 'Saving...' : 'Save Preferences' }}
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <h3 class="h5 fw-semibold mb-3">Account Settings</h3>
                                    <p class="text-muted-custom mb-4">Review account information and secure your profile.</p>

                                    <div class="row g-3 mb-3">
                                        <div class="col-12 col-md-6">
                                            <div class="bg-secondary-custom rounded-xl p-3">
                                                <p class="small text-muted-custom mb-1">Name</p>
                                                <p class="fw-semibold mb-0">{{ Auth::user()?->first_name ?? 'Your' }} {{ Auth::user()?->last_name ?? 'Name' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="bg-secondary-custom rounded-xl p-3">
                                                <p class="small text-muted-custom mb-1">Email</p>
                                                <p class="fw-semibold mb-0">{{ Auth::user()?->email }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-secondary-custom rounded-xl p-3">
                                        <p class="small text-muted-custom mb-1">Phone</p>
                                        <p class="fw-semibold mb-0">{{ Auth::user()?->phone ?? 'Not set' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
