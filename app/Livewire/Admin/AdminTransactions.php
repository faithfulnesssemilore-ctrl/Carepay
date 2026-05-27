<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class AdminTransactions extends Component
{
    public $searchQuery = '';

    public $filterStatus = 'all';

    public $selectedTransaction = null;

    public $showModal = false;

    protected $transactions = [
        [
            'id' => 'TXN20260225001',
            'type' => 'transfer',
            'from' => 'John Doe',
            'to' => 'Sarah Wilson',
            'amount' => 250.00,
            'fee' => 0.00,
            'status' => 'completed',
            'date' => 'Feb 25, 2026 - 2:30 PM',
            'category' => 'P2P Transfer',
        ],
        [
            'id' => 'TXN20260225002',
            'type' => 'bill',
            'from' => 'Michael Brown',
            'to' => 'PowerCo',
            'amount' => 85.50,
            'fee' => 0.00,
            'status' => 'completed',
            'date' => 'Feb 25, 2026 - 9:15 AM',
            'category' => 'Bill Payment',
        ],
        [
            'id' => 'TXN20260224003',
            'type' => 'transfer',
            'from' => 'Jennifer Lee',
            'to' => 'David Chen',
            'amount' => 1200.00,
            'fee' => 1.50,
            'status' => 'completed',
            'date' => 'Feb 24, 2026 - 3:45 PM',
            'category' => 'Bank Transfer',
        ],
        [
            'id' => 'TXN20260224004',
            'type' => 'transfer',
            'from' => 'Sarah Wilson',
            'to' => 'John Doe',
            'amount' => 150.00,
            'fee' => 0.00,
            'status' => 'pending',
            'date' => 'Feb 24, 2026 - 8:20 PM',
            'category' => 'P2P Transfer',
        ],
        [
            'id' => 'TXN20260223005',
            'type' => 'bill',
            'from' => 'John Doe',
            'to' => 'Netflix',
            'amount' => 15.99,
            'fee' => 0.00,
            'status' => 'completed',
            'date' => 'Feb 23, 2026 - 12:00 PM',
            'category' => 'Bill Payment',
        ],
        [
            'id' => 'TXN20260222006',
            'type' => 'transfer',
            'from' => 'Michael Brown',
            'to' => 'Jennifer Lee',
            'amount' => 500.00,
            'fee' => 0.00,
            'status' => 'failed',
            'date' => 'Feb 22, 2026 - 5:30 PM',
            'category' => 'P2P Transfer',
        ],
    ];

    public function getFilteredTransactionsProperty()
    {
        return collect($this->transactions)->filter(function ($txn) {
            $matchesSearch =
                stripos($txn['id'], $this->searchQuery) !== false ||
                stripos($txn['from'], $this->searchQuery) !== false ||
                stripos($txn['to'], $this->searchQuery) !== false;

            $matchesFilter = $this->filterStatus === 'all' || $txn['status'] === $this->filterStatus;

            return $matchesSearch && $matchesFilter;
        })->toArray();
    }

    public function getTotalVolumeProperty()
    {
        return collect($this->transactions)->sum('amount');
    }

    public function getTotalFeesProperty()
    {
        return collect($this->transactions)->sum('fee');
    }

    public function viewDetails($id)
    {
        $this->selectedTransaction = collect($this->transactions)->firstWhere('id', $id);
        $this->showModal = true;
    }

    public function downloadReport()
    {
        // Dummy export function
        session()->flash('message', 'Report downloaded successfully');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTransaction = null;
    }

    public function render()
    {
        return view('livewire.admin.admin-transactions', [
            'filteredTransactions' => $this->getFilteredTransactionsProperty(),
            'totalVolume' => $this->getTotalVolumeProperty(),
            'totalFees' => $this->getTotalFeesProperty(),
        ]);
    }
}
