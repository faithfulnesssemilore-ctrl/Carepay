<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VirtualAccountResource\Pages;
use App\Models\VirtualAccount;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VirtualAccountResource extends Resource
{
    protected static ?string $model = VirtualAccount::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Virtual Accounts';

    protected static ?int $navigationSort = 6;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Virtual Account Details')
                    ->description('Virtual account information')
                    ->components([
                        FormComponents\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        FormComponents\TextInput::make('account_number')
                            ->required()
                            ->unique(VirtualAccount::class, 'account_number', ignoreRecord: true)
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        FormComponents\TextInput::make('account_name')
                            ->required(),
                        FormComponents\TextInput::make('bank_name')
                            ->required(),
                        FormComponents\TextInput::make('provider')
                            ->required(),
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
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('account_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bank_name')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('provider')
                    ->searchable()
                    ->sortable()
                    ->badge(),
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
                SelectFilter::make('provider')
                    ->options([
                        'Flutterwave' => 'Flutterwave',
                        'Paystack' => 'Paystack',
                        'Interswitch' => 'Interswitch',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVirtualAccounts::route('/'),
            'edit' => Pages\EditVirtualAccount::route('/{record}/edit'),
        ];
    }
}
