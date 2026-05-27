<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class AdminUsers extends Component
{
    public $searchQuery = '';

    public $filterStatus = 'all';

    public $selectedUser = null;

    public $showModal = false;

    protected $users = [
        [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1 (555) 123-4567',
            'kycStatus' => 'verified',
            'tier' => 'Tier 1',
            'balance' => 12450.00,
            'status' => 'active',
            'joined' => 'Jan 15, 2026',
        ],
        [
            'id' => 2,
            'name' => 'Sarah Wilson',
            'email' => 'sarah.wilson@example.com',
            'phone' => '+1 (555) 234-5678',
            'kycStatus' => 'verified',
            'tier' => 'Tier 2',
            'balance' => 8200.50,
            'status' => 'active',
            'joined' => 'Jan 20, 2026',
        ],
        [
            'id' => 3,
            'name' => 'Michael Brown',
            'email' => 'michael.brown@example.com',
            'phone' => '+1 (555) 345-6789',
            'kycStatus' => 'pending',
            'tier' => 'Tier 1',
            'balance' => 3500.00,
            'status' => 'active',
            'joined' => 'Feb 5, 2026',
        ],
        [
            'id' => 4,
            'name' => 'Jennifer Lee',
            'email' => 'jennifer.lee@example.com',
            'phone' => '+1 (555) 456-7890',
            'kycStatus' => 'verified',
            'tier' => 'Tier 1',
            'balance' => 5750.25,
            'status' => 'active',
            'joined' => 'Feb 10, 2026',
        ],
        [
            'id' => 5,
            'name' => 'David Chen',
            'email' => 'david.chen@example.com',
            'phone' => '+1 (555) 567-8901',
            'kycStatus' => 'rejected',
            'tier' => 'Tier 1',
            'balance' => 1200.00,
            'status' => 'suspended',
            'joined' => 'Feb 15, 2026',
        ],
    ];

    public function getFilteredUsersProperty()
    {
        return collect($this->users)->filter(function ($user) {
            $matchesSearch =
                stripos($user['name'], $this->searchQuery) !== false ||
                stripos($user['email'], $this->searchQuery) !== false;

            $matchesFilter = $this->filterStatus === 'all' || $user['status'] === $this->filterStatus;

            return $matchesSearch && $matchesFilter;
        })->toArray();
    }

    public function getTotalUsersProperty()
    {
        return count($this->users);
    }

    public function getActiveUsersProperty()
    {
        return collect($this->users)->where('status', 'active')->count();
    }

    public function getSuspendedUsersProperty()
    {
        return collect($this->users)->where('status', 'suspended')->count();
    }

    public function viewUser($id)
    {
        $this->selectedUser = collect($this->users)->firstWhere('id', $id);
        $this->showModal = true;
    }

    public function suspendUser()
    {
        if ($this->selectedUser) {
            session()->flash('message', 'User '.$this->selectedUser['name'].' has been suspended');
            $this->showModal = false;
            $this->selectedUser = null;
        }
    }

    public function unsuspendUser()
    {
        if ($this->selectedUser) {
            session()->flash('message', 'User '.$this->selectedUser['name'].' has been unsuspended');
            $this->showModal = false;
            $this->selectedUser = null;
        }
    }

    public function verifyUser()
    {
        if ($this->selectedUser) {
            session()->flash('message', 'User '.$this->selectedUser['name'].' has been verified');
            $this->showModal = false;
            $this->selectedUser = null;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedUser = null;
    }

    public function render()
    {
        return view('livewire.admin.admin-users', [
            'filteredUsers' => $this->getFilteredUsersProperty(),
            'totalUsers' => $this->getTotalUsersProperty(),
            'activeUsers' => $this->getActiveUsersProperty(),
            'suspendedUsers' => $this->getSuspendedUsersProperty(),
        ]);
    }
}
