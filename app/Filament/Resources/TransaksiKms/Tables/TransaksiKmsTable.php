<?php

namespace App\Filament\Resources\TransaksiKms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class TransaksiKmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_km')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Nomor transaksi disalin!')
                    ->copyMessageDuration(1500),
                TextColumn::make('tanggal_km')
                    ->date()
                    ->sortable(),
                TextColumn::make('jenisKas.jenis_kas')
                    ->label('Jenis Kas')
                    ->sortable(),
                TextColumn::make('masterTerima.jenis_terima')
                    ->label('Terima Dari')
                    ->sortable(),
                TextColumn::make('nominal_km')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),
                TextColumn::make('id_klasifikasi')
                    ->searchable(),
                TextColumn::make('keterangan_km')
                    ->searchable(),
                TextColumn::make('pembuat')
                    ->searchable(),
            ])
            ->filters([
                Filter::make('tanggal_km')
                    ->form([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->default(now()->endOfMonth()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $date) => $q->whereDate('tanggal_km', '>=', $date))
                            ->when($data['sampai'], fn ($q, $date) => $q->whereDate('tanggal_km', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->format('d M Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->format('d M Y');
                        }
                        return $indicators;
                    }),
                SelectFilter::make('id_jenis_kas')
                    ->label('Jenis Kas')
                    ->options(function () {
                        $user = auth()->user();
                        $allOptions = [
                            1 => 'Kas Kecil (KGS)',
                            2 => 'Kas Office (OGS)',
                            3 => 'Kas Personalia (PGS)',
                            4 => 'Kas Bangunan (BGS)',
                        ];
                        
                        if ($user && !$user->isSuperAdmin()) {
                            $allowedIds = $user->getAllowedJenisKasIds();
                            return array_intersect_key($allOptions, array_flip($allowedIds));
                        }
                        
                        return $allOptions;
                    }),
            ])
            ->defaultSort('tanggal_km', 'desc')
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
