<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components as FormComponents;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Audit Logs';

    protected static ?int $navigationSort = 10;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Audit Details')
                    ->description('User action audit log')
                    ->components([
                        FormComponents\TextInput::make('id')
                            ->disabled(),
                        FormComponents\TextInput::make('user_id')
                            ->label('User ID')
                            ->disabled(),
                        FormComponents\TextInput::make('action')
                            ->disabled(),
                        FormComponents\TextInput::make('entity_type')
                            ->disabled(),
                        FormComponents\TextInput::make('entity_id')
                            ->disabled(),
                        FormComponents\Textarea::make('changes')
                            ->disabled(),
                        FormComponents\TextInput::make('ip_address')
                            ->disabled(),
                        FormComponents\TextInput::make('user_agent')
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
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('action')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('entity_type')
                    ->label('Entity Type')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('entity_id')
                    ->label('Entity ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options([
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'view' => 'View',
                        'login' => 'Login',
                        'logout' => 'Logout',
                    ]),
                SelectFilter::make('entity_type')
                    ->options([
                        'user' => 'User',
                        'transaction' => 'Transaction',
                        'wallet' => 'Wallet',
                        'scheduled_payment' => 'Scheduled Payment',
                        'bank_account' => 'Bank Account',
                    ]),
                Filter::make('created_at')
                    ->form([
                        FormComponents\DatePicker::make('created_from')
                            ->label('From'),
                        FormComponents\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
