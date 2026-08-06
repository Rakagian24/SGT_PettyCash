<?php

namespace App\Filament\Resources\MasterKlasifikasis;

use App\Filament\Resources\MasterKlasifikasis\Pages\CreateMasterKlasifikasi;
use App\Filament\Resources\MasterKlasifikasis\Pages\EditMasterKlasifikasi;
use App\Filament\Resources\MasterKlasifikasis\Pages\ListMasterKlasifikasis;
use App\Filament\Resources\MasterKlasifikasis\Schemas\MasterKlasifikasiForm;
use App\Filament\Resources\MasterKlasifikasis\Tables\MasterKlasifikasisTable;
use App\Models\MasterKlasifikasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterKlasifikasiResource extends Resource
{
    protected static ?string $model = MasterKlasifikasi::class;

    protected static ?string $navigationLabel = 'Master Klasifikasi';

    protected static ?string $modelLabel = 'Master Klasifikasi';
    protected static ?string $pluralModelLabel = 'Master Klasifikasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $recordTitleAttribute = 'klasifikasi';

    public static function form(Schema $schema): Schema
    {
        return MasterKlasifikasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterKlasifikasisTable::configure($table);
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
            'index' => ListMasterKlasifikasis::route('/'),
            'create' => CreateMasterKlasifikasi::route('/create'),
            'edit' => EditMasterKlasifikasi::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var \App\Models\WebUser|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_master_klasifikasi');
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\WebUser|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_master_klasifikasi');
    }
}
