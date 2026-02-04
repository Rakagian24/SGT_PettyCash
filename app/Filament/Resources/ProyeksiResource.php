<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProyeksiResource\Pages;
use App\Models\Proyeksi;
use App\Models\MasterJenisKas;
use App\Models\MasterKlasifikasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Icons\Heroicon;

class ProyeksiResource extends Resource
{
    protected static ?string $model = Proyeksi::class;

    protected static ?string $navigationLabel = 'Laporan Proyeksi';
    protected static ?string $modelLabel = 'Proyeksi Kas';
    protected static ?string $pluralModelLabel = 'Proyeksi Kas';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_proyeksi');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_proyeksi');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('id_proyeksi')
                    ->label('ID Proyeksi')
                    ->default(fn () => Proyeksi::generatePRKNumber())
                    ->disabled()
                    ->dehydrated(),

                DatePicker::make('tgl_dari')
                    ->label('Tanggal Dari')
                    ->required()
                    ->default(now())
                    ->native(false),

                DatePicker::make('tgl_sampai')
                    ->label('Tanggal Sampai')
                    ->required()
                    ->default(now())
                    ->native(false),

                Select::make('id_jenis_kas')
                    ->label('Jenis Kas')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && !$user->isSuperAdmin()) {
                            $allowedIds = $user->getAllowedJenisKasIds();
                            return MasterJenisKas::where('status', 0)
                                ->whereIn('id_jenis_kas', $allowedIds)
                                ->pluck('jenis_kas', 'id_jenis_kas');
                        }
                        return MasterJenisKas::where('status', 0)->pluck('jenis_kas', 'id_jenis_kas');
                    })
                    ->required()
                    ->searchable()
                    ->native(false),

                TextInput::make('kisaran_sawal')
                    ->label('Kisaran Saldo Awal')
                    ->required()
                    ->default(0)
                    ->prefix('Rp')
                    ->placeholder('Contoh: 25000.65')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state && $state !== '') {
                            $cleanValue = str_replace(',', '', $state);
                            if (is_numeric($cleanValue)) {
                                $numericValue = (float) $cleanValue;
                                $formatted = number_format($numericValue, 2, '.', ',');
                                $set('kisaran_sawal', $formatted);
                            }
                        }
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return $state ? (float) str_replace(',', '', $state) : null;
                    })
                    ->formatStateUsing(function ($state) {
                        return $state ? number_format((float) $state, 2, '.', ',') : '';
                    }),

                Repeater::make('details')
                    ->label('Detail Proyeksi')
                    ->relationship('details')
                    ->schema([
                        Select::make('id_klasifikasi')
                            ->label('Klasifikasi')
                            ->options(function () {
                                return MasterKlasifikasi::where('status', 0)
                                    ->where('tipe_klasifikasi', 'KK')
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        return [$item->id_klasifikasi => $item->klasifikasi . ' (' . $item->kriteria . ')'];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->native(false),

                        TextInput::make('nominal_proyeksi')
                            ->label('Nominal Proyeksi')
                            ->required()
                            ->prefix('Rp')
                            ->placeholder('Contoh: 25000.65')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state && $state !== '') {
                                    $cleanValue = str_replace(',', '', $state);
                                    if (is_numeric($cleanValue)) {
                                        $numericValue = (float) $cleanValue;
                                        $formatted = number_format($numericValue, 2, '.', ',');
                                        $set('nominal_proyeksi', $formatted);
                                    }
                                }
                            })
                            ->dehydrateStateUsing(function ($state) {
                                return $state ? (float) str_replace(',', '', $state) : null;
                            })
                            ->formatStateUsing(function ($state) {
                                return $state ? number_format((float) $state, 2, '.', ',') : '';
                            }),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->addActionLabel('Tambah Klasifikasi')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $table = $table
            ->columns([
                TextColumn::make('id_proyeksi')
                    ->label('ID Proyeksi')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ProyeksiResource::getUrl('edit', ['record' => $record])),

                TextColumn::make('tgl_input')
                    ->label('Tanggal Input')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tgl_dari')
                    ->label('Tanggal Dari')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tgl_sampai')
                    ->label('Tanggal Sampai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('jenisKas.jenis_kas')
                    ->label('Jenis Kas')
                    ->searchable(),

                TextColumn::make('kisaran_sawal')
                    ->label('Kisaran Saldo Awal')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->alignRight(),

                TextColumn::make('total_proyeksi')
                    ->label('Total Proyeksi')
                    ->formatStateUsing(fn ($state, $record) => number_format($record->details->sum('nominal_proyeksi'), 2, '.', ','))
                    ->alignRight()
                    ->getStateUsing(fn ($record) => $record->details->sum('nominal_proyeksi')),

                TextColumn::make('actions')
                    ->label('Export')
                    ->getStateUsing(fn () => 'Export Excel')
                    ->url(fn ($record) => route('proyeksi.export', $record->id_proyeksi))
                    ->openUrlInNewTab()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_jenis_kas')
                    ->label('Jenis Kas')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user && !$user->isSuperAdmin()) {
                            $allowedIds = $user->getAllowedJenisKasIds();
                            return MasterJenisKas::where('status', 0)
                                ->whereIn('id_jenis_kas', $allowedIds)
                                ->pluck('jenis_kas', 'id_jenis_kas');
                        }
                        return MasterJenisKas::where('status', 0)->pluck('jenis_kas', 'id_jenis_kas');
                    }),

                Tables\Filters\Filter::make('tgl_dari')
                    ->form([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($query, $date) => $query->whereDate('tgl_dari', '>=', $date))
                            ->when($data['sampai'], fn ($query, $date) => $query->whereDate('tgl_dari', '<=', $date));
                    }),
            ])
            ->actions([
                // Actions removed due to compatibility issues
            ])
            ->bulkActions([
                // Bulk actions removed due to compatibility issues
            ])
            ->defaultSort('tgl_input', 'desc');
        
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProyeksi::route('/'),
            'create' => Pages\CreateProyeksi::route('/create'),
            'view' => Pages\ViewProyeksi::route('/{record}'),
            'edit' => Pages\EditProyeksi::route('/{record}/edit'),
        ];
    }
}