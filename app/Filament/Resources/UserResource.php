<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Personal Information')
                    ->description('Basic user details')
                    ->components([
                        FormComponents\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        FormComponents\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        FormComponents\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),
                        FormComponents\TextInput::make('username')
                            ->unique(User::class, 'username', ignoreRecord: true)
                            ->maxLength(255),
                        FormComponents\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                SchemaComponents\Section::make('Security & Authentication')
                    ->description('Password and authentication settings')
                    ->components([
                        FormComponents\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
                            ->required(fn (string $context): bool => $context === 'create'),
                        FormComponents\TextInput::make('pin')
                            ->label('Transaction PIN')
                            ->password()
                            ->revealable()
                            ->maxLength(6),
                    ])->columns(2),

                SchemaComponents\Section::make('Identity & Compliance')
                    ->description('KYC and identity verification')
                    ->components([
                        FormComponents\Select::make('id_type')
                            ->options([
                                'national_id' => 'National ID',
                                'passport' => 'Passport',
                                'drivers_license' => "Driver's License",
                                'bvn' => 'BVN',
                            ]),
                        FormComponents\TextInput::make('id_number')
                            ->maxLength(255),
                        FormComponents\Checkbox::make('kyc_verified')
                            ->label('KYC Verified')
                            ->default(false),
                        FormComponents\Checkbox::make('registration_complete')
                            ->label('Registration Complete')
                            ->default(false),
                        FormComponents\Checkbox::make('terms_accepted')
                            ->label('Terms & Conditions Accepted')
                            ->default(false),
                    ])->columns(2),

                SchemaComponents\Section::make('Account Status')
                    ->description('User account settings')
                    ->components([
                        FormComponents\Select::make('role')
                            ->options([
                                0 => 'User',
                                1 => 'Moderator',
                                2 => 'Admin',
                            ])
                            ->default(0),
                        FormComponents\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'suspended' => 'Suspended',
                                'blocked' => 'Blocked',
                            ])
                            ->default('active'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('username')
                    ->searchable(),
                BadgeColumn::make('kyc_verified')
                    ->label('KYC')
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => true,
                        'heroicon-o-exclamation-circle' => false,
                    ]),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => fn ($state) => in_array($state, ['suspended', 'blocked']),
                    ]),
                TextColumn::make('wallet.balance')
                    ->label('Wallet Balance')
                    ->formatStateUsing(fn ($state) => '₦'.number_format($state ?? 0, 2))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended',
                        'blocked' => 'Blocked',
                    ]),
                SelectFilter::make('kyc_verified')
                    ->options([
                        1 => 'KYC Verified',
                        0 => 'Not Verified',
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
