<?php

namespace App\Filament\Resources\MasterKlasifikasis\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterKlasifikasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kriteria')
                    ->default(null),
                TextInput::make('klasifikasi')
                    ->default(null),
                TextInput::make('coa')
                    ->numeric()
                    ->default(null),
                TextInput::make('tipe_klasifikasi')
                    ->default(null),
                TextInput::make('status')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
