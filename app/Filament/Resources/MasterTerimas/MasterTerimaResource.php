<?php

namespace App\Filament\Resources\MasterTerimas;

use App\Filament\Resources\MasterTerimas\Pages\CreateMasterTerima;
use App\Filament\Resources\MasterTerimas\Pages\EditMasterTerima;
use App\Filament\Resources\MasterTerimas\Pages\ListMasterTerimas;
use App\Filament\Resources\MasterTerimas\Schemas\MasterTerimaForm;
use App\Filament\Resources\MasterTerimas\Tables\MasterTerimasTable;
use App\Models\MasterTerima;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterTerimaResource extends Resource
{
    protected static ?string $model = MasterTerima::class;

    protected static ?string $navigationLabel = 'Master Terima';

    protected static ?string $modelLabel = 'Master Terima';
    protected static ?string $pluralModelLabel = 'Master Terima';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $recordTitleAttribute = 'jenis_terima';

    public static function form(Schema $schema): Schema
    {
        return MasterTerimaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterTerimasTable::configure($table);
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
            'index' => ListMasterTerimas::route('/'),
            'create' => CreateMasterTerima::route('/create'),
            'edit' => EditMasterTerima::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_master_terima');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_master_terima');
    }
}
