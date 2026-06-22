<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduledPaymentResource\Pages;
use App\Models\ScheduledPayment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScheduledPaymentResource extends Resource
{
    protected static ?string $model = ScheduledPayment::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Scheduled Payments';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Payment Details')
                    ->description('Scheduled payment information')
                    ->components([
                        FormComponents\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        FormComponents\Select::make('wallet_id')
                            ->relationship('wallet', 'id')
                            ->searchable()
                            ->required()
                            ->disabled(fn (string $context): bool => $context === 'edit'),
                        FormComponents\Select::make('bank_account_id')
                            ->relationship('bankAccount', 'account_number')
                            ->searchable()
                            ->required(),
                        FormComponents\TextInput::make('amount')
                            ->label('Amount (Kobo)')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        FormComponents\Select::make('currency')
                            ->options([
                                'NGN' => 'Nigerian Naira (₦)',
                            ])
                            ->default('NGN')
                            ->required(),
                    ])->columns(2),

                SchemaComponents\Section::make('Schedule Information')
                    ->description('When to execute the payment')
                    ->components([
                        FormComponents\DateTimePicker::make('scheduled_date')
                            ->required()
                            ->minDateTime(now()),
                        FormComponents\TextInput::make('description')
                            ->maxLength(255),
                    ])->columns(2),

                SchemaComponents\Section::make('Status')
                    ->description('Payment status')
                    ->components([
                        FormComponents\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state, 2))
                    ->sortable(),
                TextColumn::make('bankAccount.account_number')
                    ->label('Bank Account')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('scheduled_date')
                    ->label('Scheduled For')
                    ->dateTime()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'info' => 'processing',
                        'danger' => fn ($state) => in_array($state, ['failed', 'cancelled']),
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'processing',
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduledPayments::route('/'),
            'edit' => Pages\EditScheduledPayment::route('/{record}/edit'),
        ];
    }
}
