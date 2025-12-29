<?php

namespace App\Filament\Resources\MasterBayars;

use App\Filament\Resources\MasterBayars\Pages\CreateMasterBayar;
use App\Filament\Resources\MasterBayars\Pages\EditMasterBayar;
use App\Filament\Resources\MasterBayars\Pages\ListMasterBayars;
use App\Filament\Resources\MasterBayars\Schemas\MasterBayarForm;
use App\Filament\Resources\MasterBayars\Tables\MasterBayarsTable;
use App\Models\MasterBayar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterBayarResource extends Resource
{
    protected static ?string $model = MasterBayar::class;

    protected static ?string $navigationLabel = 'Master Bayar';

    protected static ?string $modelLabel = 'Master Bayar';
    protected static ?string $pluralModelLabel = 'Master Bayar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $recordTitleAttribute = 'jenis_bayar';

    public static function form(Schema $schema): Schema
    {
        return MasterBayarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterBayarsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterBayars::route('/'),
            'create' => CreateMasterBayar::route('/create'),
            'edit' => EditMasterBayar::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_master_bayar');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_master_bayar');
    }
}
