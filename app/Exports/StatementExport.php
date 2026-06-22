<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StatementExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
    use Exportable;

    protected Carbon $startDate;

    protected Carbon $endDate;

    public function __construct(
        protected int $userId,
        string $startDate = '',
        string $endDate = ''
    ) {
        $this->startDate = Carbon::parse($startDate ?: now()->startOfMonth())->startOfDay();
        $this->endDate = Carbon::parse($endDate ?: now()->endOfMonth())->endOfDay();
    }

    public function query(): Builder
    {
        return Transaction::query()
            ->where('user_id', $this->userId)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'asc');
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Date',
            'Type',
            'Description',
            'Amount (₦)',
            'Status',
            'Reference',

        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->created_at->format('Y-m-d H:i:s'),
            ucfirst($transaction->type),
            $transaction->description,
            number_format($transaction->amount, 2),
            ucfirst($transaction->status->value),
            $transaction->reference,

        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
