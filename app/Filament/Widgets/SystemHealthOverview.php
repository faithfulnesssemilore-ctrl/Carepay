<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemHealthOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $kycVerificationRate = User::count() > 0
            ? number_format((User::where('kyc_verified', true)->count() / User::count()) * 100, 1)
            : '0';

        $failedTransactions = Transaction::where('status', 'failed')->count();
        $pendingTransactions = Transaction::where('status', 'pending')->count();

        return [
            Stat::make('KYC Verification Rate', $kycVerificationRate.'%')
                ->description('Users verified')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->icon('heroicon-o-identification'),

            Stat::make('Pending Transactions', $pendingTransactions)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Failed Transactions', $failedTransactions)
                ->description('Requires attention')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('System Status', 'Healthy')
                ->description('All systems operational')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-heart'),
        ];
    }
}
