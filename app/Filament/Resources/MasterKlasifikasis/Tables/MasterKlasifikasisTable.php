<?php

namespace App\Filament\Resources\MasterKlasifikasis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MasterKlasifikasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_klasifikasi')
                    ->searchable(),
                TextColumn::make('kriteria')
                    ->searchable(),
                TextColumn::make('klasifikasi')
                    ->searchable(),
                TextColumn::make('coa')
                    ->searchable(),
                TextColumn::make('tipe_klasifikasi')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('tipe_klasifikasi')
                    ->label('Tipe Klasifikasi')
                    ->options([
                        'KK' => 'KK (Kas Keluar)',
                        'KM' => 'KM (Kas Masuk)',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
