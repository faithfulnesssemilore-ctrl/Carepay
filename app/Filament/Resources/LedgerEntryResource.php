<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgerEntryResource\Pages;
use App\Models\LedgerEntry;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LedgerEntryResource extends Resource
{
    protected static ?string $model = LedgerEntry::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Ledger Entries';

    protected static ?int $navigationSort = 9;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Ledger Entry Details')
                    ->description('Ledger transaction record')
                    ->components([
                        FormComponents\TextInput::make('id')
                            ->disabled(),
                        FormComponents\Select::make('wallet_id')
                            ->relationship('wallet', 'id')
                            ->searchable()
                            ->disabled(),
                        FormComponents\TextInput::make('transaction_id')
                            ->disabled(),
                        FormComponents\Select::make('entry_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                                'fee' => 'Fee',
                                'reversal' => 'Reversal',
                            ])
                            ->disabled(),
                        FormComponents\TextInput::make('amount')
                            ->label('Amount (Kobo)')
                            ->disabled(),
                        FormComponents\TextInput::make('currency')
                            ->disabled(),
                        FormComponents\Textarea::make('description')
                            ->disabled(),
                        FormComponents\TextInput::make('created_at')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('wallet.user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('entry_type')
                    ->label('Type')
                    ->sortable()
                    ->badge()
                    ->colors([
                        'danger' => 'debit',
                        'success' => 'credit',
                        'warning' => 'fee',
                        'info' => 'reversal',
                    ]),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                    ->sortable(),
                TextColumn::make('currency')
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('entry_type')
                    ->options([
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                        'fee' => 'Fee',
                        'reversal' => 'Reversal',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgerEntries::route('/'),
            'view' => Pages\ViewLedgerEntry::route('/{record}'),
        ];
    }
}
