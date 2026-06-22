<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionTypeChart extends ChartWidget
{
    protected ?string $heading = 'Transaction Types (Today)';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $today = today();

        $creditCount = Transaction::whereDate('created_at', $today)
            ->where('type', 'credit')
            ->count();

        $debitCount = Transaction::whereDate('created_at', $today)
            ->where('type', 'debit')
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => [$creditCount, $debitCount],
                    'backgroundColor' => [
                        '#10b981', // Green for credit
                        '#ef4444', // Red for debit
                    ],
                    'borderColor' => [
                        '#059669',
                        '#dc2626',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Credit (Incoming)', 'Debit (Outgoing)'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
