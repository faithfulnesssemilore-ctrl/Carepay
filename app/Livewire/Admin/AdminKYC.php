<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class AdminKYC extends Component
{
    public $searchQuery = '';

    public $filterStatus = 'pending';

    public $selectedSubmission = null;

    public $reviewNotes = '';

    public $showModal = false;

    protected $kycSubmissions = [
        [
            'id' => 1,
            'userId' => 'USR001',
            'name' => 'Michael Brown',
            'email' => 'michael.brown@example.com',
            'idType' => 'Passport',
            'idNumber' => 'AB123456',
            'status' => 'pending',
            'submittedDate' => 'Feb 24, 2026',
            'tier' => 'Tier 1',
            'documents' => 3,
        ],
        [
            'id' => 2,
            'userId' => 'USR002',
            'name' => 'Emily Johnson',
            'email' => 'emily.johnson@example.com',
            'idType' => "Driver's License",
            'idNumber' => 'DL987654',
            'status' => 'pending',
            'submittedDate' => 'Feb 24, 2026',
            'tier' => 'Tier 2',
            'documents' => 5,
        ],
        [
            'id' => 3,
            'userId' => 'USR003',
            'name' => 'Robert Taylor',
            'email' => 'robert.taylor@example.com',
            'idType' => 'National ID',
            'idNumber' => 'NID456789',
            'status' => 'pending',
            'submittedDate' => 'Feb 23, 2026',
            'tier' => 'Tier 1',
            'documents' => 3,
        ],
        [
            'id' => 4,
            'userId' => 'USR004',
            'name' => 'Lisa Anderson',
            'email' => 'lisa.anderson@example.com',
            'idType' => 'Passport',
            'idNumber' => 'CD789012',
            'status' => 'approved',
            'submittedDate' => 'Feb 22, 2026',
            'approvedDate' => 'Feb 25, 2026',
            'tier' => 'Tier 1',
            'documents' => 3,
        ],
        [
            'id' => 5,
            'userId' => 'USR005',
            'name' => 'James Martinez',
            'email' => 'james.martinez@example.com',
            'idType' => "Driver's License",
            'idNumber' => 'DL345678',
            'status' => 'rejected',
            'submittedDate' => 'Feb 21, 2026',
            'rejectedDate' => 'Feb 24, 2026',
            'tier' => 'Tier 1',
            'documents' => 2,
            'rejectionReason' => 'Documents not clear',
        ],
    ];

    public function getFilteredSubmissionsProperty()
    {
        return collect($this->kycSubmissions)->filter(function ($submission) {
            $matchesSearch =
                stripos($submission['name'], $this->searchQuery) !== false ||
                stripos($submission['userId'], $this->searchQuery) !== false ||
                stripos($submission['email'], $this->searchQuery) !== false;

            $matchesFilter = $this->filterStatus === 'all' || $submission['status'] === $this->filterStatus;

            return $matchesSearch && $matchesFilter;
        })->toArray();
    }

    public function viewSubmission($id)
    {
        $this->selectedSubmission = collect($this->kycSubmissions)->firstWhere('id', $id);
        $this->showModal = true;
    }

    public function approveSubmission()
    {
        if ($this->selectedSubmission) {
            // Update status in database (dummy action for now)
            session()->flash('message', 'KYC submission approved for '.$this->selectedSubmission['name']);
            $this->showModal = false;
            $this->selectedSubmission = null;
        }
    }

    public function rejectSubmission()
    {
        if ($this->selectedSubmission && ! empty($this->reviewNotes)) {
            // Update status in database (dummy action for now)
            session()->flash('message', 'KYC submission rejected for '.$this->selectedSubmission['name']);
            $this->showModal = false;
            $this->selectedSubmission = null;
            $this->reviewNotes = '';
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedSubmission = null;
        $this->reviewNotes = '';
    }

    public function render()
    {
        return view('livewire.admin.admin-kyc', [
            'filteredSubmissions' => $this->getFilteredSubmissionsProperty(),
        ]);
    }
}
