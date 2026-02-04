<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\MasterJenisKas;
use Illuminate\Support\Facades\DB;

class SaldoKasOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $saldoData = $this->getSaldoPerJenisKas();
        $transaksiHariIni = $this->getTransaksiHariIni();
        
        $stats = [];
        
        // Stats untuk setiap jenis kas
        foreach ($saldoData as $data) {
            $stats[] = Stat::make($data['jenis_kas'], number_format($data['saldo'], 2, '.', ','))
                ->description($data['saldo'] >= 0 ? 'Saldo Tersedia' : 'Saldo Minus')
                ->descriptionIcon($data['saldo'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($data['saldo'] >= 0 ? 'success' : 'danger');
        }
        
        // Stats untuk transaksi hari ini
        $stats[] = Stat::make('Kas Masuk Hari Ini', number_format($transaksiHariIni['kas_masuk'], 2, '.', ','))
            ->description($transaksiHariIni['count_km'] . ' transaksi')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success');
            
        $stats[] = Stat::make('Kas Keluar Hari Ini', number_format($transaksiHariIni['kas_keluar'], 2, '.', ','))
            ->description($transaksiHariIni['count_kk'] . ' transaksi')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger');
            
        $netFlow = $transaksiHariIni['kas_masuk'] - $transaksiHariIni['kas_keluar'];
        $stats[] = Stat::make('Net Flow Hari Ini', number_format($netFlow, 2, '.', ','))
            ->description($netFlow >= 0 ? 'Surplus' : 'Defisit')
            ->descriptionIcon($netFlow >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($netFlow >= 0 ? 'success' : 'warning');
        
        return $stats;
    }
    
    private function getSaldoPerJenisKas(): array
    {
        $user = auth()->user();
        $allowedJenisKas = [];
        
        if ($user && !$user->isSuperAdmin()) {
            $allowedJenisKas = $user->getAllowedJenisKasIds();
        }
        
        $jenisKasQuery = MasterJenisKas::where('status', 0);
        
        if (!empty($allowedJenisKas)) {
            $jenisKasQuery->whereIn('id_jenis_kas', $allowedJenisKas);
        }
        
        $jenisKasList = $jenisKasQuery->get();
        $saldoData = [];
        
        foreach ($jenisKasList as $jenisKas) {
            // Hitung saldo dari transaksi KM dan KK
            $kasmasuk = DB::table('transaksi_km')
                ->where('id_jenis_kas', $jenisKas->id_jenis_kas)
                ->sum('nominal_km');
                
            $kaskeluar = DB::table('transaksi_kk')
                ->where('id_jenis_kas', $jenisKas->id_jenis_kas)
                ->sum('nominal_kk');
                
            $saldo = $kasmasuk - $kaskeluar;
            
            $saldoData[] = [
                'jenis_kas' => $jenisKas->jenis_kas,
                'saldo' => $saldo
            ];
        }
        
        return $saldoData;
    }
    
    private function getTransaksiHariIni(): array
    {
        $today = now()->format('Y-m-d');
        
        $user = auth()->user();
        $allowedJenisKas = [];
        
        if ($user && !$user->isSuperAdmin()) {
            $allowedJenisKas = $user->getAllowedJenisKasIds();
        }
        
        $kmQuery = DB::table('transaksi_km')->whereDate('tanggal_km', $today);
        $kkQuery = DB::table('transaksi_kk')->whereDate('tanggal_kk', $today);
        
        if (!empty($allowedJenisKas)) {
            $kmQuery->whereIn('id_jenis_kas', $allowedJenisKas);
            $kkQuery->whereIn('id_jenis_kas', $allowedJenisKas);
        }
        
        $kasmasuk = $kmQuery->sum('nominal_km');
        $countKm = $kmQuery->count();
        
        $kaskeluar = $kkQuery->sum('nominal_kk');
        $countKk = $kkQuery->count();
        
        return [
            'kas_masuk' => $kasmasuk,
            'kas_keluar' => $kaskeluar,
            'count_km' => $countKm,
            'count_kk' => $countKk
        ];
    }
}