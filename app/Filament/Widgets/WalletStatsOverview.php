<?php

namespace App\Filament\Widgets;

use App\Models\Wallet;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WalletStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalWallets = Wallet::count();
        $activeWallets = Wallet::where('status', 'active')->count();
        $lockedWallets = Wallet::where('locked', true)->count();
        $totalBalance = Wallet::sum('balance');

        return [
            Stat::make('Total Wallets', $totalWallets)
                ->description('Active user wallets')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('info')
                ->icon('heroicon-o-wallet'),

            Stat::make('Active Wallets', $activeWallets)
                ->description(number_format(($activeWallets / $totalWallets) * 100, 1).'% of total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-badge'),

            Stat::make('Locked Wallets', $lockedWallets)
                ->description('Temporarily locked')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning')
                ->icon('heroicon-o-lock-closed'),

            Stat::make('Total Balance', '₦'.number_format($totalBalance, 2))
                ->description('All wallets combined')
                ->descriptionIcon('heroicon-m-currency-naira')
                ->color('primary')
                ->icon('heroicon-o-currency-naira'),
        ];
    }
}
