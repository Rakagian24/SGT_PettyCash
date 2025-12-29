<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class LaporanMutasiKasExport implements FromCollection, WithHeadings, WithColumnFormatting, WithEvents
{
    protected $data;
    protected $jenisKas;
    protected $periode;

    public function __construct(Collection $data, string $jenisKas, string $periode)
    {
        $this->data = $data;
        $this->jenisKas = $jenisKas;
        $this->periode = $periode;
    }

    protected function cleanWhitespace($text)
    {
        if (empty($text) || $text === '-') {
            return $text;
        }
        
        // Hapus whitespace di awal dan akhir
        $text = trim($text);
        
        // Hapus multiple spaces menjadi single space
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Hapus karakter tidak terlihat seperti tab, newline, dll
        $text = preg_replace('/[\r\n\t]/', ' ', $text);
        
        // Trim lagi setelah pembersihan
        return trim($text);
    }

    public function collection()
    {
        // Transform data untuk Excel - pastikan semua data termasuk saldo awal
        return $this->data->map(function ($item, $index) {
            return [
                'tanggal' => $item['tanggal'],
                'no_transaksi' => $item['no_transaksi'],
                'jenis_transaksi' => $this->cleanWhitespace($item['jenis_transaksi']),
                'keterangan' => $this->cleanWhitespace($item['keterangan']), // Clear whitespace
                'id_klasifikasi' => $item['id_klasifikasi'],
                'kriteria' => $this->cleanWhitespace($item['kriteria']),
                'klasifikasi' => $this->cleanWhitespace($item['klasifikasi']),
                'nominal_pemasukan' => $item['nominal_pemasukan'],
                'nominal_pengeluaran' => $item['nominal_pengeluaran'],
                'saldo_akhir' => $item['saldo_akhir'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'TANGGAL',
            'NOMOR KM/KK',
            'DARI/KE',
            'KETERANGAN',
            'ID',
            'KRITERIA',
            'KLASIFIKASI',
            'KM',
            'KK',
            'SALDO'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Tanggal akan diformat manual
            'H' => '#,##0.00', // KM
            'I' => '#,##0.00', // KK  
            'J' => '#,##0.00', // SALDO
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert header di baris 1 dan 2 sebelum data
                $sheet->insertNewRowBefore(1, 2);
                
                // Header Judul (Row 1)
                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', "LAPORAN MUTASI {$this->jenisKas} PT. GUNAJAYA SANTOSA");
                
                // Header Periode (Row 2)
                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', "Periode {$this->periode}");
                
                // Style untuk header judul
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                
                // Style untuk header periode
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                
                // Style untuk header kolom (sekarang di row 3)
                $sheet->getStyle('A3:J3')->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $highestRow = $sheet->getHighestRow();
                
                // Format tanggal untuk setiap baris data (mulai dari row 4)
                for ($row = 4; $row <= $highestRow; $row++) {
                    $tanggalValue = $sheet->getCell("A{$row}")->getValue();
                    if ($tanggalValue !== '-' && $tanggalValue !== 'TANGGAL' && !empty($tanggalValue)) {
                        try {
                            // Coba parse tanggal dan format sesuai VB.NET
                            $date = \Carbon\Carbon::parse($tanggalValue);
                            $sheet->setCellValue("A{$row}", $date->format('d-M-y'));
                        } catch (\Exception $e) {
                            // Keep original value if parsing fails
                        }
                    }
                }
                
                // Apply borders untuk semua data (mulai dari row 4)
                $dataRange = "A4:J{$highestRow}";
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                
                // Tambah baris TOTAL
                $totalRow = $highestRow + 1;
                $sheet->setCellValue("G{$totalRow}", 'TOTAL');
                
                // Formula untuk total KM (kolom H) - mulai dari row 4
                $sheet->setCellValue("H{$totalRow}", "=SUM(H4:H{$highestRow})");
                
                // Formula untuk total KK (kolom I) - mulai dari row 4
                $sheet->setCellValue("I{$totalRow}", "=SUM(I4:I{$highestRow})");
                
                // Formula untuk saldo akhir (mengambil saldo terakhir)
                $sheet->setCellValue("J{$totalRow}", "=J{$highestRow}");
                
                // Style untuk baris TOTAL
                $sheet->getStyle("G{$totalRow}:J{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'numberFormat' => [
                        'formatCode' => '#,##0.00'
                    ]
                ]);
                
                // Format kolom G (TOTAL label) sebagai text
                $sheet->getStyle("G{$totalRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                
                // Auto-fit semua kolom
                foreach (range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // Set minimum width untuk kolom yang mungkin terlalu sempit
                $sheet->getColumnDimension('A')->setWidth(12); // Tanggal
                $sheet->getColumnDimension('B')->setWidth(15); // Nomor KM/KK (lebih lebar untuk nomor lengkap)
                $sheet->getColumnDimension('H')->setWidth(15); // KM
                $sheet->getColumnDimension('I')->setWidth(15); // KK
                $sheet->getColumnDimension('J')->setWidth(15); // SALDO
            },
        ];
    }
}