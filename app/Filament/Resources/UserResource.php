<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->description('Basic user details')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('username')
                            ->unique(User::class, 'username', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                    ])->columns(2),

                Forms\Components\Section::make('Security & Authentication')
                    ->description('Password and authentication settings')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\TextInput::make('pin')
                            ->label('Transaction PIN')
                            ->password()
                            ->revealable()
                            ->maxLength(6),
                    ])->columns(2),

                Forms\Components\Section::make('Identity & Compliance')
                    ->description('KYC and identity verification')
                    ->schema([
                        Forms\Components\Select::make('id_type')
                            ->options([
                                'national_id' => 'National ID',
                                'passport' => 'Passport',
                                'drivers_license' => "Driver's License",
                                'bvn' => 'BVN',
                            ]),
                        Forms\Components\TextInput::make('id_number')
                            ->maxLength(255),
                        Forms\Components\Checkbox::make('kyc_verified')
                            ->label('KYC Verified')
                            ->default(false),
                        Forms\Components\Checkbox::make('registration_complete')
                            ->label('Registration Complete')
                            ->default(false),
                        Forms\Components\Checkbox::make('terms_accepted')
                            ->label('Terms & Conditions Accepted')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Account Status')
                    ->description('User account settings')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->options([
                                0 => 'User',
                                1 => 'Moderator',
                                2 => 'Admin',
                            ])
                            ->default(0),
                        Forms\Components\Select::make('status')
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
                    ->formatStateUsing(fn ($state) => '₦' . number_format($state / 100, 2))
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
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
