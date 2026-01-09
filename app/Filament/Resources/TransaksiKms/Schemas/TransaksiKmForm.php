<?php

namespace App\Filament\Resources\TransaksiKms\Schemas;

use App\Models\MasterTerima;
use App\Models\MasterKlasifikasi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransaksiKmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('tanggal_km')
                    ->label('Tanggal')
                    ->required()
                    ->default(now())
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                        $jenisKas = $get('id_jenis_kas');
                        if ($jenisKas && $state) {
                            // For create mode
                            if (!$record) {
                                $nextNumber = \App\Models\TransaksiKm::getNextTransactionNumber($jenisKas, $state);
                                $set('_next_no_km', $nextNumber);
                            }
                            
                            // For edit mode - update preview
                            if ($record) {
                                $originalPeriod = \Carbon\Carbon::parse($record->tanggal_km)->format('ym');
                                $newPeriod = \Carbon\Carbon::parse($state)->format('ym');
                                
                                if ($originalPeriod !== $newPeriod || $record->id_jenis_kas !== $jenisKas) {
                                    $previewNumber = \App\Models\TransaksiKm::getPreviewNumberForEdit($jenisKas, $state, $record->idx);
                                    $set('_preview_no_km', $previewNumber);
                                } else {
                                    $set('_preview_no_km', 'Tidak berubah');
                                }
                            }
                        }
                    }),

                Select::make('id_jenis_kas')
                    ->label('Jenis Kas')
                    ->options([
                        1 => 'Kas Kecil (KGS)',
                        2 => 'Kas Operasional (OGS)',
                        3 => 'Kas Personalia (PGS)',
                        4 => 'Kas Bangunan (BGS)',
                    ])
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                        if ($state) {
                            // For create mode
                            if (!$record) {
                                $nextNumber = \App\Models\TransaksiKm::getNextTransactionNumber($state, $get('tanggal_km'));
                                $set('_next_no_km', $nextNumber);
                            }
                            
                            // For edit mode - update preview
                            if ($record) {
                                $newDate = $get('tanggal_km');
                                if ($newDate) {
                                    $originalPeriod = \Carbon\Carbon::parse($record->tanggal_km)->format('ym');
                                    $newPeriod = \Carbon\Carbon::parse($newDate)->format('ym');
                                    
                                    if ($originalPeriod !== $newPeriod || $record->id_jenis_kas !== $state) {
                                        $previewNumber = \App\Models\TransaksiKm::getPreviewNumberForEdit($state, $newDate, $record->idx);
                                        $set('_preview_no_km', $previewNumber);
                                    } else {
                                        $set('_preview_no_km', 'Tidak berubah');
                                    }
                                }
                            }
                        } else {
                            $set('_next_no_km', null);
                            $set('_preview_no_km', null);
                        }
                    }),

                TextInput::make('no_km')
                    ->label('No. Kas Masuk')
                    ->disabled()
                    ->dehydrated(false)
                    ->hidden(fn ($context) => $context === 'create')
                    ->helperText(fn ($context) => $context === 'edit' 
                        ? 'Nomor akan berubah jika tanggal dipindah ke bulan/tahun yang berbeda' 
                        : 'Nomor akan di-generate otomatis'),

                // Preview nomor transaksi baru saat edit
                TextInput::make('_preview_no_km')
                    ->label('Preview Nomor Baru')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($context) => $context === 'edit')
                    ->live()
                    ->afterStateHydrated(function ($state, callable $set, callable $get, $record) {
                        if ($record) {
                            $newDate = $get('tanggal_km');
                            $newJenisKas = $get('id_jenis_kas');
                            
                            if ($newDate && $newJenisKas) {
                                $originalPeriod = \Carbon\Carbon::parse($record->tanggal_km)->format('ym');
                                $newPeriod = \Carbon\Carbon::parse($newDate)->format('ym');
                                
                                if ($originalPeriod !== $newPeriod || $record->id_jenis_kas !== $newJenisKas) {
                                    $previewNumber = \App\Models\TransaksiKm::getPreviewNumberForEdit($newJenisKas, $newDate, $record->idx);
                                    $set('_preview_no_km', $previewNumber);
                                } else {
                                    $set('_preview_no_km', 'Tidak berubah');
                                }
                            }
                        }
                    })
                    ->helperText('Nomor ini akan digunakan jika periode atau jenis kas berubah'),

                // Display next transaction number for user reference
                TextInput::make('_next_no_km')
                    ->label('Nomor Transaksi Berikutnya')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($context) => $context === 'create')
                    ->live()
                    ->afterStateHydrated(function ($state, callable $set, callable $get) {
                        $jenisKas = $get('id_jenis_kas');
                        if ($jenisKas) {
                            $nextNumber = \App\Models\TransaksiKm::getNextTransactionNumber($jenisKas);
                            $set('_next_no_km', $nextNumber);
                        }
                    })
                    ->helperText('Nomor ini akan digunakan untuk transaksi yang akan dibuat'),

                Select::make('id_terima')
                    ->label('Diterima Dari')
                    ->relationship('terima', 'jenis_terima', modifyQueryUsing: fn ($query) => $query->where('status', 0))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('jenis_terima')
                            ->label('Nama Pemberi')
                            ->required(),
                    ])
                    ->native(false),

                TextInput::make('nominal_km')
                    ->label('Nominal')
                    ->prefix('Rp')
                    ->required()
                    ->placeholder('Contoh: 25000.65')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state && $state !== '') {
                            // Remove any existing commas first
                            $cleanValue = str_replace(',', '', $state);
                            
                            // Check if it's a valid number
                            if (is_numeric($cleanValue)) {
                                $numericValue = (float) $cleanValue;
                                $formatted = number_format($numericValue, 2, '.', ',');
                                $set('nominal_km', $formatted);
                            }
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return $state ? (float) str_replace(',', '', $state) : null;
                    })
                    ->formatStateUsing(function ($state) {
                        return $state ? number_format((float) $state, 2, '.', ',') : '';
                    }),

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

                Textarea::make('keterangan_km')
                    ->label('Keterangan')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('pembuat')
                    ->label('Pembuat')
                    ->default(auth()->user()->name ?? auth()->user()->user_id)
                    ->disabled()
                    ->dehydrated(),
            ]);
    }
}
