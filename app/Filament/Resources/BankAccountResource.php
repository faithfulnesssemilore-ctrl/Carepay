<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Bank Accounts';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Bank Account Details')
                    ->description('Linked bank account information')
                    ->components([
                        FormComponents\Select::make('wallet_id')
                            ->relationship('wallet', 'id')
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        FormComponents\TextInput::make('bank_name')
                            ->required()
                            ->maxLength(255),
                        FormComponents\TextInput::make('bank_code')
                            ->required()
                            ->maxLength(10),
                        FormComponents\TextInput::make('account_number')
                            ->required()
                            ->unique(BankAccount::class, 'account_number', ignoreRecord: true)
                            ->maxLength(20),
                        FormComponents\TextInput::make('account_name')
                            ->required()
                            ->maxLength(255),
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
                TextColumn::make('bank_name')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('bank_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('account_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bank_name')
                    ->options([
                        'Access Bank' => 'Access Bank',
                        'GTB' => 'GTB',
                        'Zenith Bank' => 'Zenith Bank',
                        'First Bank' => 'First Bank',
                        'UBA' => 'UBA',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
