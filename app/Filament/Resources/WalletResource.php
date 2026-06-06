<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Models\Wallet;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Wallets';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Wallet Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        Forms\Components\TextInput::make('balance')
                            ->label('Balance (Kobo)')
                            ->numeric()
                            ->disabled()
                            ->helperText('Calculated in Kobo for precision'),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'NGN' => 'Nigerian Naira (₦)',
                                'USD' => 'US Dollar ($)',
                                'GBP' => 'British Pound (£)',
                            ])
                            ->default('NGN'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'frozen' => 'Frozen',
                                'closed' => 'Closed',
                            ])
                            ->default('active'),
                        Forms\Components\Checkbox::make('locked')
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
                    ->formatStateUsing(fn ($state) => '₦' . number_format(($state ?? 0) / 100, 2))
                    ->sortable(),
                TextColumn::make('currency')
                    ->badge()
                    ->colors([
                        'primary' => 'NGN',
                        'info' => 'USD',
                        'secondary' => 'GBP',
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
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (₦)')
                            ->numeric()
                            ->required()
                            ->minValue(100)
                            ->step(100),
                        Forms\Components\TextInput::make('description')
                            ->label('Reason')
                            ->default('Admin fund'),
                    ])
                    ->action(function (Wallet $record, array $data): void {
                        $amountInKobo = (int) ($data['amount'] * 100);
                        $record->balance += $amountInKobo;
                        $record->save();

                        // Log transaction
                        $record->transactions()->create([
                            'user_id' => $record->user_id,
                            'type' => 'credit',
                            'amount' => $amountInKobo,
                            'description' => $data['description'],
                            'reference' => 'ADMIN-FUND-' . uniqid(),
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
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (₦)')
                            ->numeric()
                            ->required()
                            ->minValue(100)
                            ->step(100),
                        Forms\Components\TextInput::make('description')
                            ->label('Reason')
                            ->default('Admin debit'),
                    ])
                    ->action(function (Wallet $record, array $data): void {
                        $amountInKobo = (int) ($data['amount'] * 100);
                        if ($record->balance < $amountInKobo) {
                            Notification::make()
                                ->danger()
                                ->title('Insufficient Balance')
                                ->body('Wallet balance is insufficient for this debit')
                                ->send();
                            return;
                        }

                        $record->balance -= $amountInKobo;
                        $record->save();

                        $record->transactions()->create([
                            'user_id' => $record->user_id,
                            'type' => 'debit',
                            'amount' => $amountInKobo,
                            'description' => $data['description'],
                            'reference' => 'ADMIN-DEBIT-' . uniqid(),
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
