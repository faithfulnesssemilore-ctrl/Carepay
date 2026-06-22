<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            Stat::make('Today\'s Transactions', Transaction::whereDate('created_at', $today)->count())
                ->description('Completed today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->icon('heroicon-o-arrow-path'),

            Stat::make('Weekly Volume', '₦'.number_format(Transaction::where('created_at', '>=', $thisWeek)->sum('amount'), 2))
                ->description('Transaction value this week')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success')
                ->icon('heroicon-o-currency-naira'),

            Stat::make('Monthly Transactions', Transaction::where('created_at', '>=', $thisMonth)->count())
                ->description('This month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Failed Transactions', Transaction::where('status', 'failed')->count())
                ->description('Needs investigation')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
