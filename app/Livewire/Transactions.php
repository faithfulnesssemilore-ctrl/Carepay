<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchQuery = '';

    public $filterStatus = 'all';

    public $selectedTransaction = null;

    public $totalTransactions = 0;

    public $totalIn = 0;

    public $totalOut = 0;

    public function mount()
    {
        $this->calculateStats();
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function calculateStats()
    {
        $userId = Auth::id();

        $transactions = Transaction::where('user_id', $userId)->get();

        $this->totalTransactions = $transactions->count();

        // amount is already cast to naira, don't divide again
        $this->totalIn = round($transactions
            ->where('type', 'credit')
            ->sum('amount'), 2);

        $this->totalOut = round($transactions
            ->where('type', 'debit')
            ->sum('amount'), 2);
    }

    public function selectTransaction($id)
    {
        $this->selectedTransaction = Transaction::find($id);
    }

    public function clearSelection()
    {
        $this->selectedTransaction = null;
    }

    public function render()
    {
        $query = Transaction::where('user_id', Auth::id());

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->searchQuery}%")
                    ->orWhere('reference', 'like', "%{$this->searchQuery}%");
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $transactions = $query
            ->latest()
            ->paginate(10);

        return view('livewire.transactions', [
            'transactions' => $transactions,
        ]);
    }
}
