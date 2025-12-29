<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebUserResource\Pages;
use App\Models\WebUser;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Hash;

class WebUserResource extends Resource
{
    protected static ?string $model = WebUser::class;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-users';
    }
    
    public static function getNavigationLabel(): string
    {
        return 'User Management';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                    
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                    
                Select::make('role')
                    ->label('Role')
                    ->options(WebUser::getAvailableRoles())
                    ->required()
                    ->default('user')
                    ->live(), // Make it reactive
                    
                CheckboxList::make('permissions')
                    ->label('Permissions')
                    ->options(WebUser::getAvailablePermissions())
                    ->columns(2)
                    ->visible(fn (callable $get) => $get('role') !== 'super_admin')
                    ->helperText('Super Admin memiliki akses ke semua menu secara otomatis'),
                    
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'user' => 'success',
                        default => 'gray',
                    }),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options(WebUser::getAvailableRoles()),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebUsers::route('/'),
            'create' => Pages\CreateWebUser::route('/create'),
            'edit' => Pages\EditWebUser::route('/{record}/edit'),
        ];
    }
}