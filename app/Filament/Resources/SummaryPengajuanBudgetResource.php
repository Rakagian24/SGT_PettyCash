<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SummaryPengajuanBudgetResource\Pages;
use App\Models\SummaryPengajuanBudget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SummaryPengajuanBudgetResource extends Resource
{
    protected static ?string $model = SummaryPengajuanBudget::class;

    protected static ?string $navigationLabel = 'Summary Pengajuan Budget';
    protected static ?string $modelLabel = 'Summary Pengajuan Budget';
    protected static ?string $pluralModelLabel = 'Summary Pengajuan Budget';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;
    protected static string|\UnitEnum|null $navigationGroup = 'Budget';

    protected static ?string $recordTitleAttribute = 'id_spb';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_summary_pengajuan_budget');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_summary_pengajuan_budget');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('id_spb')
                    ->label('ID SPB')
                    ->default(fn () => SummaryPengajuanBudget::generateSPBNumber())
                    ->disabled()
                    ->dehydrated(),

                DatePicker::make('tgl_dari')
                    ->label('Tanggal Dari')
                    ->required()
                    ->default(now()->startOfMonth())
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::updateSummaryData($state, $get('tgl_sampai'), $set);
                    }),

                DatePicker::make('tgl_sampai')
                    ->label('Tanggal Sampai')
                    ->required()
                    ->default(now()->endOfMonth())
                    ->native(false)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        self::updateSummaryData($get('tgl_dari'), $state, $set);
                    }),

                TextInput::make('kgs')
                    ->label('Kas Kecil')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total', self::calculateTotal($get));
                    }),

                TextInput::make('ogs')
                    ->label('Kas Office')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total', self::calculateTotal($get));
                    }),

                TextInput::make('pgs')
                    ->label('Kas Personalia')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total', self::calculateTotal($get));
                    }),

                TextInput::make('bgs')
                    ->label('Kas Bangunan')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('total', self::calculateTotal($get));
                    }),

                TextInput::make('total')
                    ->label('Total Pengajuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(false)
                    ->live()
                    ->default(0)
                    ->afterStateHydrated(function ($component, $state, callable $get) {
                        $component->state(self::calculateTotal($get));
                    }),

                TextInput::make('pembulatan')
                    ->label('Pembulatan')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->default(0),
            ]);
    }

    protected static function updateSummaryData($tglDari, $tglSampai, callable $set): void
    {
        if ($tglDari && $tglSampai) {
            $summaryData = SummaryPengajuanBudget::getSummaryData($tglDari, $tglSampai);
            
            $set('kgs', $summaryData['kgs']);
            $set('ogs', $summaryData['ogs']);
            $set('pgs', $summaryData['pgs']);
            $set('bgs', $summaryData['bgs']);
            
            // Calculate and set total using the summary data directly
            $total = $summaryData['kgs'] + $summaryData['ogs'] + $summaryData['pgs'] + $summaryData['bgs'];
            $set('total', $total);
        }
    }

    protected static function calculateTotal(callable $get): float
    {
        return ($get('kgs') ?? 0) + ($get('ogs') ?? 0) + ($get('pgs') ?? 0) + ($get('bgs') ?? 0);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_spb')
                    ->label('ID SPB')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tgl_input')
                    ->label('Tanggal Pembuatan')
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

                TextColumn::make('kgs')
                    ->label('Kas Kecil')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),

                TextColumn::make('ogs')
                    ->label('Kas Office')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),

                TextColumn::make('pgs')
                    ->label('Kas Personalia')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),

                TextColumn::make('bgs')
                    ->label('Kas Bangunan')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),

                TextColumn::make('total')
                    ->label('Total Pengajuan')
                    ->formatStateUsing(fn ($state, $record) => number_format($record->kgs + $record->ogs + $record->pgs + $record->bgs, 2, '.', ','))
                    ->getStateUsing(fn ($record) => $record->kgs + $record->ogs + $record->pgs + $record->bgs)
                    ->sortable(),

                TextColumn::make('pembulatan')
                    ->label('Pembulatan')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->sortable(),

                TextColumn::make('Export')
                    ->label('Export')
                    ->getStateUsing(fn () => 'Export Excel')
                    ->url(fn ($record) => route('summary-pengajuan-budget.export', $record->id_spb))
                    ->openUrlInNewTab()
                    ->color('success'),
            ])
            ->filters([
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
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSummaryPengajuanBudgets::route('/'),
            'create' => Pages\CreateSummaryPengajuanBudget::route('/create'),
            'edit' => Pages\EditSummaryPengajuanBudget::route('/{record}/edit'),
            'view' => Pages\ViewSummaryPengajuanBudget::route('/{record}'),
        ];
    }
}