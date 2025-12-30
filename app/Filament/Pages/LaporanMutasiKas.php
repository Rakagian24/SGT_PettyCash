<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\HeaderActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Facades\DB;
use App\Models\MasterJenisKas;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Exports\LaporanMutasiKasExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanMutasiKas extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static string|null $navigationLabel = 'Laporan Mutasi Kas';
    protected static string|null $title = 'Laporan Mutasi Kas';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_laporan_mutasi');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        if ($user->isSuperAdmin()) {
            return true;
        }
        
        return $user->hasPermission('view_laporan_mutasi');
    }

    protected string $view = 'filament.pages.laporan-mutasi-kas';



    public $cachedResults = null;
    public $currentJenisKas = null;
    public $currentDari = null;
    public $currentSampai = null;


    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Return a dummy query that Filament expects
        // The actual data is fetched in the filter's query callback or getTableRecords
        return MasterJenisKas::query()->whereRaw('1=0');
    }

    protected function runCustomQuery(array $data): void
    {
        $dari = $data['dari'] ?? null;
        $sampai = $data['sampai'] ?? null;
        $id_jenis_kas = $data['id_jenis_kas'] ?? null;

        if (!$dari || !$sampai || !$id_jenis_kas) {
            $this->cachedResults = collect([]);
            return;
        }

        // Simpan data filter untuk digunakan di export
        $this->currentDari = $dari;
        $this->currentSampai = $sampai;
        $this->currentJenisKas = $id_jenis_kas;

        // Properly escape parameters using PDO quote
        // Ensure strictly Y-m-d format to match user's string comparison logic
        $dari = substr($dari, 0, 10);
        $sampai = substr($sampai, 0, 10);
        
        $pdo = DB::connection()->getPdo();
        $dariQuoted = $pdo->quote($dari);
        $sampaiQuoted = $pdo->quote($sampai);
        $idJenisKasQuoted = $pdo->quote($id_jenis_kas);

        $sql = "
            WITH SaldoAwal AS (
                SELECT
                    COALESCE(SUM(nominal_km), 0) - COALESCE(SUM(nominal_kk), 0) AS saldo_awal
                FROM
                    (
                        SELECT
                            km.nominal_km,
                            0 AS nominal_kk
                        FROM
                            transaksi_km AS km
                        WHERE
                            km.tanggal_km < {$dariQuoted}
                            AND km.id_jenis_kas = {$idJenisKasQuoted}
                        UNION ALL
                        SELECT
                            0 AS nominal_km,
                            kk.nominal_kk
                        FROM
                            transaksi_kk AS kk
                        WHERE
                            kk.tanggal_kk < {$dariQuoted}
                            AND kk.id_jenis_kas = {$idJenisKasQuoted}
                    ) AS SubQuerySaldo
            ),
            CombinedTransactions AS (
                SELECT
                    km.tanggal_km AS tanggal,
                    km.no_km AS no_transaksi,
                    mt.jenis_terima AS jenis_transaksi, -- Menandai transaksi sebagai Kas Masuk
                    km.keterangan_km AS keterangan,
                    mk.id_klasifikasi,
                    mk.kriteria,
                    mk.klasifikasi,
                    km.nominal_km AS nominal_pemasukan,
                    0 AS nominal_pengeluaran
                FROM
                    transaksi_km AS km
                    INNER JOIN master_klasifikasi AS mk ON km.id_klasifikasi = mk.id_klasifikasi
                    INNER JOIN master_terima AS mt on km.id_terima = mt.id_terima
                WHERE
                    km.tanggal_km BETWEEN {$dariQuoted}
                    AND {$sampaiQuoted}
                    AND km.id_jenis_kas = {$idJenisKasQuoted}
                UNION ALL
                SELECT
                    kk.tanggal_kk AS tanggal,
                    kk.no_kk AS no_transaksi,
                    mb.jenis_bayar AS jenis_transaksi, -- Menandai transaksi sebagai Kas Keluar
                    kk.keterangan_kk AS keterangan,
                    mk.id_klasifikasi,
                    mk.kriteria,
                    mk.klasifikasi,
                    0 AS nominal_pemasukan,
                    kk.nominal_kk AS nominal_pengeluaran
                FROM
                    transaksi_kk AS kk
                    INNER JOIN master_klasifikasi AS mk ON kk.id_klasifikasi = mk.id_klasifikasi
                    INNER JOIN master_bayar as mb on kk.id_bayar = mb.id_bayar
                WHERE
                    kk.tanggal_kk BETWEEN {$dariQuoted}
                    AND {$sampaiQuoted}
                    AND kk.id_jenis_kas = {$idJenisKasQuoted}
            ),
            FullTransactions AS (
                SELECT
                    '-' AS tanggal,
                    '-' AS no_transaksi,
                    '-' AS jenis_transaksi,
                    'SALDO AWAL' AS keterangan,
                    '-' AS id_klasifikasi,
                    '-' AS kriteria,
                    '-' AS klasifikasi,
                    0 AS nominal_pemasukan,
                    0 AS nominal_pengeluaran,
                    saldo_awal AS saldo_awal
                FROM
                    SaldoAwal
                UNION ALL
                SELECT
                    tanggal,
                    no_transaksi,
                    jenis_transaksi,
                    keterangan,
                    id_klasifikasi,
                    kriteria,
                    klasifikasi,
                    nominal_pemasukan,
                    nominal_pengeluaran,
                    NULL AS saldo_awal
                FROM
                    CombinedTransactions
            )
            SELECT
                tanggal,
                no_transaksi,
                jenis_transaksi,
                keterangan,
                id_klasifikasi,
                kriteria,
                klasifikasi,
                nominal_pemasukan,
                nominal_pengeluaran,
                SUM(
                    COALESCE(nominal_pemasukan, 0) - COALESCE(nominal_pengeluaran, 0) + COALESCE(saldo_awal, 0)
                ) OVER (
                    ORDER BY
                    tanggal ASC,
                    CASE 
                        WHEN no_transaksi LIKE 'KM%' THEN 0
                        WHEN no_transaksi LIKE 'KK%' THEN 1
                        ELSE 2
                    END,
                    no_transaksi ASC
                ) AS saldo_akhir
            FROM
                FullTransactions
            ORDER BY
                CASE 
                    WHEN tanggal = '-' THEN 0 ELSE 1 
                END,
                tanggal ASC,
                CASE 
                    WHEN no_transaksi LIKE 'KM%' THEN 0
                    WHEN no_transaksi LIKE 'KK%' THEN 1
                    ELSE 2
                END,
                no_transaksi ASC
        ";

        // Execute the query
        $results = DB::select($sql);

        // Convert stdClass to array and add row_id
        $arrayResults = [];
        foreach ($results as $index => $item) {
            $array = json_decode(json_encode($item), true);
            $array['row_id'] = $index + 1; // Generate row_id since query doesn't provide it
            $array['key'] = $array['row_id'];
            
            // Ensure numeric types
            $array['nominal_pemasukan'] = (float) $array['nominal_pemasukan'];
            $array['nominal_pengeluaran'] = (float) $array['nominal_pengeluaran'];
            $array['saldo_akhir'] = (float) $array['saldo_akhir'];

            // Format transaction number: tampilkan lengkap, jangan dipotong
            // if ($array['no_transaksi'] !== '-' && (str_starts_with($array['no_transaksi'], 'KM') || str_starts_with($array['no_transaksi'], 'KK'))) {
            //     $array['no_transaksi'] = substr($array['no_transaksi'], -4);
            // }
            
            $arrayResults[] = $array;
        }

        // Store results in a property for table access
        $this->cachedResults = collect($arrayResults);
    }



    public function getTableRecords(): \Illuminate\Contracts\Pagination\Paginator
    {
        // If cachedResults is null, it typically means filters haven't run yet or logic was skipped.
        // In Filament lifecycle, table query modification (filters) happens before records fetching.
        // However, if no filter is active/applied (e.g. initial load without defaults? No, we set defaults), 
        // cachedResults might be null.
        
        // We handle the "empty" state here.

        
        $data = $this->cachedResults ?? collect([]);
        
        // Get per page from table property or default to 10
        $perPage = $this->tableRecordsPerPage === 'all' ? $data->count() : ($this->tableRecordsPerPage ?? 10);
        $perPage = is_numeric($perPage) ? $perPage : 10;
        
        $currentPage = Paginator::resolveCurrentPage('page');
        
        return new LengthAwarePaginator(
            $data->forPage($currentPage, $perPage),
            $data->count(),
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }

    public function getTableRecordKey($record): string
    {
        // Tell Filament to use 'key' field as the unique identifier
        return (string) $record['key'];
    }



    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('tanggal')
                    ->label('TANGGAL')
                    ->alignCenter(),
                TextColumn::make('no_transaksi')
                    ->label('NOMOR KM/KK')
                    ->alignCenter(),
                TextColumn::make('jenis_transaksi')
                    ->label('DARI/KE')
                    ->wrap(),
                TextColumn::make('keterangan')
                    ->label('KETERANGAN')
                    ->wrap(),
                TextColumn::make('id_klasifikasi')
                    ->label('ID'),
                TextColumn::make('kriteria')
                    ->label('KRITERIA')
                    ->wrap(),
                TextColumn::make('klasifikasi')
                    ->label('KLASIFIKASI')
                    ->wrap(),
                TextColumn::make('nominal_pemasukan')
                    ->label('KM')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->alignRight()
                    ->extraAttributes(['class' => 'py-4'])
                    ->summarize(\Filament\Tables\Columns\Summarizers\Summarizer::make()
                        ->label('TOTAL')
                        ->using(fn () => $this->cachedResults?->sum('nominal_pemasukan') ?? 0)
                        ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))),
                TextColumn::make('nominal_pengeluaran')
                    ->label('KK')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->alignRight()
                    ->extraAttributes(['class' => 'py-4'])
                    ->summarize(\Filament\Tables\Columns\Summarizers\Summarizer::make()
                        ->label('TOTAL')
                        ->using(fn () => $this->cachedResults?->sum('nominal_pengeluaran') ?? 0)
                        ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))),
                TextColumn::make('saldo_akhir')
                    ->label('SALDO')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))
                    ->alignRight()
                    ->extraAttributes(['class' => 'py-4'])
                    ->summarize(\Filament\Tables\Columns\Summarizers\Summarizer::make()
                        ->label('SALDO AKHIR')
                        ->using(fn () => $this->cachedResults?->last()['saldo_akhir'] ?? 0)
                        ->formatStateUsing(fn ($state) => number_format($state, 2, '.', ','))),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('EXPORT EXCEL')
                    ->action(fn() => $this->exportToExcel())
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down'),
                Action::make('cetak_pdf')
                    ->label('CETAK PDF')
                    ->url(fn() => $this->getPdfPreviewUrl())
                    ->color('info')
                    ->icon('heroicon-o-printer')
                    ->openUrlInNewTab(),
            ], position: HeaderActionsPosition::Bottom)
            ->filters([
                Filter::make('filter')
                    ->form([
                         DatePicker::make('dari')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfMonth())
                            ->required()
                            ->native(false),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal')
                            ->default(now())
                            ->required()
                            ->native(false),
                        Select::make('id_jenis_kas')
                            ->label('Jenis Kas')
                            ->options(MasterJenisKas::where('status', 0)->pluck('jenis_kas', 'id_jenis_kas'))
                            ->default(fn () => MasterJenisKas::where('status', 0)->first()?->id_jenis_kas)
                            ->required()
                            ->native(false)
                            ->searchable(),
                    ])
                    ->columns(3)
                    ->query(function (Builder $query, array $data) {
                        $this->runCustomQuery($data);
                        return $query; // Return the dummy query unmodified
                    })
                    ->columnSpanFull()
            ], layout: FiltersLayout::AboveContent);
            
    }

    public function exportToExcel()
    {
        if (!$this->cachedResults || $this->cachedResults->isEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('Export Gagal')
                ->body('Data tidak ditemukan untuk diekspor. Pastikan filter sudah diset dengan benar.')
                ->warning()
                ->send();
            return;
        }

        try {
            $jenisKas = 'KAS';
            $dari = '';
            $sampai = '';
            
            // Ambil dari property yang sudah disimpan
            if ($this->currentJenisKas) {
                $masterJenis = MasterJenisKas::find($this->currentJenisKas);
                $jenisKas = $masterJenis ? strtoupper($masterJenis->jenis_kas) : 'KAS';
            }
            
            if ($this->currentDari && $this->currentSampai) {
                try {
                    $dari = \Carbon\Carbon::parse($this->currentDari)->format('d-M-Y');
                    $sampai = \Carbon\Carbon::parse($this->currentSampai)->format('d-M-Y');
                } catch (\Exception $e) {
                    $dari = date('d-M-Y');
                    $sampai = date('d-M-Y');
                }
            }
            
            // Format periode lengkap
            $periode = "Periode {$dari} sampai {$sampai}";
            
            // Generate filename yang aman
            $filename = "Laporan_Mutasi_Kas_{$jenisKas}_Periode_{$dari}_sd_{$sampai}.xlsx";
            
            // Bersihkan karakter yang tidak valid untuk nama file
            $filename = str_replace([' ', '/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
            
            return Excel::download(
                new LaporanMutasiKasExport($this->cachedResults, $jenisKas, $periode),
                $filename
            );
            
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Export Gagal')
                ->body('Terjadi kesalahan saat export: ' . $e->getMessage())
                ->danger()
                ->send();
            
            // Log error untuk debugging
            \Log::error('Export Excel Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function getPdfPreviewUrl()
    {
        if (!$this->cachedResults || $this->cachedResults->isEmpty()) {
            return null;
        }

        try {
            // Clean up old session data first
            $this->cleanupOldPdfSessions();
            
            $jenisKas = 'KAS';
            $dari = '';
            $sampai = '';
            
            // Ambil dari property yang sudah disimpan
            if ($this->currentJenisKas) {
                $masterJenis = MasterJenisKas::find($this->currentJenisKas);
                $jenisKas = $masterJenis ? strtoupper($masterJenis->jenis_kas) : 'KAS';
            }
            
            if ($this->currentDari && $this->currentSampai) {
                try {
                    $dari = \Carbon\Carbon::parse($this->currentDari)->format('d-M-Y');
                    $sampai = \Carbon\Carbon::parse($this->currentSampai)->format('d-M-Y');
                } catch (\Exception $e) {
                    $dari = date('d-M-Y');
                    $sampai = date('d-M-Y');
                }
            }
            
            // Generate unique session key
            $sessionKey = 'laporan_mutasi_kas_' . uniqid();
            
            // Store data in session instead of URL
            session([
                $sessionKey => [
                    'data' => $this->cachedResults->toArray(),
                    'jenis_kas' => $jenisKas,
                    'dari' => $dari,
                    'sampai' => $sampai,
                    'expires_at' => now()->addMinutes(10) // Expire after 10 minutes
                ]
            ]);
            
            // Return URL dengan session key saja
            return route('laporan.mutasi.kas.pdf', [
                'session_key' => $sessionKey
            ]);
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Clean up old PDF session data to prevent session bloat
     */
    private function cleanupOldPdfSessions()
    {
        $allSessionData = session()->all();
        $now = now();
        
        foreach ($allSessionData as $key => $value) {
            // Check if this is a PDF session key and if it's expired
            if (str_starts_with($key, 'laporan_mutasi_kas_') && 
                is_array($value) && 
                isset($value['expires_at']) && 
                $now->gt($value['expires_at'])) {
                session()->forget($key);
            }
        }
    }

    public function resetTable(): void
    {
        $this->dispatch('refreshTable');
    }
}
