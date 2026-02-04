<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AliranKasChart extends ChartWidget
{
    protected static ?int $sort = 2;
    
    public function getHeading(): ?string
    {
        return 'Aliran Kas 7 Hari Terakhir';
    }

    protected function getData(): array
    {
        $data = $this->getAliranKasData();
        
        return [
            'datasets' => [
                [
                    'label' => 'Kas Masuk',
                    'data' => $data['kas_masuk'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Kas Keluar',
                    'data' => $data['kas_keluar'],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + value.toLocaleString("id-ID"); }',
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'elements' => [
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
        ];
    }
    
    private function getAliranKasData(): array
    {
        $labels = [];
        $kasmasuk = [];
        $kaskeluar = [];
        
        $user = auth()->user();
        $allowedJenisKas = [];
        
        if ($user && !$user->isSuperAdmin()) {
            $allowedJenisKas = $user->getAllowedJenisKasIds();
        }
        
        // Generate data untuk 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->format('Y-m-d');
            
            // Label untuk chart
            $labels[] = $date->format('d M');
            
            // Kas masuk per hari
            $kmQuery = DB::table('transaksi_km')->whereDate('tanggal_km', $dateString);
            if (!empty($allowedJenisKas)) {
                $kmQuery->whereIn('id_jenis_kas', $allowedJenisKas);
            }
            $kmHarian = $kmQuery->sum('nominal_km');
            $kasmasuk[] = (float) $kmHarian;
            
            // Kas keluar per hari
            $kkQuery = DB::table('transaksi_kk')->whereDate('tanggal_kk', $dateString);
            if (!empty($allowedJenisKas)) {
                $kkQuery->whereIn('id_jenis_kas', $allowedJenisKas);
            }
            $kkHarian = $kkQuery->sum('nominal_kk');
            $kaskeluar[] = (float) $kkHarian;
        }
        
        return [
            'labels' => $labels,
            'kas_masuk' => $kasmasuk,
            'kas_keluar' => $kaskeluar,
        ];
    }
}