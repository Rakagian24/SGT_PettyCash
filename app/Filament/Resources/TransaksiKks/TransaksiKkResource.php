<?php

namespace App\Filament\Resources\TransaksiKks;

use App\Filament\Resources\TransaksiKks\Pages\CreateTransaksiKk;
use App\Filament\Resources\TransaksiKks\Pages\EditTransaksiKk;
use App\Filament\Resources\TransaksiKks\Pages\ListTransaksiKks;
use App\Filament\Resources\TransaksiKks\Schemas\TransaksiKkForm;
use App\Filament\Resources\TransaksiKks\Tables\TransaksiKksTable;
use App\Models\TransaksiKk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransaksiKkResource extends Resource
{
    protected static ?string $model = TransaksiKk::class;

    protected static ?string $navigationLabel = 'Transaksi Kas Keluar';

    protected static ?string $modelLabel = 'Transaksi Kas Keluar';
    protected static ?string $pluralModelLabel = 'Transaksi Kas Keluar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $recordTitleAttribute = 'no_kk';

    public static function form(Schema $schema): Schema
    {
        return TransaksiKkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        $table = TransaksiKksTable::configure($table);
        
        // Apply jenis kas filter for non-super admin users
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $allowedJenisKas = $user->getAllowedJenisKasIds();
            if (!empty($allowedJenisKas)) {
                $table->modifyQueryUsing(fn ($query) => $query->whereIn('id_jenis_kas', $allowedJenisKas));
            }
        }
        
        return $table;
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
            'index' => ListTransaksiKks::route('/'),
            'create' => CreateTransaksiKk::route('/create'),
            'edit' => EditTransaksiKk::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_transaksi_kk');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_transaksi_kk');
    }
}
