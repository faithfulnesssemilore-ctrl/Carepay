<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class RecentTransactionsTable extends BaseWidget
{
    protected static ?int $sort = 8;

    protected function getTableQuery(): Builder|Relation|null
    {
        return Transaction::query()->latest()->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('ID')
                ->searchable()
                ->sortable(),
            TextColumn::make('user.email')
                ->label('User')
                ->searchable()
                ->sortable(),
            TextColumn::make('amount')
                ->label('Amount')
                ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                ->sortable(),
            TextColumn::make('type')
                ->badge()
                ->colors([
                    'success' => 'credit',
                    'danger' => 'debit',
                ]),
            BadgeColumn::make('status')
                ->colors([
                    'success' => 'completed',
                    'warning' => 'pending',
                    'info' => 'processing',
                    'danger' => 'failed',
                ]),
            TextColumn::make('created_at')
                ->label('Date')
                ->dateTime()
                ->sortable(),
        ];
    }

    public static function getHeading(): ?string
    {
        return 'Recent Transactions';
    }
}
