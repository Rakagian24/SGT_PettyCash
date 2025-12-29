<?php

namespace App\Filament\Resources\MasterTerimas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MasterTerimasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis_terima')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'success',
                        1 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Aktif',
                        1 => 'Tidak Aktif',
                        default => 'Unknown',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        '0' => 'Aktif',
                        '1' => 'Tidak Aktif',
                    ])
                    ->label('Status'),
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
