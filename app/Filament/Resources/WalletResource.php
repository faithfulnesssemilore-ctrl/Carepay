<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Models\Transaction;
use App\Models\Wallet;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Wallets';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Wallet Information')
                    ->components([
                        FormComponents\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        FormComponents\TextInput::make('balance')
                            ->label('Balance (Kobo)')
                            ->numeric()
                            ->disabled()
                            ->helperText('Calculated in Kobo for precision'),
                        FormComponents\Select::make('currency')
                            ->options([
                                'NGN' => 'Nigerian Naira (₦)',

                            ])
                            ->default('NGN'),
                        FormComponents\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'frozen' => 'Frozen',
                                'closed' => 'Closed',
                            ])
                            ->default('active'),
                        FormComponents\Checkbox::make('locked')
                            ->label('Locked (Anti-fraud)')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.full_name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('balance')
                    ->label('Balance')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                    ->sortable(),
                TextColumn::make('currency')
                    ->badge()
                    ->colors([
                        'primary' => 'NGN',
                        'info' => 'NGN',
                        'secondary' => 'N/A',
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'frozen',
                        'danger' => 'closed',
                    ]),
                TextColumn::make('transactions_count')
                    ->label('Transactions')
                    ->counts('transactions'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Action::make('fund')
                    ->label('Fund Wallet')
                    ->icon('heroicon-m-plus-circle')
                    ->modalHeading('Fund user wallet')
                    ->modalSubheading('Add funds directly to the selected wallet with a transaction record.')
                    ->form([
                        FormComponents\TextInput::make('amount')
                            ->label('Amount (₦)')
                            ->placeholder('Enter amount in Naira')
                            ->numeric()
                            ->required()
                            ->minValue(100)
                            ->step(100)
                            ->helperText('Minimum funding amount is ₦100.'),
                        FormComponents\TextInput::make('description')
                            ->label('Reason')
                            ->placeholder('Admin fund')
                            ->default('Admin fund'),
                    ])
                    ->action(function (Wallet $record, array $data): void {
                        $amountInKobo = (int) ($data['amount'] * 100);
                        $record->increment('balance', $amountInKobo);

                        $record->transactions()->create([
                            'user_id' => $record->user_id,
                            'type' => 'credit',
                            'amount' => $amountInKobo / 100,
                            'description' => $data['description'],
                            'reference' => 'ADMIN-FUND-'.uniqid(),
                            'status' => 'completed',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Wallet Funded')
                            ->body("₦{$data['amount']} added to wallet")
                            ->send();
                    }),
                Action::make('debit')
                    ->label('Debit Wallet')
                    ->icon('heroicon-m-minus-circle')
                    ->color('danger')
                    ->modalHeading('Debit user wallet')
                    ->modalSubheading('Remove funds from the selected wallet and log a debit transaction.')
                    ->form([
                        FormComponents\TextInput::make('amount')
                            ->label('Amount (₦)')
                            ->placeholder('Enter amount in Naira')
                            ->numeric()
                            ->required()
                            ->minValue(100)
                            ->step(100)
                            ->helperText('Debits cannot exceed the current balance.'),
                        FormComponents\TextInput::make('description')
                            ->label('Reason')
                            ->placeholder('Admin debit')
                            ->default('Admin debit'),
                    ])
                    ->action(function (Wallet $record, array $data): void {
                        $amountInKobo = (int) ($data['amount'] * 100);
                        if ($record->getRawOriginal('balance') < $amountInKobo) {
                            Notification::make()
                                ->danger()
                                ->title('Insufficient Balance')
                                ->body('Wallet balance is insufficient for this debit')
                                ->send();

                            return;
                        }

                        $record->decrement('balance', $amountInKobo);

                        $record->transactions()->create([
                            'user_id' => $record->user_id,
                            'type' => 'debit',
                            'amount' => $amountInKobo / 100,
                            'description' => $data['description'],
                            'reference' => 'ADMIN-DEBIT-'.uniqid(),
                            'status' => 'completed',
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Wallet Debited')
                            ->body("₦{$data['amount']} removed from wallet")
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWallets::route('/'),
            'create' => Pages\CreateWallet::route('/create'),
            'edit' => Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}
