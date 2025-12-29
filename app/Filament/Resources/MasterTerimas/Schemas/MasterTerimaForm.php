<?php

namespace App\Filament\Resources\MasterTerimas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterTerimaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('jenis_terima')
                    ->default(null),
                Select::make('status')
                    ->options([
                        0 => 'Aktif',
                        1 => 'Tidak Aktif',
                    ])
                    ->default(0)
                    ->required(),
            ]);
    }
}
