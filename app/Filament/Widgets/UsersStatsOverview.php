<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UsersStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Active user accounts')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-users'),
            Stat::make('Verified Users', User::where('kyc_verified', true)->count())
                ->description('KYC verified accounts')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info')
                ->icon('heroicon-o-check-badge'),
            Stat::make('New Users (7 days)', User::where('created_at', '>=', now()->subDays(7))->count())
                ->description('Users joined this week')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
