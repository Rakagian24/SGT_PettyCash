<?php

namespace App\Filament\Resources\TransaksiKks\Schemas;

use App\Models\MasterBayar;
use App\Models\MasterKlasifikasi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransaksiKkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal_kk')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),

                Select::make('id_jenis_kas')
                    ->label('Jenis Kas')
                    ->options([
                        1 => 'Kas Kecil (KGS)',
                        2 => 'Kas Office (OGS)',
                        3 => 'Kas Personalia (PGS)',
                        4 => 'kas Bangunan (BGS)',
                    ])
                    ->required()
                    ->searchable()
                    ->native(false),

                TextInput::make('no_kk')
                    ->label('No. Kas Keluar')
                    ->disabled()
                    ->dehydrated(false)
                    ->hidden(fn ($context) => $context === 'create')
                    ->helperText('Nomor akan di-generate otomatis'),

                Select::make('id_bayar')
                    ->label('Dibayar Kepada')
                    ->relationship('bayar', 'jenis_bayar', modifyQueryUsing: fn ($query) => $query->where('status', 0))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('jenis_bayar')
                            ->label('Nama Penerima')
                            ->required(),
                    ])
                    ->native(false),

                TextInput::make('nominal_kk')
                    ->label('Nominal')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Select::make('id_klasifikasi')
                    ->label('Klasifikasi')
                    ->relationship('klasifikasi', 'klasifikasi')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateHydrated(function ($state, callable $set) {
                        if ($state) {
                            $klasifikasi = \App\Models\MasterKlasifikasi::find($state);
                            if ($klasifikasi) {
                                $set('_kriteria', $klasifikasi->kriteria);
                                $set('_id_klasifikasi_display', $klasifikasi->id_klasifikasi);
                            }
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $klasifikasi = \App\Models\MasterKlasifikasi::find($state);
                            if ($klasifikasi) {
                                $set('_kriteria', $klasifikasi->kriteria);
                                $set('_id_klasifikasi_display', $klasifikasi->id_klasifikasi);
                            }
                        } else {
                            $set('_kriteria', null);
                            $set('_id_klasifikasi_display', null);
                        }
                    }),

                TextInput::make('_kriteria')
                    ->label('Kriteria')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($get) => $get('id_klasifikasi') !== null),

                TextInput::make('_id_klasifikasi_display')
                    ->label('ID Klasifikasi')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($get) => $get('id_klasifikasi') !== null),

                Textarea::make('keterangan_kk')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('pembuat')
                    ->label('Pembuat')
                    ->default(auth()->user()->name ?? auth()->user()->user_id)
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('status')
                    ->numeric()
                    ->default(0)
                    ->hidden(),
            ]);
    }
}
