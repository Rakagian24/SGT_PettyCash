<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanBudgetResource\Pages;
use App\Models\PengajuanBudget;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class PengajuanBudgetResource extends Resource
{
    protected static ?string $model = PengajuanBudget::class;

    protected static ?string $navigationLabel = 'Pengajuan Budget';
    protected static ?string $modelLabel = 'Pengajuan Budget';
    protected static ?string $pluralModelLabel = 'Pengajuan Budget';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|\UnitEnum|null $navigationGroup = 'Budget';

    protected static ?string $recordTitleAttribute = 'id_pengajuan_budget';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_pengajuan_budget');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_pengajuan_budget');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('id_pengajuan_budget')
                    ->label('ID Pengajuan Budget')
                    ->default(fn () => PengajuanBudget::generatePGBNumber())
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

                TextInput::make('kisaran_saldo')
                    ->label('Kisaran Sisa Saldo')
                    ->required()
                    ->default(0)
                    ->prefix('Rp')
                    ->placeholder('Contoh: 25000.65')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $state !== '') {
                            // Format the current field
                            $cleanValue = str_replace(',', '', $state);
                            if (is_numeric($cleanValue)) {
                                $numericValue = (float) $cleanValue;
                                $formatted = number_format($numericValue, 2, '.', ',');
                                $set('kisaran_saldo', $formatted);
                            }
                        }
                        
                        // Auto calculate nominal_pengajuan when kisaran_saldo changes
                        $details = $get('details') ?? [];
                        $totalDetail = collect($details)->sum(function($detail) {
                            $value = $detail['nominal_pengajuan_dtl'] ?? 0;
                            return (float) str_replace(',', '', $value);
                        });
                        $kisaranSaldo = (float) str_replace(',', '', $state ?? 0);
                        
                        if ($totalDetail >= $kisaranSaldo) {
                            $pengajuan = $totalDetail - $kisaranSaldo;
                        } else {
                            $pengajuan = $totalDetail;
                        }
                        
                        $formattedPengajuan = number_format($pengajuan, 2, '.', ',');
                        $set('nominal_pengajuan', $formattedPengajuan);
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return $state ? (float) str_replace(',', '', $state) : null;
                    })
                    ->formatStateUsing(function ($state) {
                        return $state ? number_format((float) $state, 2, '.', ',') : '';
                    }),

                TextInput::make('nominal_pengajuan')
                    ->label('Nominal Pengajuan')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->formatStateUsing(function ($state) {
                        return $state ? number_format((float) $state, 2, '.', ',') : '';
                    }),

                Repeater::make('details')
                    ->label('Detail Pengajuan Budget')
                    ->relationship('details')
                    ->schema([
                        TextInput::make('keterangan')
                            ->label('Keterangan')
                            ->required()
                            ->maxLength(255),

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

                        Select::make('lampiran')
                            ->label('Lampiran')
                            ->options([
                                'ADA' => 'ADA',
                                'TIDAK ADA' => 'TIDAK ADA',
                            ])
                            ->required()
                            ->default('TIDAK ADA')
                            ->native(false),

                        TextInput::make('nominal_pengajuan_dtl')
                            ->label('Nominal')
                            ->required()
                            ->prefix('Rp')
                            ->placeholder('Contoh: 25000.65')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && $state !== '') {
                                    // Format the current field
                                    $cleanValue = str_replace(',', '', $state);
                                    if (is_numeric($cleanValue)) {
                                        $numericValue = (float) $cleanValue;
                                        $formatted = number_format($numericValue, 2, '.', ',');
                                        $set('nominal_pengajuan_dtl', $formatted);
                                    }
                                }
                                
                                // Auto calculate total when detail nominal changes
                                $details = $get('../../details') ?? [];
                                $totalDetail = collect($details)->sum(function($detail) {
                                    return (float) str_replace(',', '', $detail['nominal_pengajuan_dtl'] ?? 0);
                                });
                                $kisaranSaldo = floatval(str_replace(',', '', $get('../../kisaran_saldo') ?? 0));
                                
                                if ($totalDetail >= $kisaranSaldo) {
                                    $pengajuan = $totalDetail - $kisaranSaldo;
                                } else {
                                    $pengajuan = $totalDetail;
                                }
                                
                                $formattedPengajuan = number_format($pengajuan, 2, '.', ',');
                                $set('../../nominal_pengajuan', $formattedPengajuan);
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
                    ->addActionLabel('Tambah Detail')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $table = $table
            ->columns([
                TextColumn::make('id_pengajuan_budget')
                    ->label('ID Pengajuan Budget')
                    ->searchable()
                    ->sortable(),

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

                TextColumn::make('kisaran_saldo')
                    ->label('Kisaran Sisa Saldo')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),

                TextColumn::make('nominal_pengajuan')
                    ->label('Nominal Pengajuan')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),

                TextColumn::make('details_count')
                    ->label('Jumlah Detail')
                    ->counts('details')
                    ->sortable(),

                TextColumn::make('Export')
                    ->label('Export')
                    ->getStateUsing(fn () => 'Export Excel')
                    ->url(fn ($record) => route('pengajuan-budget.export', $record->id_pengajuan_budget))
                    ->openUrlInNewTab()
                    ->color('success'),
            ])
            ->filters([
                SelectFilter::make('id_jenis_kas')
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

                Filter::make('tgl_dari')
                    ->form([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tgl_dari', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tgl_sampai', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPengajuanBudgets::route('/'),
            'create' => Pages\CreatePengajuanBudget::route('/create'),
            'edit' => Pages\EditPengajuanBudget::route('/{record}/edit'),
            'view' => Pages\ViewPengajuanBudget::route('/{record}'),
        ];
    }
}