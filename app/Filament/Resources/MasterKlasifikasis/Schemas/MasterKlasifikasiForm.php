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
                TextInput::make('id_klasifikasi')
                    ->label('ID Klasifikasi')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->helperText('Masukkan ID Klasifikasi unik'),
                    
                TextInput::make('kriteria')
                    ->label('Kriteria')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('klasifikasi')
                    ->label('Klasifikasi')
                    ->required()
                    ->maxLength(255),
                    
                TextInput::make('coa')
                    ->label('COA')
                    ->maxLength(255),
                    
                TextInput::make('tipe_klasifikasi')
                    ->label('Tipe Klasifikasi')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Contoh: KM (Kas Masuk) atau KK (Kas Keluar)'),
                    
                TextInput::make('status')
                    ->label('Status')
                    ->numeric()
                    ->default(0)
                    ->dehydrated()
                    ->hidden(),
            ]);
    }
}
