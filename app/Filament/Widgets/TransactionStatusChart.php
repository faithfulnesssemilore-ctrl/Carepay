<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionStatusChart extends ChartWidget
{
    protected ?string $heading = 'Transaction Status Distribution';

    protected static ?int $sort = 7;

    protected function getData(): array
    {
        $completed = Transaction::where('status', 'completed')->count();
        $pending = Transaction::where('status', 'pending')->count();
        $processing = Transaction::where('status', 'processing')->count();
        $failed = Transaction::where('status', 'failed')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => [$completed, $pending, $processing, $failed],
                    'backgroundColor' => [
                        '#10b981', // Green for completed
                        '#f59e0b', // Amber for pending
                        '#3b82f6', // Blue for processing
                        '#ef4444', // Red for failed
                    ],
                    'borderColor' => [
                        '#059669',
                        '#d97706',
                        '#1d4ed8',
                        '#dc2626',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Completed', 'Pending', 'Processing', 'Failed'],
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
