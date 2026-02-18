<?php

namespace App\Filament\Resources\TransaksiKks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;

class TransaksiKksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_kk')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Nomor transaksi disalin!')
                    ->copyMessageDuration(1500),
                TextColumn::make('tanggal_kk')
                    ->date()
                    ->sortable(),
                TextColumn::make('jenisKas.jenis_kas')
                    ->label('Jenis Kas')
                    ->sortable(),
                TextColumn::make('masterBayar.jenis_bayar')
                    ->label('Bayar Ke')
                    ->sortable(),
                TextColumn::make('nominal_kk')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),
                TextColumn::make('id_klasifikasi')
                    ->searchable(),
                TextColumn::make('keterangan_kk')
                    ->searchable(),
                TextColumn::make('pembuat')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('tanggal_kk')
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
                            ->when($data['dari'], fn ($q, $date) => $q->whereDate('tanggal_kk', '>=', $date))
                            ->when($data['sampai'], fn ($q, $date) => $q->whereDate('tanggal_kk', '<=', $date));
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
            ->defaultSort('tanggal_kk', 'desc')
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
