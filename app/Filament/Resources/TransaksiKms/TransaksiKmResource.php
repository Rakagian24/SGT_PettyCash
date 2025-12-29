<?php

namespace App\Filament\Resources\TransaksiKms;

use App\Filament\Resources\TransaksiKms\Pages\CreateTransaksiKm;
use App\Filament\Resources\TransaksiKms\Pages\EditTransaksiKm;
use App\Filament\Resources\TransaksiKms\Pages\ListTransaksiKms;
use App\Filament\Resources\TransaksiKms\Schemas\TransaksiKmForm;
use App\Filament\Resources\TransaksiKms\Tables\TransaksiKmsTable;
use App\Models\TransaksiKm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransaksiKmResource extends Resource
{
    protected static ?string $model = TransaksiKm::class;

    protected static ?string $navigationLabel = 'Transaksi Kas Masuk';

    protected static ?string $modelLabel = 'Transaksi Kas Masuk';
    protected static ?string $pluralModelLabel = 'Transaksi Kas Masuk';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $recordTitleAttribute = 'no_km';

    public static function form(Schema $schema): Schema
    {
        return TransaksiKmForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransaksiKmsTable::configure($table);
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
            'index' => ListTransaksiKms::route('/'),
            'create' => CreateTransaksiKm::route('/create'),
            'edit' => EditTransaksiKm::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_transaksi_km');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_transaksi_km');
    }
}
