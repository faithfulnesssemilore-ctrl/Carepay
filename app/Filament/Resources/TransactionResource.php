<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Transaction Details')
                    ->components([
                        FormComponents\TextInput::make('reference')
                            ->disabled(),
                        FormComponents\Select::make('type')
                            ->options([
                                'credit' => 'Credit (Incoming)',
                                'debit' => 'Debit (Outgoing)',
                            ])
                            ->disabled(),
                        FormComponents\TextInput::make('amount')
                            ->label('Amount (Kobo)')
                            ->disabled(),
                        FormComponents\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->disabled(),
                        FormComponents\Textarea::make('description')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wallet.user.full_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wallet.user.email')
                    ->label('Email')
                    ->searchable(),
                BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'success' => 'credit',
                        'danger' => 'debit',
                    ])
                    ->icons([
                        'heroicon-m-arrow-down-circle' => 'credit',
                        'heroicon-m-arrow-up-circle' => 'debit',
                    ]),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'info' => 'processing',
                        'danger' => fn ($state) => in_array($state, ['failed', 'cancelled']),
                    ]),
                TextColumn::make('reference')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
                Filter::make('created_at')
                    ->form([
                        FormComponents\DatePicker::make('created_from')
                            ->label('From'),
                        FormComponents\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
        ];
    }
}
