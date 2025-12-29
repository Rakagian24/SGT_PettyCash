<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengeluaranKlasifikasiChart extends ChartWidget
{
    protected static ?int $sort = 3;
    
    public function getHeading(): ?string
    {
        return 'Pengeluaran per Klasifikasi (Bulan Ini)';
    }

    protected function getData(): array
    {
        $data = $this->getPengeluaranKlasifikasi();
        
        return [
            'datasets' => [
                [
                    'data' => $data['values'],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',   // Blue
                        'rgba(16, 185, 129, 0.8)',   // Green  
                        'rgba(245, 158, 11, 0.8)',   // Yellow
                        'rgba(239, 68, 68, 0.8)',    // Red
                        'rgba(139, 69, 19, 0.8)',    // Brown
                        'rgba(147, 51, 234, 0.8)',   // Purple
                        'rgba(236, 72, 153, 0.8)',   // Pink
                        'rgba(107, 114, 128, 0.8)',  // Gray
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(139, 69, 19, 1)',
                        'rgba(147, 51, 234, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(107, 114, 128, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { 
                            var label = context.label || "";
                            var value = context.parsed;
                            var total = context.dataset.data.reduce((a, b) => a + b, 0);
                            var percentage = ((value / total) * 100).toFixed(1);
                            return label + ": Rp " + value.toLocaleString("id-ID") + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
    
    private function getPengeluaranKlasifikasi(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $pengeluaran = DB::table('transaksi_kk as kk')
            ->join('master_klasifikasi as mk', 'kk.id_klasifikasi', '=', 'mk.id_klasifikasi')
            ->select('mk.klasifikasi', DB::raw('SUM(kk.nominal_kk) as total'))
            ->whereBetween('kk.tanggal_kk', [$startOfMonth, $endOfMonth])
            ->groupBy('mk.klasifikasi', 'mk.id_klasifikasi')
            ->orderBy('total', 'desc')
            ->limit(8) // Ambil 8 klasifikasi teratas
            ->get();
        
        $labels = [];
        $values = [];
        
        foreach ($pengeluaran as $item) {
            $labels[] = $item->klasifikasi;
            $values[] = (float) $item->total;
        }
        
        // Jika tidak ada data, berikan data dummy
        if (empty($labels)) {
            $labels = ['Belum ada data'];
            $values = [0];
        }
        
        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}