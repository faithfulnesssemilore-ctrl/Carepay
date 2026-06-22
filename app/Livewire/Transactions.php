<?php

namespace App\Livewire;

use App\Jobs\ExportStatementJob;
use App\Models\Transaction;
use App\TransactionTypeEnum;
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

    public $showExportModal = false;

    public $exportStartDate = '';

    public $exportEndDate = '';

    public $exportMessage = '';

    public $isExporting = false;

    public function mount()
    {
        $this->calculateStats();
        // Set default date range to last 30 days
        $this->exportEndDate = today()->format('Y-m-d');
        $this->exportStartDate = today()->subDays(30)->format('Y-m-d');
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
            ->where('type', TransactionTypeEnum::Credit)
            ->sum('amount'), 2);

        $this->totalOut = round($transactions
            ->where('type', TransactionTypeEnum::Debit)
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

    public function openExportModal()
    {
        $this->showExportModal = true;
        $this->exportMessage = '';
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
        $this->exportMessage = '';
    }

    public function requestStatementExport()
    {
        $this->exportMessage = '';
        $this->isExporting = true;

        try {
            // Validate dates
            $startDate = $this->exportStartDate;
            $endDate = $this->exportEndDate;

            if (empty($startDate) || empty($endDate)) {
                throw new \Exception('Please select both start and end dates.');
            }

            if (strtotime($startDate) > strtotime($endDate)) {
                throw new \Exception('Start date must be before end date.');
            }

            // Directly dispatch the job without making HTTP request
            dispatch(new ExportStatementJob(
                Auth::id(),
                $startDate,
                $endDate
            ));

            $this->exportMessage = 'Your statement export has been queued. You will receive an email when it\'s ready to download.';
            $this->showExportModal = false;
            $this->dispatch('toast', type: 'success', message: $this->exportMessage);
        } catch (\Exception $e) {
            $this->exportMessage = 'Error: '.$e->getMessage();
            $this->dispatch('toast', type: 'error', message: $this->exportMessage);
        } finally {
            $this->isExporting = false;
        }
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
