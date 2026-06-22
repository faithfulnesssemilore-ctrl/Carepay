<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentTransactionsTable;
use App\Filament\Widgets\SystemHealthOverview;
use App\Filament\Widgets\TransactionStatsOverview;
use App\Filament\Widgets\TransactionStatusChart;
use App\Filament\Widgets\TransactionTypeChart;
use App\Filament\Widgets\TransactionVolumeChart;
use App\Filament\Widgets\UsersStatsOverview;
use App\Filament\Widgets\WalletStatsOverview;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            SystemHealthOverview::class,
            TransactionStatsOverview::class,
            WalletStatsOverview::class,
            UsersStatsOverview::class,
            TransactionVolumeChart::class, // Line chart for transaction volume
            TransactionTypeChart::class,
            TransactionStatusChart::class,
            RecentTransactionsTable::class,
        ];
    }
public function getColumns(): array | int
    {
        return 2;
    }
}
