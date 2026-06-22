<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLimitResource\Pages;
use App\Models\UserLimit;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserLimitResource extends Resource
{
    protected static ?string $model = UserLimit::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'User Limits';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('User Transaction Limits')
                    ->description('Transaction and transfer limits for users')
                    ->components([
                        FormComponents\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        FormComponents\TextInput::make('single_transaction_limit')
                            ->label('Single Transaction Limit (Kobo)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        FormComponents\TextInput::make('daily_transfer_limit')
                            ->label('Daily Transfer Limit (Kobo)')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        FormComponents\TextInput::make('daily_transfer_used')
                            ->label('Daily Transfer Used (Kobo)')
                            ->numeric()
                            ->disabled(),
                        FormComponents\DatePicker::make('limit_reset_date')
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
                TextColumn::make('single_transaction_limit')
                    ->label('Single Txn Limit')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                    ->sortable(),
                TextColumn::make('daily_transfer_limit')
                    ->label('Daily Limit')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                    ->sortable(),
                TextColumn::make('daily_transfer_used')
                    ->label('Daily Used')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                    ->sortable(),
                TextColumn::make('limit_reset_date')
                    ->label('Reset Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListUserLimits::route('/'),
            'edit' => Pages\EditUserLimit::route('/{record}/edit'),
        ];
    }
}
